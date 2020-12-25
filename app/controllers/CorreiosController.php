<?php

class CorreiosController extends BaseController {
        
    public function getIndex($user = null){

        $records = Tracking::where(function($query) use ($user) {
            $query->where('ativo', '=', '1');
            $query->where('atualiza_status', '=', '1');

            if($user){
                $query->where('usuario_id', '=', $user);
            }
        })
        ->orderBy('usuario_id')
        ->get();

        //BaseController::debug($records, true);
        if(count($records) > 0){
            foreach($records as $row){
                //BaseController::debug($row->numero);
                CorreiosController::updateWH($row->numero); //Lê a página em HTML
            }
        }
    }

    public function getDebug($user = null){

        $records = Tracking::where(function($query) use ($user) {
            $query->where('ativo', '=', '1');
            $query->where('atualiza_status', '=', '1');

            if($user){
                $query->where('usuario_id', '=', $user);
            }
        })
        ->orderBy('usuario_id')
        ->get();
        if(count($records) > 0){
            foreach($records as $row){
                //$row->numero = 'RM422645366CN';
                CorreiosController::updateWH($row->numero); //Lê a página em HTML
            }
        }
    }

    public function getTrackings($list = null, $user = null){
        $records = Tracking::where(function($query) use ($user) {
            $query->where('ativo', '=', '1');
            $query->where('atualiza_status', '=', '1');

            if($user){
                $query->where('usuario_id', '=', $user);
            }
        })
        ->orderBy('usuario_id')
        ->get();

        if(count($records) > 0){
            BaseController::debug("TOTAL: " . count($records) . " CODIGOS");

            if($list){
                foreach($records as $row){
                    BaseController::debug($row->numero);
                }
            }
        }
    }

    public function getUsers($list = null){
        $users = User::where('ativo', '=', '1')
        ->orderBy('id', 'DESC')
        ->get();

        if(count($users) > 0){
            BaseController::debug("TOTAL: " . count($users) . " USUARIOS");

            if($list){
                foreach($users as $row){
                    BaseController::debug("#{$row->id} - {$row->nome} - {$row->email}");
                }
            }
        }
    }

    public function getRogerio(){
        $this->getIndex(2);
    }

    public function getAdmin(){
        $this->getIndex(1);
    }

    public function getWs($codigo){
        CorreiosController::updateWS($codigo);
    }

    public static function getUpdate($id){
        CorreiosController::updateWH($id);
    }


    ////////////////////////////////////////////////////////////////////////////////
    //// WEBSRO ////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////

    /**
     *
     */
        
    public static function updateWH($id){
        BaseController::debug($id);

        $url = 'http://www.websro.com.br/detalhes.php?P_COD_UNI=';
        $html = file_get_contents($url . $id);
        $msgEntrega = [
            'entregue',
            'entrega efetuada',
            'objeto entregue ao destinatário'
        ];
        $sendEmail = false;

        //Verifica se o objeto ainda não foi postado
		if(strstr($html, '<table class="table table-bordered">') !== false){

            //Limpa o codigo html
            $html = preg_replace("@\r|\t|\n| +@", ' ', $html);
            $html = str_replace('</tr>', "</tr>\n", $html);

            //Pega as linhas com o rastreamento
            if(preg_match_all('@<tr>(.*)</tr>@', $html, $mat, PREG_SET_ORDER)){

                $temp  = null;
                $track = [];
                $mat   = array_reverse($mat);
                unset($mat[count($mat) - 1]);

                //Formata as linhas e gera um array
                foreach($mat as $item){
                    $item[0] = preg_replace("@\r|\t|\n| +@", ' ', $item[0]);

                    preg_match("@<td valign='top'>(.*)</td> <td>@", $item[0], $dateRaw);
                    $dateRaw = explode('<label>', str_replace('<br>', ' ',$dateRaw[1]));

                    //Date
                    $date    = $dateRaw[0];
                    $dateSQL = preg_replace('@([0-9]{2})/([0-9]{2})/([0-9]{4}) ([0-9]{2}):([0-9]{2})@', '$3-$2-$1 $4:$5:00', strip_tags($date));

                    //Local
                    preg_match("@</strong><br>(.*)</td>@", $item[0], $local);

                    if(strstr($local[1], ' para ')){
                        $local = explode(' para ', $local[1]);
                        $from  = explode(' de ', $local[0]);

                        if(array_key_exists(1, $from)){
                            $from  = trim($from[1]);
                        }else{
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
                    //BaseController::debug($track);
                }


                //////////////////////////////////////////////////
                //// GRAVA AS INFORMAÇOES ////////////////////////
                //////////////////////////////////////////////////
                //BaseController::debug($track, true);

                foreach($track as $info){

                    $record = TrackingHistory::where('rastreio_historico.rastreio_id', '=', $id)
                    ->where('rastreio_historico.hash', '=', $info['hash'])
                    ->orderBy('rastreio_historico.created_at', 'DESC')
                    ->orderBy('rastreio_historico.id', 'DESC')
                    ->first();

                    //COMPARA OS HASHES
                    $recordHash = ($record) ? $record->hash : null;
                    if($recordHash != $info['hash']) {

                        BaseController::debug("HASH: " . $info['hash']);
                        BaseController::debug($info);

                        //ICONE
                        $icone = CorreiosController::setIcon(mb_strtolower(trim($info['acao'])));

                        //GRAVA O REGISTRO
                        $rastreio = new TrackingHistory();
                        $rastreio->rastreio_id = $id;
                        $rastreio->data        = $info['data'];
                        $rastreio->data_sql    = $info['data_sql'];
                        $rastreio->local       = trim($info['local']);
                        $rastreio->acao        = trim($info['acao']);
                        $rastreio->detalhes    = isset($info['detalhes']) ? trim($info['detalhes']) : '';
                        $rastreio->hash        = $info['hash'];
                        $rastreio->save();

                        Tracking::where('numero', '=', $id)->update(array(
                                'status'      => trim($info['acao']),
                                'data_status' => $info['data_sql'],
                                'novo_status' => 1,
                                'icone'       => $icone
                            )
                        );

                        if(in_array(mb_strtolower($info['acao']), $msgEntrega) !== false){
                            Tracking::where('numero', '=', $id)->update(array('atualiza_status' => 0));
                        }
                        /////ENVIO DO EMAIL //////////////////
                        $sendEmail = true;

                    }
                }

                //////////////////////////////////////
                /////ENVIO DO EMAIL //////////////////
                //////////////////////////////////////
                if($sendEmail){

                    $dados = Tracking::where('numero', '=', $id)->first();
                    $user  = User::where('id', '=', $dados->usuario_id)->first();

                    $data = array(
                        'cod_rastreio' => $id,
                        'description'  => $dados->descricao,
                        'detail'	   => $info['acao'] . ((strlen($info['detalhes']) > 0) ? ' - ' . $info['detalhes'] : '')
                    );

                    $rcpto = explode(';', $user->email);
                    if(! is_array($rcpto))
                        $rcpto = array($rcpto);

                    #Mail::send('emails.rastreio', $data, function($message) use ($user, $rcpto){
                    #    $message->from('rastreio@cooltracker.com.br', 'Rastreio - Cooltracker');
                    #    $message->to($rcpto)->subject('Novidades sobre sua encomenda!! ' . date('d/m/Y H:i:s'));
                    #});

                    //ENVIO DA NOTIFICACAO///////////////
                    BaseController::debug('Notificacao');
                    //if($user->id <= 2){
                        $fcmTopic = $user->id;
                        $titulo = 'Novidades sobre sua encomenda!!!';
                        $mensagem = "A sua encomenda {$id} recebeu uma atualização de status. Clique aqui para ver!";

                        echo '<pre>';
                        CorreiosController::sendNotification($titulo, $mensagem, $fcmTopic);
                        echo '</pre>';
                    //}

                    BaseController::debug('Atualizou');
                    BaseController::debug('============================================');

                    return true;

                } else {
                    BaseController::debug('Nenhuma novidade. Nao foi atualizado');
                    BaseController::debug('============================================');
                }
                ////////////////////////////////////////////
                ///FIM ENVIO EMAIL
                ////////////////////////////////////////////

            } else {
                BaseController::debug('Nao rastreavel. Nao foi atualizado. Cod 2');
                BaseController::debug('============================================');
                return true;
            }
        } else {
            BaseController::debug('Nao rastreavel. Nao foi atualizado. Cod 1');
            BaseController::debug('============================================');
            return true;
        }

    }

    /*/
    ///// FIM WEBSRO /////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////

    ////////////////////////////////////////////
    ///WEBSERVICE //////////////////////////////
    ////////////////////////////////////////////


    /**
     * 
    
    
     */

    /////FIM WEBSERVICE////////////////////////////////
    ///////////////////////////////////////////////////


    /////////////////////////////////////////////////////
    ///// SET ICON //////////////////////////////////////
    /////////////////////////////////////////////////////
    public static function setIcon($action){

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
	
	function getStatus(){
		return $this->status;
	}
	
	function getDataStatus(){
		return $this->data;
	}
	
	function getDataSqlStatus(){
		return $this->data_sql;
	}
	
	function insereUser($dados){
		$sql = '';
	}
	
	function getHash(){
		return $this->hash;
	}
	
	function insereTracking($dados){
		$tracking  = trim($dados['tracking']);
		$descricao = $dados['descricao'];
		$usuario   = $dados['usuario'];
		$status    = $dados['status'];
		$data_sql  = $dados['data_sql'];
		
		if(!$this->isTrack($tracking)){
			$sql = "INSERT INTO rastreio (trk_usu_id, trk_numero, trk_descricao, trk_status, trk_data_status) VALUES ('{$usuario}','{$tracking}','$descricao', '{$status}', '{$data_sql}')";
			$res = mysql_query($sql);
			//print_r($sql); exit();
			if($this->isTrack($tracking)){
				header("Location: index.php?erro=false");	
			}else{
				header("Location: index.php?erro=true");
			}
		}else{
			header("Location: index.php?erro=O objeto inserido já existe");
		}
		
	}
	
	function isTrack($trk){
		$sql  = "
			SELECT * FROM rastreio WHERE trk_numero = '{$trk}' AND trk_ativo = 1";
		$res  = mysql_query($sql);
		$rows = mysql_num_rows($res);
		return ($rows > 0) ? true : false;
	}
	
	function getTracks($usu_id){
		include("conexao.inc.php");
		$sql = "
			SELECT 
				*,
				DATE_FORMAT(trk_data, '%d/%m/%Y') AS data,
				DATE_FORMAT(trk_data_status, '%d/%m/%Y') AS data_status
			FROM
				rastreio
			WHERE
				trk_usu_id = '{$usu_id}' AND
				trk_ativo = 1
			ORDER BY
				trk_atualiza_status DESC,
				trk_data DESC
		";
		//print_r($sql); exit();
		$res  = mysql_query($sql);
		return $res;
	}
	
	function delTrack($dados){
		$track_id  = trim($dados['track_id']);
		$usuario   = $dados['usuario'];
		$tracking  = trim($dados['trk_numero']);
		
		$sql = "UPDATE rastreio SET trk_ativo = 0, trk_atualiza_status = 0 WHERE trk_id = '{$track_id}'";
		$res = mysql_query($sql);
		
		if(!$this->isTrack($tracking)){
			header("Location: index.php");	
		}else{
			header("Location: index.php");
		}
	}
	
	function upHash($trk_id, $trk_status, $trk_data_status){
		$atualiza_status = ($trk_status == 'entrega efetuada') ? ', trk_atualiza_status = 0' : '';
		$sql = "UPDATE rastreio SET trk_status = '{$trk_status}', trk_data_status = '{$trk_data_status}' {$atualiza_status} WHERE trk_id = '{$trk_id}'";
		//print_r($sql); exit();
		$res = mysql_query($sql);
	}
	
	function upStatusView($track){
		$sql = "UPDATE rastreio SET trk_novo_status=1 WHERE trk_numero = '{$track}' AND trk_ativo=1";
		$res = mysql_query($sql);
	}
	
	function visualizado($track){
		$sql = "UPDATE rastreio SET trk_novo_status=0 WHERE trk_numero = '{$track}' AND trk_ativo=1";
		$res = mysql_query($sql);
	}

    function atualizaEntrega(){
        $records = Tracking::where('atualiza_status', '=', '1')
        ->where('ativo', '=', '1')
        ->orderBy('usuario_id')
        ->get();

        if(count($records) > 0){
            foreach($records as $row){
                CorreiosController::update($row->numero);
            }
        }
    }


    public function getHasher(){
        
        echo '
            <html>
                <body>
                    <div style="width:100%; position:relative; margin-top: 200px;">
                        <div style="margin: auto; width: 246px; padding: 10px; border: 2px solid #73AD21">
                            <br>
                            Gerador de Hash
                            <br>
                            <form method="POST">
                                <input id="login-password" type="password" name="senha" required="" class="input-material"><br>
                                <button type="submit" id="login" class="btn btn-primary">Gerar Hash</button>
                            </form>
                        </div>
                    </div>
                </body>
            </html>
        ';

    }

    public function postHasher(){

        echo Hash::make(Input::get('senha'));
        exit();

    }



    //////////////////////////////////////
    // WEBSERVICE APP ////////////////////
    //////////////////////////////////////

    //Obtem os rastreios cadastrados
    public function getDashboard($user, $token){

        if($token == 'a65e5sa69weesadf4'){

            $records = Tracking::where('usuario_id', '=', $user)
            ->where('ativo', '=', 1)
            ->where('arquivado', '=', 0)
            //->orderBy('novo_status', 'DESC')
            ->orderBy('atualiza_status', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

            $total = Tracking::where('usuario_id', '=', $user)
            ->where('ativo', '=', 1)
            ->where('arquivado', '=', 0)
            ->count();

            if($total > 0) {

                $arrAnswer['success'] = true;
                $arrAnswer['total']   = $total;

                foreach($records as $row) {

                    $flag = strtolower(substr($row->numero, -2));
                    $arrAnswer['data'][] = [
                        'tracking'    => $row->numero,
                        'description' => $row->descricao,
                        'date'        => $row->data_status,
                        'action'      => $row->status,
                        'novo_status' => $row->novo_status,
                        'smile'       => "$row->icone",
                        'flag'        => $flag
                    ];

                }

            }else{

                $arrAnswer['success'] = false;
                $arrAnswer['total']   = 0;
                $arrAnswer['data']    = null;

            }

        } else {

            $arrAnswer['success'] = false;
            $arrAnswer['total']   = 0;
            $arrAnswer['data']    = null;

        }

        echo json_encode($arrAnswer);
    }

    public function getService($user, $token){

        if($token == 'a65e5sa69weesadf4'){

            $records = Tracking::where('usuario_id', '=', $user)
            ->where('ativo', '=', 1)
            ->where('arquivado', '=', 0)
            //->orderBy('novo_status', 'DESC')
            ->orderBy('atualiza_status', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

            $arrAnswer = [];

            if($records) {

                foreach($records as $row) {

                    $flag = strtolower(substr($row->numero, -2));
                    $arrAnswer[] = [
                        'tracking'    => $row->numero,
                        'description' => $row->descricao,
                        'date'        => $row->data_status,
                        'action'      => $row->status,
                        'novo_status' => $row->novo_status,
                        'smile'       => "$row->icone",
                        'flag'        => $flag
                    ];

                }

            }

        }

        echo json_encode($arrAnswer);
    }

    //Obtem os detalhes do rastreio
    public function getDetails($tracking, $token) {

        if($token == 'asdf564e879w632sdf56'){

            $total = TrackingHistory::where('rastreio_id', '=', $tracking)->count();
        
            $records = TrackingHistory::where('rastreio_id', '=', $tracking)
            ->orderBy('data_sql', 'DESC')
            ->orderBy('acao', 'ASC')
            ->get();

            if($total > 0) {

                $arrAnswer['success'] = true;
                $arrAnswer['total']   = $total;

                foreach($records as $row){

                    $arrAnswer['data'][] = [
                        'tracking' => $row->rastreio_id,
                        'date'     => $row->data,
                        'from'     => $row->local,
                        'to'       => $row->acao,
                        'details'  => $row->detalhes
                    ];

                }

            } else {

                $arrAnswer['success'] = false;
                $arrAnswer['total']   = 0;
                $arrAnswer['data']    = null;

            }

        }else {

            $arrAnswer['success'] = false;
            $arrAnswer['total']   = 0;
            $arrAnswer['data']    = null;

        }

        echo json_encode($arrAnswer);

    }

    //Marca as mensagens como Lidas
    public function getRead($tracking, $token){

        if($token == 'a5f6ad56e9w87we9856') {

            $update = Tracking::where('numero', '=', $tracking)->update(array('novo_status' => 0));

            $arrAnswer['success'] = true;
            $arrAnswer['total']   = 1;
            $arrAnswer['data']    = $update;

        } else {

            $arrAnswer['success'] = false;
            $arrAnswer['total']   = 0;
            $arrAnswer['data']    = null;

        }

        echo json_encode($arrAnswer);

    }

    //Valida a autenticação do usuário
    public function getLogin($usuario, $senha, $token) {

        //URL TESTE
        //http://cooltracker.com.br/correios/login/dGVzdGVAdGVzdGUuY29tLmJy/MTIz/w9e78rds64f56

        if($token == 'w9e78rds64f56') {

            $usuario = base64_decode($usuario);
            $senha   = base64_decode($senha);

            //Prepara para o login
            $type = (filter_var($usuario, FILTER_VALIDATE_EMAIL)) ? 'email' : 'login';

            $credentials = [
                $type      => $usuario,
                'password' => $senha
            ];

            if(Auth::attempt($credentials)){

                if(Auth::user()->deleted_at){

                    $arrAnswer['success'] = false;
                    $arrAnswer['total']   = 0;
                    $arrAnswer['data'][]  = ['msg' => 'Usuário desabilitado'];

                } else {

                    $arrAnswer['success'] = true;
                    $arrAnswer['total']   = 1;
                    $arrAnswer['data'][]  = ['id' => Auth::user()->id];

                }

            }else{

                $arrAnswer['success'] = false;
                $arrAnswer['total']   = 0;
                $arrAnswer['data'][]  = ['msg' => 'Nome de usuário ou senha inválidos'];

            }

        }else{

            $arrAnswer['success'] = false;
            $arrAnswer['total']   = 0;
            $arrAnswer['data']    = null;

        }

        echo json_encode($arrAnswer);

    }

    public function getCadastro($user, $tracking, $descricao, $token){

        if($token == 'xzc45vfa9f7lp54z65cvx665'){

            $tracking  = strtoupper($tracking);
            $descricao = base64_decode($descricao);

            $inputs = [
                'tracking'  => $tracking,
                'descricao' => $descricao
            ];

            $rules = [
                'tracking' => 'required|Min:13|Max:13',
                'descricao' => 'required'
            ];
            
            $messages = [
                'required' => 'O campo :attribute é obrigatório.',
                'min' => 'O campo :attribute deve conter 13 caracteres.',
                'max' => 'O campo :attribute deve conter 13 caracteres.'
            ];
            
            $validator = Validator::make($inputs, $rules, $messages);

            if ($validator->fails()) {
    
                $arrErrors = json_decode($validator->errors()->toJson());

                foreach($arrErrors as $error){
                    $arrMsg[] = ['msg' => str_replace('tracking', 'Código de Rastreio', $error[0])];
                }

                $arrAnswer['success'] = false;
                $arrAnswer['data']    = $arrMsg;

            } else {
            
                $mTracking = new Tracking();
                $mTracking->usuario_id = $user;
                $mTracking->numero = $tracking;
                $mTracking->descricao = $descricao;
                $mTracking->status = 'nao rastreavel';
                $mTracking->data_status = '1970-01-01 00:00:00';
                $mTracking->atualiza_status = 1;
                $mTracking->novo_status = 1;
                $mTracking->ativo = 1;

                if($mTracking->save()){

                    $arrAnswer['success'] = true;
                    $arrAnswer['data'][]  = ['msg' => 'Encomenda cadastrada!'];

                }else{

                    $arrAnswer['success'] = false;
                    $arrAnswer['data'][]  = ['msg' => 'Erro ao cadastrar a encomenda!'];

                }

            }

        } else {

            $arrAnswer['success'] = false;
            $arrAnswer['data'][]  = ['msg' => ''];

        }

        echo json_encode($arrAnswer);

    }

    public function getRegister($name, $user, $pass, $token){

        //URL TESTE
        //http://cooltracker.com.br/correios/register/Um9nZXJpbw==/dGVzdGVAdGVzdGUuY29tLmJy/MTIz/r5ek8st48eyr87dso47w

        if($token == 'r5ek8st48eyr87dso47w'){

            $name  = base64_decode($name);
            $user  = base64_decode($user);
            $pass  = Hash::make(base64_decode($pass));

            $inputs = [
                'nome'  => $name,
                'login' => $user,
                'senha' => $pass,
                'email' => $user
            ];

            $rules = [
                'nome'  => 'required|min:5',
                'login' => 'required|min:5',
                'senha' => 'required|min:6',
                'email' => 'required|email|unique:usuarios,email'
            ];
            
            $messages = [
                'required' => 'O campo :attribute é obrigatório.',
                'min'      => 'O campo :attribute deve conter :min caracteres.',
                'unique'   => 'O :atribute já existe'
            ];
            
            $validator = Validator::make($inputs, $rules, $messages);

            if ($validator->fails()) {
    
                $arrErrors = json_decode($validator->errors()->toJson());

                foreach($arrErrors as $error){
                    $arrMsg[] = ['msg' => $error[0]];
                }

                $arrAnswer['success'] = false;
                $arrAnswer['data']    = $arrMsg;

            } else {
            
                $mUser = new User();
                $mUser->nome = $name;
                $mUser->login = $user;
                $mUser->senha = $pass;
                $mUser->email = $user;

                if($mUser->save()){

                    $arrAnswer['success'] = true;
                    $arrAnswer['data'][]  = ['msg' => 'Usuário cadastrado!'];

                }else{

                    $arrAnswer['success'] = false;
                    $arrAnswer['data'][]  = ['msg' => 'Erro ao cadastrar o usuário!'];

                }

            }

        } else {

            $arrAnswer['success'] = false;
            $arrAnswer['data'][]  = ['msg' => ''];

        }

        echo json_encode($arrAnswer);

    }

    //Esqueci a senha
    public function getForgot($email, $token){
        
        //URL TESTE
        //http://cooltracker.com.br/correios/forgot/dGVzdGVAdGVzdGUuY29tLmJy/re54gex5na45eazta87ew8gosj71dtoa7ea78sa

        if($token == 're54gex5na45eazta87ew8gosj71dtoa7ea78sa'){
            $email  = base64_decode($email);
            $remember_token = strtoupper(substr(md5('cooltracker_'.date('d-m-Y h:i:s')), 10, 5));

            $user = User::where('email', '=', $email)->get();

            if($user) {
                $update = User::where('email', '=', $email)->update(['remember_token' => $remember_token]);

                $data = array(
                    'remember_token' => $remember_token
                );

                Mail::send('emails.forgot', $data, function($message) use ($user, $email){
                    $message->from('rastreio@cooltracker.com.br', 'Rastreio - Cooltracker');
                    $message->to($email)->subject('Cooltracker - Lembrete de senha ' . date('d/m/Y H:i:s'));
                });

                $arrAnswer['success'] = true;
                $arrAnswer['data'][]  = ['msg' => 'Email enviado com sucesso!!'];

            } else {
                $arrAnswer['success'] = false;
                $arrAnswer['data'][]  = ['msg' => 'Usuário não encontrado'];
            }

        } else {
            $arrAnswer['success'] = false;
            $arrAnswer['data'][]  = ['msg' => 'Permissão negada'];
        }

        echo json_encode($arrAnswer);
    }

    public function getReset($codigo, $senha, $token){
        
        //URL TESTE
        //http://cooltracker.com.br/correios/reset/ ~ /mi04gex3le47eazna757ew4dej30dtli2ea14ci2e37a

        if($token == 'mi04gex3le47eazna757ew4dej30dtli2ea14ci2e37a'){
            $remember_token  = strtoupper(base64_decode($codigo));
            $senha           = Hash::make(base64_decode($senha));
            $expires         = strtoupper(substr(md5('cooltracker_'.date('d-m-Y h:i:s')), 10, 5));

            $user = User::where('remember_token', '=', $remember_token)->get();

            if($user) {
                $update = User::where('remember_token', '=', $remember_token)->update([
                    'senha'          => $senha,
                    'remember_token' => $expires
                ]);

                $arrAnswer['success'] = true;
                $arrAnswer['data'][]  = ['msg' => 'Senha alterada com sucesso!!'];

            } else {
                $arrAnswer['success'] = false;
                $arrAnswer['data'][]  = ['msg' => 'Usuário não encontrado'];
            }

        } else {
            $arrAnswer['success'] = false;
            $arrAnswer['data'][]  = ['msg' => 'Permissão negada'];
        }

        echo json_encode($arrAnswer);
    }

    public function getUpdateTracking($user, $tracking_old, $tracking, $descricao, $token){

        if($token == 're04gex3na23eazta7ex507ew4tej21dtli4su17c2e17da57'){
            $tracking      = strtoupper($tracking);
            $tracking_old  = strtoupper($tracking_old);
            $descricao     = base64_decode($descricao);

            $inputs = [
                'tracking'  => $tracking,
                'descricao' => $descricao
            ];

            $rules = [
                'tracking' => 'required|Min:13|Max:13',
                'descricao' => 'required'
            ];
            
            $messages = [
                'required' => 'O campo :attribute é obrigatório.',
                'min' => 'O campo :attribute deve conter 13 caracteres.',
                'max' => 'O campo :attribute deve conter 13 caracteres.'
            ];
            
            $validator = Validator::make($inputs, $rules, $messages);

            if ($validator->fails()) {
    
                $arrErrors = json_decode($validator->errors()->toJson());

                foreach($arrErrors as $error){
                    $arrMsg[] = ['msg' => str_replace('tracking', 'Código de Rastreio', $error[0])];
                }

                $arrAnswer['success'] = false;
                $arrAnswer['data']    = $arrMsg;

            } else {
            
                $UpdateTracking = Tracking::where('numero', '=', $tracking_old)
                ->where('usuario_id', '=', $user)
                ->update([
                    'numero'    => $tracking,
                    'descricao' => $descricao
                ]);
                
                $arrAnswer['success'] = true;
                $arrAnswer['data'][]  = ['msg' => 'Encomenda atualizada!'];

            }

        } else {
            $arrAnswer['success'] = false;
            $arrAnswer['data'][]  = ['msg' => 'Permissão negada'];
        }

        echo json_encode($arrAnswer);
    }

    public function getDeleteTracking($user, $tracking, $token){

        if($token == 'su04src3e423llen5zght7ex507te4t4j21dtli4su74c2e17da57'){
            $tracking = strtoupper($tracking);

            $inputs = [
                'user'      => $user,
                'tracking'  => $tracking
            ];

            $rules = [
                'user'     => 'required',
                'tracking' => 'required|Min:13|Max:13'
            ];
            
            $messages = [
                'required' => 'O campo :attribute é obrigatório.'
            ];
            
            $validator = Validator::make($inputs, $rules, $messages);

            if ($validator->fails()) {
    
                $arrErrors = json_decode($validator->errors()->toJson());

                foreach($arrErrors as $error){
                    $arrMsg[] = ['msg' => str_replace('tracking', 'Código de Rastreio', $error[0])];
                }

                $arrAnswer['success'] = false;
                $arrAnswer['data']    = $arrMsg;

            } else {
            
                $UpdateTracking = Tracking::where('numero', '=', $tracking)
                ->where('usuario_id', '=', $user)
                ->update([
                    'ativo'           => 0,
                    'atualiza_status' => 0
                ]);
                
                $arrAnswer['success'] = true;
                $arrAnswer['data'][]  = ['msg' => 'Encomenda excluída!'];

            }

        } else {
            $arrAnswer['success'] = false;
            $arrAnswer['data'][]  = ['msg' => 'Permissão negada'];
        }

        echo json_encode($arrAnswer);
    }

    public function getUpdateStorage($user, $tracking, $token){

        if($token == 'fe04src3e423ll5te5zght7507su4t4j21dl4dx74c2da1757'){
            $tracking = strtoupper($tracking);

            $inputs = [
                'user'      => $user,
                'tracking'  => $tracking
            ];

            $rules = [
                'user'     => 'required',
                'tracking' => 'required|Min:13|Max:13'
            ];
            
            $messages = [
                'required' => 'O campo :attribute é obrigatório.'
            ];
            
            $validator = Validator::make($inputs, $rules, $messages);

            if ($validator->fails()) {
    
                $arrErrors = json_decode($validator->errors()->toJson());

                foreach($arrErrors as $error){
                    $arrMsg[] = ['msg' => str_replace('tracking', 'Código de Rastreio', $error[0])];
                }

                $arrAnswer['success'] = false;
                $arrAnswer['data']    = $arrMsg;

            } else {
            
                $UpdateTracking = Tracking::where('numero', '=', $tracking)
                ->where('usuario_id', '=', $user)
                ->update([
                    'arquivado' => 1
                ]);
                
                $arrAnswer['success'] = true;
                $arrAnswer['data'][]  = ['msg' => 'Encomenda arquivada!'];

            }

        } else {
            $arrAnswer['success'] = false;
            $arrAnswer['data'][]  = ['msg' => 'Permissão negada'];
        }

        echo json_encode($arrAnswer);
    }

    //Obtem os rastreios arquivados
    public function getStoraged($user, $token){

        if($token == '48mar4f5wx320li655xe4rde549x0li534w5ci5r454a69'){

            $records = Tracking::where('usuario_id', '=', $user)
            ->where('ativo', '=', 1)
            ->where('arquivado', '=', 1)
            //->orderBy('novo_status', 'DESC')
            ->orderBy('atualiza_status', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

            $total = Tracking::where('usuario_id', '=', $user)
            ->where('ativo', '=', 1)
            ->where('arquivado', '=', 1)
            ->count();

            if($total > 0) {

                $arrAnswer['success'] = true;
                $arrAnswer['total']   = $total;

                foreach($records as $row) {

                    $flag = strtolower(substr($row->numero, -2));
                    $arrAnswer['data'][] = [
                        'tracking'    => $row->numero,
                        'description' => $row->descricao,
                        'date'        => $row->data_status,
                        'action'      => $row->status,
                        'novo_status' => $row->novo_status,
                        'smile'       => "$row->icone",
                        'flag'        => $flag
                    ];

                }

            }else{

                $arrAnswer['success'] = false;
                $arrAnswer['total']   = 0;
                $arrAnswer['data']    = null;

            }

        } else {

            $arrAnswer['success'] = false;
            $arrAnswer['total']   = 0;
            $arrAnswer['data']    = null;

        }

        echo json_encode($arrAnswer);
    }

    public function getRecover($user, $tracking, $token){

        if($token == 'su04src3e423llen5zght7ex507te4t4j21dtli4su74c2e17da57'){
            $tracking = strtoupper($tracking);

            $inputs = [
                'user'      => $user,
                'tracking'  => $tracking
            ];

            $rules = [
                'user'     => 'required',
                'tracking' => 'required|Min:13|Max:13'
            ];
            
            $messages = [
                'required' => 'O campo :attribute é obrigatório.'
            ];
            
            $validator = Validator::make($inputs, $rules, $messages);

            if ($validator->fails()) {
    
                $arrErrors = json_decode($validator->errors()->toJson());

                foreach($arrErrors as $error){
                    $arrMsg[] = ['msg' => str_replace('tracking', 'Código de Rastreio', $error[0])];
                }

                $arrAnswer['success'] = false;
                $arrAnswer['data']    = $arrMsg;

            } else {
            
                $UpdateTracking = Tracking::where('numero', '=', $tracking)
                ->where('usuario_id', '=', $user)
                ->update([
                    'arquivado' => 0
                ]);
                
                $arrAnswer['success'] = true;
                $arrAnswer['data'][]  = ['msg' => 'Encomenda desarquivada!'];

            }

        } else {
            $arrAnswer['success'] = false;
            $arrAnswer['data'][]  = ['msg' => 'Permissão negada'];
        }

        echo json_encode($arrAnswer);
    }

    public function getUpdateFcmToken($user, $fcmtoken, $token){

        if($token == 'mar04src3e173ci547rgex670a32483te5zght707su4t4j21dl4dx10c2da156'){

            $inputs = [
                'fcm_token' => $fcmtoken
            ];

            $rules = [
                'fcm_token' => 'required'
            ];
            
            $messages = [
                'required' => 'O campo :attribute é obrigatório.'
            ];
            
            $validator = Validator::make($inputs, $rules, $messages);

            if ($validator->fails()) {
    
                $arrErrors = json_decode($validator->errors()->toJson());

                foreach($arrErrors as $error){
                    $arrMsg[] = ['msg' => str_replace('tracking', 'Código de Rastreio', $error[0])];
                }

                $arrAnswer['success'] = false;
                $arrAnswer['data']    = $arrMsg;

            } else {
            
                $UpdateUser = User::where('id', '=', $user)
                ->update([
                    'fcm_token' => $fcmtoken
                ]);
                
                $arrAnswer['success'] = true;
                $arrAnswer['data'][]  = ['msg' => 'Token atualizado!'];

            }

        } else {
            $arrAnswer['success'] = false;
            $arrAnswer['data'][]  = ['msg' => 'Permissão negada'];
        }

        echo json_encode($arrAnswer);
    }

    static function sendNotification($title, $message, $id = 0) {
        #API access key from Google API's Console
        define( 'API_ACCESS_KEY', 'AAAA3h7MrFc:APA91bE8cay1XJkaHfJgvY4_O-xupadoZPZOAIRLIVqGzvL_ZUOtBUYFa24dDqfwLZXbyBpQjbXU4LBEOeMVJt7vubdO_AsYkzHmb4omYGgA5iDwjVFgq2zUBUjSum5eKRL-BicJ05TO');
        //$id = ['dUy90D_3Lpg:APA91bE_2_cFFyB1oIQE46R6YKGGNZz12Pbl6mrPKWzuaDsZbaZe9ffBMm72SwmxIMth2r2DOQtL0EUw7_v1HRvl4YXIQVn_9t5Vdqij5brN_BZDoQCbC_r_mhc9egYj4cT2FaIBRZaS'];
        $registrationIds = [$id];
        $topic = $id;
        $url = 'https://fcm.googleapis.com/fcm/send';
        
        #prep the bundle
        $msg = [
            'body' 	=> $message,
            'title' => $title,
        ];

        $fields = [
            'to'           => '/topics/user-' . $topic,
            'notification' => $msg
        ];
        
        $headers = [
            'Authorization: key=' . API_ACCESS_KEY,
            'Content-Type: application/json'
        ];

        #Send Reponse To FireBase Server	
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($fields));

        $result = curl_exec($ch);

        curl_close($ch);

        if($result === false){
            echo 'Curl error: ' . curl_error($ch);
        }else{
            echo 'Notificacao enviada! - user:' . $topic;
            //echo "RESULTADO<br>";
            //echo $result;
        }
    }

    function getNotification($id){
        $user = User::where('id', '=', $id)->first();
        if($user){
            $chave = $id; //$user->fcm_token;
            $title = "Novidades sobre sua encomenda!!";
            $message = "A sua encomenda OG154768547BR recebeu uma atualização. Clique para ver!!!";

            BaseController::debug($chave);
            CorreiosController::sendNotification($title, $message, $chave);
        } else {
            BaseController::debug('Nao foi possivel encontrar o usuario');
        }
    }

    function getTopic($id, $token){
        
        if($token == 'ro04src3t173si547rgex670bu32b48s3ce5zght707tu4t4j21x10c2da156'){

            $inputs = [
                'id' => $id
            ];

            $rules = [
                'id' => 'required'
            ];
            
            $messages = [
                'required' => 'O campo :attribute é obrigatório.'
            ];
            
            $validator = Validator::make($inputs, $rules, $messages);

            if ($validator->fails()) {
    
                $arrErrors = json_decode($validator->errors()->toJson());
                foreach($arrErrors as $error){
                    $arrMsg[] = ['msg' => str_replace('tracking', 'Código de Rastreio', $error[0])];
                }
                $arrAnswer['success'] = false;
                $arrAnswer['data']    = $arrMsg;

            } else {
            
                $user = User::where('id', '=', $id)->first();
                
                //if($user->fcm_topic){
                if($user){
                    $arrAnswer['success'] = true;
                    $arrAnswer['data'][]  = ['msg' => 'Topico encontrado: ' . $user->id];
                } else {
                    $arrAnswer['success'] = false;
                $arrAnswer['data'][]  = ['msg' => 'Topico não encontrado'];
                }

            }

        } else {
            $arrAnswer['success'] = false;
            $arrAnswer['data'][]  = ['msg' => 'Permissão negada'];
        }

        echo json_encode($arrAnswer);
    }

    public function getUpdateFcmTopic($user, $token){

        if($token == 'mar04src3e173ci547rgex670a32483te5zght707su4t4j21dl4dx10c2da156'){

            $inputs = [
                'id' => $user
            ];

            $rules = [
                'id' => 'required'
            ];
            
            $messages = [
                'required' => 'O campo :attribute é obrigatório.'
            ];
            
            $validator = Validator::make($inputs, $rules, $messages);

            if ($validator->fails()) {
    
                $arrErrors = json_decode($validator->errors()->toJson());

                foreach($arrErrors as $error){
                    $arrMsg[] = ['msg' => str_replace('tracking', 'Código de Rastreio', $error[0])];
                }

                $arrAnswer['success'] = false;
                $arrAnswer['data']    = $arrMsg;

            } else {
            
                $UpdateUser = User::where('id', '=', $user)->update([
                    'fcm_topic' => 'user-' . $user
                ]);
                
                $arrAnswer['success'] = true;
                $arrAnswer['data'][]  = ['msg' => 'Topico atualizado!'];

            }

        } else {
            $arrAnswer['success'] = false;
            $arrAnswer['data'][]  = ['msg' => 'Permissão negada'];
        }

        echo json_encode($arrAnswer);
    }

    public function getPolicy(){
        return View::make('correios.policy');
    }

}

