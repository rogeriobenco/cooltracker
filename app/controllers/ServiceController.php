<?php

/**
 * Classe que implementa os métodos para acesso aos rastreios cadastrados
 * @author Rogerio Benco
 */
class ServiceController extends BaseController {

    /**
     * Valida as credenciais do usuário
     * @author Rogerio Benco
     * @param usuario E-mail ou username
     * @param senha Senha
     * @param token Chave de validação
     * @return JSON
     */
    public function getCredentials($usuario, $senha, $token) {

        //URL TESTE
        //http://cooltracker.com.br/service/credentials/dGVzdGVAdGVzdGUuY29tLmJy/MTIz/w9e78rds64f56

        if($token == 'c2b4e29b756130d8fa341194c4dff2be') {

            $usuarioDecoded = base64_decode($usuario);
            $senhaDecoded   = base64_decode($senha);

            //Prepara para o login
            $type = (filter_var($usuarioDecoded, FILTER_VALIDATE_EMAIL)) ? 'email' : 'login';

            $credentials = [
                $type      => $usuarioDecoded,
                'password' => $senhaDecoded
            ];

            if(Auth::attempt($credentials)){

                if(Auth::user()->deleted_at){
                    $arrAnswer = [
                        'success' => false,
                        'id'      => Auth::user()->id,
                        'nome'    => Auth::user()->nome,
                        'login'   => Auth::user()->login,
                        'pass'    => 'private',
                        'email'   => Auth::user()->email,
                        'msg'     => 'Usuário desabilitado'
                    ];
                } else {
                    $arrAnswer = [
                        'success' => true,
                        'id'      => Auth::user()->id,
                        'nome'    => Auth::user()->nome,
                        'login'   => Auth::user()->login,
                        'pass'    => 'private',
                        'email'   => Auth::user()->email,
                        'msg'     => 'Usuário desabilitado'
                    ];
                }

            }else{
                $arrAnswer = [
                    'success' => false,
                    'id'      => null,
                    'nome'    => null,
                    'login'   => null,
                    'pass'    => null,
                    'email'   => null,
                    'msg'     => 'Usuário ou senha inválidos'
                ];
            }

        }else{

            $arrAnswer = [
                'success' => false,
                'id'      => null,
                'nome'    => null,
                'login'   => null,
                'pass'    => null,
                'email'   => null,
                'msg'     => 'Acesso negado - INVALID_TOKEN'
            ];

        }

        echo json_encode($arrAnswer);
    }

    /**
     * Registra um novo usuário no app
     * @author Rogerio Benco
     * @param name Nome do usuario
     * @param user Email [ou nome de usuario (deprecated)] do usuário
     * @param pass Senha do usuario
     * @param token Token de validacao
     * @return JSON
     */
    public function getRegister($name, $user, $pass, $token){

        //URL TESTE
        //http://cooltracker.com.br/service/register/Um9nZXJpbw==/dGVzdGVAdGVzdGUuY29tLmJy/MTIz/r5ek8st48eyr87dso47w

        if($token == '0e434b2a2736e8c16ced080d37e65808'){

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
                    $arrMsg = $error[0];
                }

                $arrAnswer = [
                    'success' => false,
                    'msg' => $arrMsg
                ];

            } else {
            
                $mUser = new User();
                $mUser->nome = $name;
                $mUser->login = $user;
                $mUser->senha = $pass;
                $mUser->email = $user;

                if($mUser->save()){

                    $arrAnswer = [
                        'success' => true,
                        'msg'     => 'Usuário cadastrado. Faça o login para continuar!'
                    ];

                }else{

                    $arrAnswer = [
                        'success' => false,
                        'msg'     => 'Erro ao cadastrar o usuário - Contate suporte@cooltracker.com.br'
                    ];

                }

            }

        } else {

            $arrAnswer = [
                'success' => false,
                'msg' => 'Acesso negado - INVALID_TOKEN'
            ];  

        }

        echo json_encode($arrAnswer);

    }

    /**
     * Envia uma email para o usuário com um código de recuperação da senha
     * @author Rogerio Benco
     * @param email Email do usuário
     * @param token Tode de validação
     * @return JSON
     */
    public function getForgotpass($email, $token){
        
        //URL TESTE
        //http://cooltracker.com.br/service/forgotpass/dGVzdGVAdGVzdGUuY29tLmJy/258db880116e890d7bba2e11b6e20a92

        if($token == '258db880116e890d7bba2e11b6e20a92'){
            $email  = base64_decode($email);
            $remember_token = strtoupper(substr(md5('cooltracker_'.date('d-m-Y h:i:s')), 10, 5));

            $user = User::where('email', '=', $email)->count();

            if($user) {
                $update = User::where('email', '=', $email)->update(['remember_token' => $remember_token]);

                $data = array(
                    'remember_token' => $remember_token
                );

                Mail::send('emails.forgot', $data, function($message) use ($user, $email){
                    $message->from('rastreio@cooltracker.com.br', 'Rastreio - Cooltracker');
                    $message->to($email)->subject('Cooltracker - Lembrete de senha ' . date('d/m/Y H:i:s'));
                });

                $arrAnswer = [
                    'success' => true,
                    'msg'     => 'Email enviado com sucesso!!'
                ];

            } else {
                $arrAnswer = [
                    'success' => false,
                    'msg'     => 'Usuário não encontrado - contate o suporte@cooltracker.com.br'
                ];
            }

        } else {
            $arrAnswer = [
                'success' => false,
                'msg'     => 'Acesso Negado - INVALID_TOKEN'
            ];
        }

        echo json_encode($arrAnswer);
    }

    /**
     * Altera a senha do usuário
     * @author Rogerio Benco
     * @param code Código de recuperação
     * @param pass Nova senha
     * @param token Token de validação
     * @return JSON
     */
    public function getResetpass($code, $pass, $token){
        
        //URL TESTE
        //http://cooltracker.com.br/service/resetpass/bWlsZW5h/MTIz/b7bf6e18053dee2bb192009e756eb21b

        if($token == 'b7bf6e18053dee2bb192009e756eb21b'){
            $remember_token  = strtoupper(base64_decode($code));
            $pass            = Hash::make(base64_decode($pass));
            $expires         = strtoupper(substr(md5('cooltracker_'.date('d-m-Y h:i:s')), 10, 5));

            $user = User::where('remember_token', '=', $remember_token)->count();

            if($user) {
                $update = User::where('remember_token', '=', $remember_token)->update([
                    'senha'          => $pass,
                    'remember_token' => $expires
                ]);

                $arrAnswer = [
                    'success' => true,
                    'msg'     => 'Senha alterada com sucesso!!'
                ];

            } else {
                $arrAnswer = [
                    'success' => false,
                    'msg'     => 'Código de recuperação inválido!'
                ];
            }

        } else {
            $arrAnswer = [
                'success' => false,
                'msg'     => 'Acesso negado - INVALID_TOKEN'
            ];
        }

        echo json_encode($arrAnswer);
    }

    /**
     * Recupera a lista de rastreios cadastrados
     * @param user ID do usuário a ser pesquisado
     * @param type Arquivados [Sim = 1 | Não = 0]
     * @param token Chave de validação
     * @return JSON
     */
    public function getTrackinglist($user, $type, $token){

        if($token == '0d2ed2c3cee16fa27fade74941fb1270'){

            $records = Tracking::where('usuario_id', '=', $user)
            ->where('ativo', '=', 1)
            ->where('arquivado', '=', $type)
            //->orderBy('novo_status', 'DESC')
            ->orderBy('atualiza_status', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

            $arrAnswer = [];

            if($records) {

                foreach($records as $row) {

                    $flag = strtolower(substr($row->numero, -2));
                    $arrAnswer[] = [
                        'id'          => $row->id,
                        'tracking'    => $row->numero,
                        'description' => $row->descricao,
                        'date'        => $row->data_status,
                        'action'      => $row->status,
                        'novo_status' => $row->novo_status,
                        'smile'       => "$row->icone",
                        'flag'        => $flag
                    ];

                }

            } else {
                $arrAnswer[] = [
                    'id'          => null,
                    'tracking'    => null,
                    'description' => null,
                    'date'        => null,
                    'action'      => null,
                    'novo_status' => null,
                    'smile'       => null,
                    'flag'        => null
                ];
            }

        }

        echo json_encode($arrAnswer);
    }

    /**
     * Obtém os detalhes dos rastreios cadastrados
     * @author Rogerio Benco
     * @param trackingID ID do código de rastreio
     * @param token Token de validacao
     * @return JSON
     */
    //Obtem os detalhes do rastreio
    public function getTrackingdetailslist($trackingID, $token) {

        if($token == '258db880116e890d7bba2e11b6e20a92'){
        
            $records = TrackingHistory::where('rastreio_id', '=', $trackingID)
            ->orderBy('data_sql', 'DESC')
            ->orderBy('acao', 'ASC')
            ->get();

            if(count($records)) {
                foreach($records as $row){

                    $arrAnswer[] = [
                        'success'  => true,
                        'tracking' => $row->rastreio_id,
                        'date'     => $row->data,
                        'local'    => $row->local,
                        'action'   => $row->acao,
                        'details'  => $row->detalhes
                    ];

                }
            } else {
                $arrAnswer[] = [
                    'success'  => false,
                    'tracking' => $trackingID,
                    'date'     => '',
                    'local'    => '',
                    'action'   => '',
                    'details'  => 'Sem informações de rastreio. Aguarde atualização dos Correios'
                ];
            }

        }

        echo json_encode($arrAnswer);

    }

    /**
     * Marca os objetos como "lidos"
     * @author Rogerio Benco
     * @param tracking Código de rastreio
     * @param token Token de validação
     * @return JSON
     */
    public function getTrackingread($tracking, $token){

        if($token == 'd2f4a53b77164d602c61db55287e0bbd') {

            $update = Tracking::where('numero', '=', $tracking)->update(array('novo_status' => 0));

            $arrAnswer[] = [
                'success'     => true,
                'description' => "Marcada como lida"
            ];

        } else {

            $arrAnswer[] = [
                'success'     => false,
                'description' => "Acesso Negado - INVALID_TOKEN"
            ];

        }

        echo json_encode($arrAnswer);

    }

    /**
     * Registra um novo código de rastreio
     * @author Rogerio Benco
     * @param user Usuário para o qual o código de rastreio será cadastrado
     * @param trackgin Código de rastreio
     * @param descricao Descrição da encomenda cadastrada
     * @param token Token de validacao
     * @return JSON
     */
    public function getRegistertracking($user, $tracking, $descricao, $token){

        if($token == '66032145cc47187e63fef6cc4392fa38'){

            $tracking  = strtoupper(base64_decode($tracking));
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
                    $arrMsg = str_replace('tracking', 'Código de Rastreio', $error[0]);
                }

                $arrAnswer[] = [
                    'success'     => false,
                    'description' => $arrMsg
                ];

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

                    $arrAnswer[] = [
                        'success'     => true,
                        'description' => 'Encomenda cadastrada!'
                    ];

                }else{

                    $arrAnswer[] = [
                        'success'     => false, 
                        'description' => 'Erro ao cadastrar a encomenda!'
                    ];

                }

            }

        } else {

            $arrAnswer[] = [
                'success'     => false, 
                'description' => 'Acesso Negado - INVALID_TOKEN'
            ];

        }

        echo json_encode($arrAnswer);

    }

}