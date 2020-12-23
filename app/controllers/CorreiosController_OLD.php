<?php

class CorreiosController extends BaseController {
        
    public function getIndex(){
        $records = Tracking::where('atualiza_status', '=', '1')
        ->where('ativo', '=', '1')
        ->orderBy('usuario_id')
        ->get();

        //BaseController::debug($records, true);
        if(count($records) > 0){
            foreach($records as $row){
                //BaseController::debug($row->numero);
                CorreiosController::update($row->numero);
            }
        }
    }
    
	public static function update($id){

        BaseController::debug($id);
            
		//$html = file_get_contents('http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI=' . $id);
		$html = file_get_contents('http://www.websro.com.br/detalhes.php?P_COD_UNI=' . $id);

        // Verifica se o objeto ainda não foi postado
		if(strstr($html, '<table class="table table-bordered">') !== false){

            // Limpa o codigo html
            $html = preg_replace("@\r|\t|\n| +@", ' ', $html);
            $html = str_replace('</tr>', "</tr>\n", $html);

            // Pega as linhas com o rastreamento
            if (preg_match_all('@<tr>(.*)</tr>@', $html, $mat, PREG_SET_ORDER)){

                $track = array();
                $mat = array_reverse($mat);
                unset($mat[0]);
                unset($mat[count($mat)]);
                //BaseController::debug($mat, true);

                $temp = null;

                // Formata as linhas e gera um vetor
                foreach($mat as $item){
                    $item[0] = preg_replace("@\r|\t|\n| +@", ' ', $item[0]);

                    if(preg_match("@<td rowspan=\"2\">(.*)</td>@", $item[0], $d)){

                        // Cria uma linha de track
                        $arrDate = explode(' ', $d[1]);
                        $date = $arrDate[0] . ' ' . strip_tags($arrDate[1]) . ':00';

                        $arrData = preg_split('@([0-9]{2})/([0-9]{2})/([0-9]{4}) ([0-9]{2}):([0-9]{2})@', $d[1]);
                        BaseController::debug($arrData, true);

                        $tmp = array(
                            'data'     => $date,
                            'data_sql' => preg_replace('@([0-9]{2})/([0-9]{2})/([0-9]{4}) ([0-9]{2}):([0-9]{2})@', '$3-$2-$1 $4:$5', $date),
                            'local'    => '',
                            'acao'     => strtolower(strip_tags($arrData[1])),
                            'detalhes' => '',
                            'hash'     => hash('md5', $date)
                        );

                        // Se tiver um encaminhamento armazenado
                        if (isset($tempDestino)){
                            $tmp['detalhes'] = strip_tags($tempDestino);
                            $tempDestino    = null;
                        }

                        if (isset($tempOrigem)){
                            $tmp['local'] = strip_tags($tempOrigem);
                            $tempOrigem    = null;
                        }

                        // Adiciona o item na lista de rastreamento
                        $track[] = $tmp;

                    }elseif(preg_match("@<td colspan=\"2\">(.*)</td>@", $item[0], $d)){

                        $tempOrigem = $d[1];
                        //BaseController::debug($tempOrigem, true);

                    }elseif(preg_match("@<td>(.*)</td>@", $item[0], $d)){

                        $dados = explode('</td> <td>', $d[1]);

                        if(count($dados) > 1) {
                            $tempOrigem  = $dados[0];
                            $tempDestino = $dados[1];

                            //BaseController::debug($tempOrigem);
                        } else {
                            $tempDestino = $dados[0];
                        }

                        //BaseController::debug($tempDestino, true);

                    }

                }

                $track = array_reverse($track);
                //BaseController::debug($track, true);

                $info = $track[0];
                //BaseController::debug($info);
        
                $records = TrackingHistory::where('rastreio_historico.rastreio_id', '=', $id)
                ->orderBy('rastreio_historico.created_at', 'DESC')
                ->orderBy('rastreio_historico.id', 'DESC')
                ->first();
                //BaseController::debug($records, true);
        
                if(count($records) > 0){
                    BaseController::debug($info['hash']);
                    BaseController::debug($records->hash);
        
                    if($records->hash != $info['hash']){
        
                        BaseController::debug($info);

                        switch (utf8_encode($info['acao'])) {
                            case 'encaminhado':
                    case 'objeto encaminhado':
                        $icone = 2;
                        break;
                    case 'objeto postado':
                        $icone = 5;
                        break;
                    case 'a entrega nÃ£o pode ser efetuada - carteiro nÃ£o atendido':
                    case 'a entrega não pode ser efetuada - carteiro não atendido':
                    case 'tentativa de entrega nÃ£o efetuada':
                    case 'tentativa de entrega não efetuada':
                        $icone = 6;
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
                        $icone = 10;
                        break;
                    default:
                        $icone = 0;
                        break;
                        }
        
        
                        $rastreio = new TrackingHistory();
                        $rastreio->rastreio_id = $id;
                        $rastreio->data        = $info['data'];
                        $rastreio->data_sql    = $info['data_sql'];
                        $rastreio->local       = utf8_encode($info['local']);
                        $rastreio->acao        = utf8_encode(trim($info['acao']));
                        $rastreio->detalhes    = utf8_encode($info['detalhes']);
                        $rastreio->hash        = $info['hash'];
                        $rastreio->save();
        
                        Tracking::where('numero', '=', $id)->update(array(
                                'status'      => utf8_encode(trim($info['acao'])),
                                'data_status' => $info['data_sql'],
                                'novo_status' => 1,
                                'icone'       => $icone
                            )
                        );
                        
                        if(trim($info['acao']) == 'entregue' || trim($info['acao']) == 'entrega efetuada' || trim($info['acao']) == 'objeto entregue ao destinatÃ¡rio' || trim($info['acao']) == 'objeto entregue ao destinatário'){
                            Tracking::where('numero', '=', $id)->update(array('atualiza_status' => 0));
                        }
        
                        /////ENVIO DO EMAIL //////////////////
                        $dados = Tracking::where('numero', '=', $id)->first();
                        $user  = User::where('id', '=', $dados->usuario_id)->first();
        
                        // the data that will be passed into the mail view blade template
                        $data = array(
                                'cod_rastreio' => $id,
                                'description'  => $dados->descricao,
                                'detail'	   => $info['acao'] . ' - ' . $info['detalhes']
                        );
        
                        $rcpto = explode(';', $user->email);
                        if(! is_array($rcpto))
                            $rcpto = array($rcpto);

                        $retorno = Mail::send('emails.rastreio', $data, function($message) use ($user, $rcpto){
                            $message->from('rastreio@cooltracker.com.br', 'Rastreio - Cooltracker');
                            $message->to($rcpto)->subject('Novidades sobre sua encomenda!! ' . date('d/m/Y H:i:s'));
                        });

                        BaseController::debug('Atualizou');
                        BaseController::debug('============================================');

                        return true;
        
                    } else {
                        
                        BaseController::debug('Nenhuma novidade. Nao foi atualizado');
                        BaseController::debug('============================================');

                        return true;

                    }
        
                }else{
        
                    $track = array_reverse($track);
        
                    foreach($track as $info){
                        
                        BaseController::debug($info);

                        switch (utf8_encode($info['acao'])) {
                            case 'encaminhado':
                    case 'objeto encaminhado':
                        $icone = 2;
                        break;
                    case 'objeto postado':
                        $icone = 5;
                        break;
                    case 'a entrega nÃ£o pode ser efetuada - carteiro nÃ£o atendido':
                    case 'a entrega não pode ser efetuada - carteiro não atendido':
                    case 'tentativa de entrega nÃ£o efetuada':
                    case 'tentativa de entrega não efetuada':
                        $icone = 6;
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
                        $icone = 10;
                        break;
                    default:
                        $icone = 0;
                        break;
                        }
        
                        $rastreio = new TrackingHistory();
                        $rastreio->rastreio_id = $id;
                        $rastreio->data        = $info['data'];
                        $rastreio->data_sql    = $info['data_sql'];
                        $rastreio->local       = $info['local'];
                        $rastreio->acao        = trim($info['acao']);
                        $rastreio->detalhes    = $info['detalhes'];
                        $rastreio->hash        = $info['hash'];
                        $rastreio->save();
                            
                        Tracking::where('numero', '=', $id)->update(array(
                                'status'      => utf8_encode($info['acao']),
                                'data_status' => $info['data_sql'],
                                'novo_status' => 1,
                                'icone'       => $icone
                            )
                        );
        
                        if(trim($info['acao']) == 'entregue' || trim($info['acao']) == 'entrega efetuada' || trim($info['acao']) == 'objeto entregue ao destinatÃ¡rio' || trim($info['acao']) == 'objeto entregue ao destinatário'){
                            Tracking::where('numero', '=', $id)->update(array('atualiza_status' => 0));
                        }
        
                        $detalhe = trim($info['acao']) . ' - ' . $info['detalhes'];
                    }
        
                    /////ENVIO DO EMAIL //////////////////
                    $dados = Tracking::where('numero', '=', $id)->first();
                    $user  = User::where('id', '=', $dados->usuario_id)->first();
        
                    // the data that will be passed into the mail view blade template
                    $data = array(
                        'cod_rastreio' => $id,
                        'description'  => $dados->descricao,
                        'detail'	   => $info['acao'] . ' - ' . $info['detalhes']
                    );

                    $rcpto = explode(';', $user->email);
                    if(! is_array($rcpto))
                        $rcpto = array($rcpto);

                    Mail::send('emails.rastreio', $data, function($message) use ($user, $rcpto){
                        $message->from('rastreio@cooltracker.com.br', 'Rastreio - Cooltracker');
                        $message->to($rcpto)->subject('Novidades sobre sua encomenda!! ' . date('d/m/Y H:i:s'));
                    });

                    BaseController::debug('Atualizou');
                    BaseController::debug('============================================');
        
                    return true;
                }

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




    ///////////////////////////////////////////////////
    ///WEBSRO /////////////////////////////////////////

    public static function updateWH($id){
        BaseController::debug($id);

        $url = 'http://www.websro.com.br/detalhes.php?P_COD_UNI=';
        $html = file_get_contents($url . $id);
        $msgEntrega = [
            'entregue',
            'entrega efetuada',
            'objeto entregue ao destinatário'
        ];

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

                if(count($mat) < 3) {
                    unset($mat[count($mat) - 1]);
                }else{
                    unset($mat[0]);
                    unset($mat[count($mat)]);
                }

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
                        'hash'     => hash('md5', $date)
                    ];
                    $track[] = $tmp;

                    //BaseController::debug($track);
                }

                $track = array_reverse($track);
                $info  = $track[0];
                //BaseController::debug($info, true);

                $records = TrackingHistory::where('rastreio_historico.rastreio_id', '=', $id)
                ->orderBy('rastreio_historico.created_at', 'DESC')
                ->orderBy('rastreio_historico.id', 'DESC')
                ->first();

                if(count($records) < 1){

                    $track = array_reverse($track);

                    foreach($track as $info){
                        BaseController::debug($info);

                        //GRAVA o REGISTRO
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
                    }
                    /////ENVIO DO EMAIL //////////////////

                }else{

                    //COMPARA OS HASHES
                    BaseController::debug("NEW HASH: " . $info['hash']);
                    BaseController::debug("OLD HASH: " . $records->hash);

                    if($records->hash == $info['hash']){

                        BaseController::debug('Nenhuma novidade. Nao foi atualizado');
                        BaseController::debug('============================================');
                        return true;

                    }else{

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

                    }
                }

                //////////////////////////////////////
                /////ENVIO DO EMAIL //////////////////
                //////////////////////////////////////
                $dados = Tracking::where('numero', '=', $id)->first();
                $user  = User::where('id', '=', $dados->usuario_id)->first();

                // the data that will be passed into the mail view blade template
                $data = array(
                        'cod_rastreio' => $id,
                        'description'  => $dados->descricao,
                        'detail'	   => $info['acao'] . ((strlen($info['detalhes']) > 0) ? ' - ' . $info['detalhes'] : '')
                );

                $rcpto = explode(';', $user->email);
                if(! is_array($rcpto))
                    $rcpto = array($rcpto);

                Mail::send('emails.rastreio', $data, function($message) use ($user, $rcpto){
                    $message->from('rastreio@cooltracker.com.br', 'Rastreio - Cooltracker');
                    $message->to($rcpto)->subject('Novidades sobre sua encomenda!! ' . date('d/m/Y H:i:s'));
                });

                //ENVIO DA NOTIFICACAO///////////////
                BaseController::debug('Notificacao');
                if($user->id <= 2){
                    $fcmTopic = $user->id;
                    $titulo = 'Novidades sobre sua encomenda!!!';
                    $mensagem = "A sua encomenda {$id} recebeu uma atualização de status. Clique aqui para ver!";

                    echo '<pre>';
                    CorreiosController::sendNotification($titulo, $mensagem, $fcmTopic);
                    echo '</pre>';
                }

                BaseController::debug('Atualizou');
                BaseController::debug('============================================');

                return true;
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
    

    /////////////////////////////////////////////////////////////////////
    ///// ALTERNATIVO ///////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////
    //$url = 'https://www.linkcorreios.com.br/?id=';
    public static function updateWH_BKP($id){

        //$id = 'BE049177405BR';
        BaseController::debug($id);

		//$url = 'http://websro.correios.com.br/sro_bin/txect01$.QueryList?P_LINGUA=001&P_TIPO=001&P_COD_UNI=';
        //$url = 'http://www.websro.com.br/detalhes.php?P_COD_UNI=';
        $url = 'https://www.linkcorreios.com.br/?id=';
        $html = file_get_contents($url . $id);

        // Verifica se o objeto ainda não foi postado
        if(strstr($html, '<table class="table table-bordered">') !== false){

            // Limpa o codigo html
            $html = preg_replace("@\r|\t|\n| +@", ' ', $html);
            $html = str_replace('</tr>', "</tr>\n", $html);

            // Pega as linhas com o rastreamento
            if (preg_match_all('@<tr>(.*)</tr>@', $html, $mat, PREG_SET_ORDER)){

                $track = array();
                $mat = array_reverse($mat);
                unset($mat[0]);
                unset($mat[count($mat)]);
                //BaseController::debug($mat, true);

                $temp = null;

                // Formata as linhas e gera um vetor
                foreach($mat as $item){
                    $item[0] = preg_replace("@\r|\t|\n| +@", ' ', $item[0]);

                    if(preg_match("@<td rowspan=\"2\">(.*)</td>@", $item[0], $d)){

                        // Cria uma linha de track
                        $arrDate = explode(' ', $d[1]);
                        $date = $arrDate[0] . ' ' . strip_tags($arrDate[1]) . '  ';

                        $arrData = preg_split('@([0-9]{2})/([0-9]{2})/([0-9]{4}) ([0-9]{2}):([0-9]{2})@', $d[1]);
                        //BaseController::debug($arrData);

                        $tmp = array(
                            'data'     => $date,
                            'data_sql' => preg_replace('@([0-9]{2})/([0-9]{2})/([0-9]{4}) ([0-9]{2}):([0-9]{2})@', '$3-$2-$1 $4:$5:00', $date),
                            'local'    => '',
                            'acao'     => strip_tags($arrData[1]),
                            'detalhes' => '',
                            'hash'     => hash('md5', $date)
                        );

                        // Se tiver um encaminhamento armazenado
                        if (isset($tempDestino)){
                            $tmp['detalhes'] = strip_tags($tempDestino);
                            $tempDestino    = null;
                        }

                        if (isset($tempOrigem)){
                            $tmp['local'] = strip_tags($tempOrigem);
                            $tempOrigem    = null;
                        }

                        // Adiciona o item na lista de rastreamento
                        $track[] = $tmp;

                    }elseif(preg_match("@<td colspan=\"2\">(.*)</td>@", $item[0], $d)){

                        $tempOrigem = trim(str_replace('Local:', '', $d[1]));
                        //BaseController::debug($tempOrigem, true);

                    }elseif(preg_match("@<td>(.*)</td>@", $item[0], $d)){

                        $dados = explode('</td> <td>', $d[1]);

                        if(count($dados) > 1) {
                            $tempOrigem  = trim(str_replace('Local:', '', $dados[0]));
                            $tempDestino = trim(str_replace('Origem:', '', $dados[1]));

                            //BaseController::debug($tempOrigem);
                        } else {
                            $tempDestino = trim(str_replace('Origem:', '', $dados[0]));
                        }

                        //BaseController::debug($tempDestino, true);

                    }

                }

                $track = array_reverse($track);
                //BaseController::debug($track);

                $info = $track[0];
                //BaseController::debug($info);
        
                $records = TrackingHistory::where('rastreio_historico.rastreio_id', '=', $id)
                ->orderBy('rastreio_historico.created_at', 'DESC')
                ->orderBy('rastreio_historico.id', 'DESC')
                ->first();
                //BaseController::debug($records, true);
        
                if(count($records) > 0){
                    BaseController::debug("NEW HASH: " . $info['hash']);
                    BaseController::debug("OLD HASH: " . $records->hash);
        
                    if($records->hash != $info['hash']){
                        BaseController::debug($info);

                        //ICONE
                        $icone = CorreiosController::setIcon(mb_strtolower(trim($info['acao'])));

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

                        $msgEntrega = [
                            'entregue',
                            'entrega efetuada',
                            'objeto entregue ao destinatário'
                        ];

                        if(in_array(mb_strtolower(trim($info['acao'])), $msgEntrega) !== false){
                            BaseController::debug('ENTREGUE');
                            Tracking::where('numero', '=', $id)->update(array('atualiza_status' => 0));
                        }

                        /////ENVIO DO EMAIL //////////////////
                        $dados = Tracking::where('numero', '=', $id)->first();
                        $user  = User::where('id', '=', $dados->usuario_id)->first();
        
                        // the data that will be passed into the mail view blade template
                        $data = array(
                                'cod_rastreio' => $id,
                                'description'  => $dados->descricao,
                                'detail'	   => $info['acao'] . ((strlen($info['detalhes']) > 0) ? ' - ' . $info['detalhes'] : '')
                        );

                        $rcpto = explode(';', $user->email);
                        if(! is_array($rcpto))
                            $rcpto = array($rcpto);

                        Mail::send('emails.rastreio', $data, function($message) use ($user, $rcpto){
                            $message->from('rastreio@cooltracker.com.br', 'Rastreio - Cooltracker');
                            $message->to($rcpto)->subject('Novidades sobre sua encomenda!! ' . date('d/m/Y H:i:s'));
                        });

                        //ENVIO DA NOTIFICACAO///////////////
                        BaseController::debug('Notificacao A');
                        //BaseController::debug($user->id);
                        if($user->id <= 2){
                            $fcmID = $user->fcm_token;
                            $titulo = 'Novidades sobre sua encomenda!!!';
                            $mensagem = "A sua encomenda {$id} recebeu uma atualização de status. Clique aqui para ver!";

                            //BaseController::debug($fcmID);
                            //BaseController::debug($titulo);
                            //BaseController::debug($mensagem);

                            CorreiosController::sendNotification($titulo, $mensagem, $user->id);
                        }

                        BaseController::debug('Atualizou');
                        BaseController::debug('============================================');

                        return true;
        
                    } else {
                        
                        BaseController::debug('Nenhuma novidade. Nao foi atualizado');
                        BaseController::debug('============================================');

                        return true;

                    }
        
                }else{
        
                    $track = array_reverse($track);
        
                    foreach($track as $info){
                        BaseController::debug($info);

                        //ICONE
                        $icone = CorreiosController::setIcon(mb_strtolower(trim($info['acao'])));
        
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

                        $msgEntrega = [
                            'entregue',
                            'entrega efetuada',
                            'objeto entregue ao destinatário'
                        ];

                        if(in_array(mb_strtolower(trim($info['acao'])), $msgEntrega) !== false){
                            BaseController::debug('ENTREGUE');
                            Tracking::where('numero', '=', $id)->update(array('atualiza_status' => 0));
                        }

                    }


                    /////ENVIO DO EMAIL //////////////////
                    $dados = Tracking::where('numero', '=', $id)->first();
                    $user  = User::where('id', '=', $dados->usuario_id)->first();
    
                    // the data that will be passed into the mail view blade template
                    $data = array(
                            'cod_rastreio' => $id,
                            'description'  => $dados->descricao,
                            'detail'	   => $info['acao'] . ((strlen($info['detalhes']) > 0) ? ' - ' . $info['detalhes'] : '')
                    );

                    $rcpto = explode(';', $user->email);
                    if(! is_array($rcpto))
                        $rcpto = array($rcpto);

                    Mail::send('emails.rastreio', $data, function($message) use ($user, $rcpto){
                        $message->from('rastreio@cooltracker.com.br', 'Rastreio - Cooltracker');
                        $message->to($rcpto)->subject('Novidades sobre sua encomenda!! ' . date('d/m/Y H:i:s'));
                    });

                    //ENVIO DA NOTIFICACAO///////////////
                    BaseController::debug('Notificacao B');
                    //BaseController::debug($user->id);
                    if($user->id <= 2){
                        $fcmID = $user->fcm_token;
                        $titulo = 'Novidades sobre sua encomenda!!!';
                        $mensagem = "A sua encomenda {$id} recebeu uma atualização de status. Clique aqui para ver!";

                        //BaseController::debug($fcmID);
                        //BaseController::debug($titulo);
                        //BaseController::debug($mensagem);

                        CorreiosController::sendNotification($titulo, $mensagem, $user->id);
                    }

                    BaseController::debug('Atualizou');
                    BaseController::debug('============================================');
        
                    return true;
                }

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
    /////////////////////////////////////////////////////////////////////
    /////////////////////////////////////////////////////////////////////


    public static function updateWS($objeto){

        BaseController::debug($objeto);

        //WEB SERVICE///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $client = new SoapClient("http://webservice.correios.com.br/service/rastro/Rastro.wsdl");

        $params = [
            'usuario'   => 'ECT', //ECT
            'senha'     => 'SRO', //SRO
            'tipo'      => 'L',
            'resultado' => 'U', //T ou U
            'lingua'    => '101',
            'objetos'   => $objeto
        ];

        //$response = $client->__soapCall("BuscaEventos", [$params]);
        $response = $client->__soapCall("BuscaEventos", [$params]);

        BaseController::debug($response);

        //ORIGEM///////////////////////////////
        $dadosOrigem = isset($response->return->objeto->evento) ? $response->return->objeto->evento : null;
        BaseController::debug($dadosOrigem); die;

        if($dadosOrigem) {

            $tmpLocal  = isset($dadosOrigem->local) ? "Origem: {$dadosOrigem->local}" : '';
            $tmpCidade = isset($dadosOrigem->cidade) ? "{$dadosOrigem->cidade}/" : '';
            $tmpUF     = isset($dadosOrigem->uf) ? $dadosOrigem->uf : '';
            $tmpDate   = isset($dadosOrigem->data) ? preg_split('[/]', $dadosOrigem->data) : '';
            $tmpHora   = isset($dadosOrigem->hora) ? $dadosOrigem->hora . ':00' : '';

            //CRIA O OBJETO PARA GRAVAÇÃO
            $origem = new StdClass;
            $origem->local   = "{$tmpLocal} - {$tmpCidade}{$tmpUF}";
            $origem->data    = "{$tmpDate[0]}/{$tmpDate[1]}/{$tmpDate[2]} {$tmpHora}";
            $origem->dataSQL = "{$tmpDate[2]}/{$tmpDate[1]}/{$tmpDate[0]} {$tmpHora}";
            $origem->acao    = isset($dadosOrigem->descricao) ? mb_strtolower($dadosOrigem->descricao) : '';
            $origem->hash    = hash('md5', $origem->dataSQL);
            //BaseController::debug($origem);


            //DESTINO//////////////////////////////
            $dadosDestino = isset($response->return->objeto->evento->destino) ? $response->return->objeto->evento->destino : null;

            $tmpLocal  = isset($dadosDestino->local) ? "Destino: {$dadosDestino->local}" : '';
            $tmpCidade = isset($dadosDestino->cidade) ? "{$dadosDestino->cidade}/" : '';
            $tmpUF     = isset($dadosDestino->uf) ? $dadosDestino->uf : '';

            $destino = new StdClass;
            $destino->detalhes = "{$tmpLocal} - {$tmpCidade}{$tmpUF}";
            //BaseController::debug($destino);

            ///////////////////////////////////////////////////////////////////////////////////////////////////////////
            //PREPARA PARA GRAVAR
            $records = TrackingHistory::where('rastreio_historico.rastreio_id', '=', $objeto)
            ->orderBy('rastreio_historico.created_at', 'DESC')
            ->orderBy('rastreio_historico.id', 'DESC')
            ->first();

            if(!$records){

                $records = new StdClass;
                $records->hash = '';

            }

            BaseController::debug($origem->hash);
            BaseController::debug($records->hash);

            if($records->hash != $origem->hash){

                switch (mb_strtolower(trim($origem->acao))) {
                    case 'encaminhado':
                    case 'objeto encaminhado':
                        $icone = 2;
                        break;
                    case 'objeto postado':
                        $icone = 5;
                        break;
                    case 'a entrega nÃ£o pode ser efetuada - carteiro nÃ£o atendido':
                    case 'a entrega não pode ser efetuada - carteiro não atendido':
                    case 'tentativa de entrega nÃ£o efetuada':
                    case 'tentativa de entrega não efetuada':
                        $icone = 6;
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
                    case 'bjeto aguardando retirada no endereço indicado':
                        $icone = 10;
                        break;
                    case 'objeto saiu para entrega ao remetente':
                    case 'objeto saiu para entrega ao remetente':
                            $icone = 11;
                            break;
                    case 'objeto devolvido ao remetente':
                        $icone = 11;
                        break;
                    default:
                        $icone = 0;
                        break;
                }

                $rastreio = new TrackingHistory();
                $rastreio->rastreio_id = $objeto;
                $rastreio->data        = $origem->data;
                $rastreio->data_sql    = $origem->dataSQL;
                $rastreio->local       = trim($origem->local);
                $rastreio->acao        = trim($origem->acao);
                $rastreio->detalhes    = isset($destino->detalhes) ? trim($destino->detalhes) : '';
                $rastreio->hash        = $origem->hash;
                $rastreio->save();
                //BaseController::debug($rastreio);

                Tracking::where('numero', '=', $objeto)->update(array(
                        'status'      => trim($origem->acao),
                        'data_status' => $origem->dataSQL,
                        'novo_status' => 1,
                        'icone'       => $icone
                    )
                );

                $msgEntrega = [
                    'entregue',
                    'entrega efetuada',
                    'objeto entregue ao destinatário'
                ];

                if(in_array(mb_strtolower($origem->acao), $msgEntrega) !== false){

                    Tracking::where('numero', '=', $objeto)->update(array('atualiza_status' => 0));

                }

                /////ENVIO DO EMAIL //////////////////
                $dados = Tracking::where('numero', '=', $objeto)->first();
                $user  = User::where('id', '=', $dados->usuario_id)->first();

                // the data that will be passed into the mail view blade template
                $data = array(
                    'cod_rastreio' => $objeto,
                    'description'  => $dados->descricao,
                    'detail'	   => $origem->acao . ((strlen($destino->detalhes)) ? ' - ' . $destino->detalhes : '')
                );

                $rcpto = explode(';', $user->email);
                if(! is_array($rcpto))
                    $rcpto = array($rcpto);

                $retorno = Mail::send('emails.rastreio', $data, function($message) use ($user, $rcpto){
                    $message->from('rastreio@cooltracker.com.br', 'Rastreio - Cooltracker');
                    $message->to($rcpto)->subject('Novidades sobre sua encomenda!! ' . date('d/m/Y H:i:s'));
                });

                BaseController::debug('Atualizou');
                BaseController::debug('============================================');

                return true;

            } else {

                BaseController::debug('Nenhuma novidade. Nao foi atualizado. Cod 2');
                BaseController::debug('============================================');

                return true;

            }

        } else {

            BaseController::debug('Nenhuma novidade. Nao foi atualizado. Cod 1');
            BaseController::debug('============================================');

        }
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    }
}

