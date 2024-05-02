<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HelperController as Helper;
use App\Models\Tracking;
use App\Models\TrackingHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Classe que faz a consulta no servidor WEBSRO, trata o HTML retornado
 * e prepara os dados para a gravação no banco de dados.
 * 
 */
class TrackingController extends Controller
{

    private const SUCCESS_STATUS_CODE = 200;
    private const INITIAL_STATUS = 'nao rastreavel';
    private const STATUS_DATE = '1970-01-01 00:00:00';
    private const FLAG_ACTIVE = 1;
    private static $statusConcluido = [
        'entregue',
        'entrega efetuada',
        'objeto entregue ao destinatário'
    ];

    public function __construct()
    {
        
    }

    /**
     * Verifica as informações de rastreio no servidor WEBSRO
     */    
    public function tracking($tracking, $debug = false, $local = false)
    {
        $debug = ($debug == 'false' || $debug == false) ? false : true;
        $local = ($local == 'false' || $local == false) ? false : true;

        //Verifica se o acesso é autenticado 
        if (!$debug && !self::isAuthenticated()) {
            Helper::response('error.access.denied', 403, false);
        }

        $inputs = ['tracking'  => $tracking];
        $rules = ['tracking' => 'required|Min:13|Max:13|regex:/[A-Za-z]{2}(\d){9}[A-Za-z]/'];
        $messages = [
            'required' => 'required.field.:attribute',
            'min' => 'required.fiel.min',
            'max' => 'required.field.max',
            'regex'=> 'error.tracking.format'
        ];

        $validator = Validator::make($inputs, $rules, $messages);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $arrErrors[] = $error;
            }

            if (!empty($arrErrors)) {
                Helper::response($arrErrors, 200, false, true);
            }
        }

        //Tenta realizar a consulta web
        try {
            $url = 'https://www.websro.com.br/detalhes.php?P_COD_UNI=' . $tracking;
            if (!$trackInfo = self::curlLoad($url)) {
                $trackInfo = self::fgetsLoad($url);
            }
        } catch (\Throwable $th) {
            Helper::response(['error.exception', $th->getMessage()], 200, false, true);
        }
        
        //Verifica se a consulta web foi bem sucedida
        if (!$trackInfo) {
            $arrInfo['hasUpdate'] = false;
            $arrInfo['statusCode'] = 200;
            $arrInfo['message'] = Helper::$messages[self::$i18n]['error.querying.tracking'];
            $arrInfo['sequence'] = 1;
            $arrInfo['tracks'] = null;

            Helper::response($arrInfo);
            return $arrInfo;
        }

        //Manipula os dados recebido na consulta web
        $arrTracks = [];
        $arrTracks = self::HtmlHandler($trackInfo);

        //Verifica se pacote pesquisado está disponível para rastreamento
        if(!$arrTracks->success) {
            $arrInfo['hasUpdate'] = false;
            $arrInfo['statusCode'] = 200;
            $arrInfo['message'] = Helper::$messages[self::$i18n][$arrTracks->place];
            $arrInfo['sequence'] = 1;
            $arrInfo['tracks'] = null;

            if(!$local) Helper::response($arrInfo);
            return $arrInfo;
        }

        //Verifica se há alguma informação 
        if (!$arrTracks->data) {
            $arrInfo['hasUpdate'] = false;
            $arrInfo['statusCode'] = 200;
            $arrInfo['message'] = Helper::$messages[self::$i18n]['info.not.updated'];
            $arrInfo['sequence'] = 2;
            $arrInfo['tracks'] = null;
            
            if(!$local) Helper::response($arrInfo);
            return $arrInfo;
        }

        //Retornou dados na consulta web
        $arrInfo['hasUpdate'] = true;
        $arrInfo['statusCode'] = 200;
        $arrInfo['message'] = Helper::$messages[self::$i18n]['updated'];
        $arrInfo['sequence'] = 1;
        $arrInfo['tracks'] = $arrTracks;

        if (!$local) Helper::response($arrInfo);
        return $arrInfo;

    }

    /**
     * Grava as informações de rastreio no banco de dados
     */
    public function updateTracking($tracking, $local = false)
    {
        $local = ($local == 'false' || $local == false) ? false : true;

        $trackInfo = (object) $this->tracking($tracking, 'debug', 'local');
        $trackInfo->tracking = $tracking;

        //Localiza outras informações referentes ao código caso esteja cadastrado na base de dados
        $singleInfo = Tracking::where('numero', $trackInfo->tracking)->first();
        $trackInfo->description = ($singleInfo) ? $singleInfo->descricao : Helper::$messages[self::$i18n]['info.tracking.not.registered'];

        $hasUpdate = false;

        if(!empty($trackInfo->tracks->data)) {
            foreach ($trackInfo->tracks->data as $info) {
                $hasUpdate = false;
                //Procura no banco de dados se a informação do rastreio já existe
                try {
                    $record = TrackingHistory::where('rastreio_id', '=', $tracking)
                    ->where('hash', '=', $info['hash'])
                    ->orderBy('created_at', 'DESC')
                    ->orderBy('id', 'DESC')
                    ->first();
                } catch (\Throwable $th) {
                    Helper::response(['error.exception', $th->getMessage()], 200, false, true);
                }

                //COMPARA OS HASHES
                $recordHash = ($record) ? $record->hash : null;
                if($recordHash != $info['hash']) {

                    //ICONE
                    $icone = $this->setIcon(mb_strtolower(trim($info['acao'])));

                    //GRAVA O REGISTRO
                    $rastreio = new TrackingHistory();
                    $rastreio->rastreio_id = $tracking;
                    $rastreio->data = $info['data'];
                    $rastreio->data_sql = $info['data_sql'];
                    $rastreio->local = trim($info['local']);
                    $rastreio->acao = trim($info['acao']);
                    $rastreio->detalhes = isset($info['detalhes']) ? trim($info['detalhes']) : '';
                    $rastreio->hash = $info['hash'];
                    $rastreio->save();

                    Tracking::where('numero', '=', $tracking)->update(array(
                            'status'      => trim($info['acao']),
                            'data_status' => $info['data_sql'],
                            'novo_status' => 1,
                            'icone'       => $icone
                        )
                    );

                    if(in_array(mb_strtolower($info['acao']), self::$statusConcluido) !== false){
                        Tracking::where('numero', '=', $tracking)->update(array('atualiza_status' => 0));
                    }

                    $hasUpdate = true;
                    $arrResponseTemp[] = ['updated' => true, 'hash' => $info['hash'], 'track' => $info];

                }
            }
        }

        $arrReponse = [
            'tracking' => $trackInfo->tracking,
            'description' => $trackInfo->description,
            'tracks' => ($hasUpdate) ? $arrResponseTemp : Helper::$messages[self::$i18n]['info.not.updated']
        ];

        if(!$local) Helper::response($arrReponse, 200);
        return $arrReponse;
    }

    /**
     * Atribui um icone especifico de acordo com o status do rastreio
     */
    private function setIcon($action){

        switch ($action) {
            case 'encaminhado':
            case 'objeto encaminhado':
            case 'objeto recebido na unidade de exportação':
            case 'objeto recebido na Unidade dos Correios':
                $icone = 2;
                break;
            case 'objeto postado':
                $icone = 5;
                break;
            case 'a entrega nÃ£o pode ser efetuada - carteiro nÃ£o atendido':
            case 'a entrega não pode ser efetuada - carteiro não atendido':
            case 'tentativa de entrega nÃ£o efetuada':
            case 'tentativa de entrega não efetuada':
                $icone = 11;
                break;
            case 'a entrega nÃ£o pode ser efetuada - cliente recusou-se a receber':
            case 'a entrega não pode ser efetuada - cliente recusou-se a receber':
                $icone = 7;
                break;
            case 'conferido':
            case 'objeto recebido pelos correios do brasil':
                $icone = 8;
                break;
            case 'entregue':
            case 'entrega efetuada':
            case 'objeto entregue ao destinatÃ¡rio':
            case 'objeto entregue ao destinatário':
                $icone = 9;
                break;
            case 'saiu para a entrega':
            case 'saiu para entrega':
            case 'objeto saiu para entrega ao destinatário':
            case 'objeto saiu para entrega ao destinatÃ¡rio':
            case 'objeto aguardando retirada no endereço indicado':
                $icone = 10;
                break;
            case 'objeto saiu para entrega ao remetente':   
                $icone = 11;
                break;
            case 'objeto devolvido ao remetente':
                $icone = 11;
                break;
            case 'aguardando pagamento':
                $icone = 6;
                break;
            case 'aguardando confirmação de pagamento':
                $icone = 13;
                break;
            case 'objeto pago':
            case 'objeto pago.':
                $icone = 12;
                break;
            default:
                $icone = 0;
                break;
        }

        return $icone;
    }

    /**
     * Envia a requisição de consulta ao servidor WEBSRO via cURL
     */
    private static function curlLoad($url = null)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return ($httpCode == self::SUCCESS_STATUS_CODE) ? $data : false;
    }

    /**
     * Envia a requisição de consulta ao servidor WEBSRO via file_get_contents, como alternativa
     */
    private static function fgetsLoad($url)
    {
        $arrContextOptions = [
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false
            ]
        ];  
        
        return file_get_contents($url, false, stream_context_create($arrContextOptions));
    }

    /**
     * Manipula o HTML retornado da consulta no servidor WEBSRO
     */
    private static function HtmlHandler($html)
    {
        if (strstr($html, '<table class="table table-bordered">') !== false) {

            //Limpa o codigo html
            $html = preg_replace("@\r|\t|\n| +@", ' ', $html);
            $html = str_replace('</tr>', "</tr>\n", $html);

            //Pega as linhas com o rastreamento
            if (preg_match_all('@<tr>(.*)</tr>@', $html, $mat, PREG_SET_ORDER)) {

                $temp  = null;
                $track = [];
                $mat   = array_reverse($mat);
                unset($mat[count($mat) - 1]);

                //Formata as linhas e gera um array
                foreach ($mat as $item) {
                    $item[0] = preg_replace("@\r|\t|\n| +@", ' ', $item[0]);

                    preg_match("@<td valign='top'>(.*)</td> <td>@", $item[0], $dateRaw);
                    $dateRaw = explode('<label>', str_replace('<br>', ' ',$dateRaw[1]));

                    //Date
                    $date    = $dateRaw[0];
                    $dateSQL = preg_replace('@([0-9]{2})/([0-9]{2})/([0-9]{4}) ([0-9]{2}):([0-9]{2})@', '$3-$2-$1 $4:$5:00', strip_tags($date));

                    //Local
                    preg_match("@</strong><br>(.*)</td>@", $item[0], $local);

                    if (strstr($local[1], ' para ')) {
                        $local = explode(' para ', $local[1]);
                        $from  = explode(' de ', $local[0]);

                        if (array_key_exists(1, $from)) {
                            $from  = trim($from[1]);
                        } else {
                            $from = '';
                        }

                        $to = trim($local[1]);
                    } else {
                        $from = '';
                    }

                    $to = trim($local[1]);

                    //Action
                    preg_match("@<strong>(.*)</strong>@", $item[0], $action);
                    $action = trim($action[1]);

                    //Cria uma linha de track
                    $tmp = [
                        'data'     => $date,
                        'data_sql' => $dateSQL,
                        'local'    => $from,
                        'acao'     => $action,
                        'detalhes' => strip_tags($to),
                        'hash'     => hash('md5', $date . $action)
                    ];
                    $track[] = $tmp;
                }
                
                return (object) ['success' => true, 'place' => 'updated', 'data' => $track];

            } else {

                return (object) ['success' => false, 'place' => 'info.not.updated', 'data' => null];
            }
        } else {
            
            return (object) ['success' => false, 'place' => 'untracktable', 'data' => null];

        }
    }

    public function insertTracking(Request $request)
    {
        if (!self::isAuthenticated()) {
            Helper::response('error.access.denied', 403, false);
        }

        if (!$request->isMethod('post')) {
            Helper::response('method.not.allowed', 403, false);
        }        

        $data = (object) $request->json()->all();

        $inputs = [
            'user' => $data->user,
            'tracking'  => $data->tracking,
            'descricao' => $data->description
        ];

        $rules = [
            'user' => 'required',
            'tracking' => 'required|unique:rastreio,numero|Min:13|Max:13|regex:/[A-Za-z]{2}(\d){9}[A-Za-z]/',
            'descricao' => 'required'
        ];

        $messages = [
            'required' => 'required.field.:attribute',
            'min' => 'required.fiel.min',
            'max' => 'required.field.max',
            'regex'=> 'error.tracking.format',
            'unique' => 'required.field.unique'
        ];

        $validator = Validator::make($inputs, $rules, $messages);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $arrErrors[] = $error;
            }

            if (!empty($arrErrors)) {
                Helper::response($arrErrors, 200, false, true);
            }

            Helper::response('error.exception', 200, false);
        }

        $trkModel = new Tracking();
        $trkModel->usuario_id = $data->user;
        $trkModel->numero = $data->tracking;
        $trkModel->descricao = $data->description;
        $trkModel->status = self::INITIAL_STATUS;
        $trkModel->data_status = self::STATUS_DATE;
        $trkModel->atualiza_status = self::FLAG_ACTIVE;
        $trkModel->novo_status = self::FLAG_ACTIVE;
        $trkModel->ativo = self::FLAG_ACTIVE;

        try {
            if($trkModel->save()){
                Helper::response('info.register.tracking.success');
            }else{
                Helper::response('info.register.tracking.failure', 200, false);
            }
        } catch(\Throwable $th) {
            Helper::response(['error.exception', $th->getMessage()], 200, false, true);
        }
        
    }

    public function userTrackingCodes($id = null, $debug = false, $local = false)
    {
        $debug = ($debug == 'false' || $debug == false) ? false : true;
        $local = ($local == 'false' || $local == false) ? false : true;

         //Verifica se o acesso é autenticado 
        if (!$debug && !self::isAuthenticated()) {
            Helper::response('error.access.denied', 403, false);
        }

        $validator = Validator::make(['id' => $id], ['id' => 'required'], ['required' => 'required.field.:attribute']);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $arrErrors[] = $error;
            }

            if (!empty($arrErrors)) {
                Helper::response($arrErrors, 200, false, true);
            }

            Helper::response('error.exception', 200, false);
        }

        $trkCodes = Tracking::where(function($query) use ($id) {
            $query->where('ativo', 1);
            $query->where('atualiza_status', 1);

            if ($id) {
                $query->where('usuario_id', $id);
            }
        })->orderBy('data_status')->get();

        if ($trkCodes->isEmpty()) {
            if (!$local) Helper::response('info.empty.result');
            return ['success' => false, 'http_code' => 200, 'data' => Helper::$messages[self::$i18n]['info.empty.result']];
        }

        if (!$local) Helper::response($trkCodes->all(), 200, true, true);
        return ['success' => true, 'http_code' => 200, 'data' => $trkCodes];
    }

    public function updateTrackingCodes($id = null, $debug = false)
    {
        $debug = ($debug == 'false' || $debug == false) ? false : true;

        //Verifica se o acesso é autenticado 
        if (!$debug && !self::isAuthenticated()) {
            Helper::response('error.access.denied', 403, false);
        }

        $validator = Validator::make(['id' => $id], ['id' => 'required'], ['required' => 'required.field.:attribute']);
        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $arrErrors[] = $error;
            }

            if (!empty($arrErrors)) {
                Helper::response($arrErrors, 200, false, true);
            }

            Helper::response('error.exception', 200, false);
        }

        $userTrkCodes = $this->userTrackingCodes($id, 'debug', 'local');
        if (!$userTrkCodes['success']) {
            Helper::response('info.empty.result', 200, false);
        }

        foreach ($userTrkCodes['data']->all() as $tracking) {
            
            //Helper::debug($tracking->numero);
            $arrResponse[] = $this->updateTracking($tracking->numero, 'local');
        }

        Helper::response($arrResponse, 200, true, true);

    }
}