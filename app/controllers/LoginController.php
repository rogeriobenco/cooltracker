<?php

class LoginController extends BaseController{
    
    public function getIndex(){
        /*User::create([
            'username' => 'rogeriobenco',
            'password' => Hash::make(Input::get('Guitar00')),
        ]);*/
        
        /*$registro = new User();
        $registro->razao = 'Teste';
        $registro->username = 'roger';
        $registro->password = Hash::make('Guitar00');
        $registro->save();
        

        $user = User::where('username', '=', 'roger')->first();
        Auth::login($user);*/

        //echo Hash::make('pedro347');
        //exit();
        
        return View::make('login.index');
    }

    public function getHash(){
        return View::make('login.hash');
    }

    public function postHash(){

        echo Hash::make(Input::get('senha'));
        echo '<br>';
        echo '$2y$10$I8ET1XHtH.01WGvFQk962uFvgp.C.C1PxUYJxZ8ZaBeJMPc.cdAh.';
        exit();

    }

    public function getSenha(){
        echo Hash::make(Input::get('secam2016')).'<br>';
        echo Hash::make(Input::get('Guitar00'));
    }
    
    public function postIndex(){

        $rules = array(
            'usuario' => 'required',
            'senha' => 'required'
        );
        
        $messages = [
            'required' => utf8_decode(utf8_encode('O campo :attribute é obrigatório.'))
        ];
        
        $validator = Validator::make(Input::all(), $rules, $messages);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }

        //Prepara para o login
        $type = (filter_var(Input::get('usuario'), FILTER_VALIDATE_EMAIL)) ? 'email' : 'login';

        $credentials = [
            $type      => Input::get('usuario'),
            'password' => Input::get('senha')
        ];

        if(Auth::attempt($credentials)){
            if(Auth::user()->deleted_at){
                return Redirect::to('login')->with('flash_error', 'Usuário desabilitado.')->withInput();
            }
            
            return Redirect::to('/');
        }else{
            return Redirect::to('login')->with('message', 'Nome de usuário ou senha inválidos.')->withInput();
        }
    }
    
    public function postFacebook(){
        
        $records = User::where('email', '=', Input::get('email'))->where('facebook', '=', 'F')->first();
        
        if(count($records) > 0){
            if(Auth::loginUsingId($records->id)){
                if(Auth::user()->deleted_at){
                    return json_encode(array('success' => false, 'errno' => 2, 'msg' => 'Usuario desabilitado'));
                }

                return json_encode(array('success' => true, 'errno' => 0, 'msg' => 'Usuário logado'));
            }else{
                return json_encode(array('success' => false,'errno' => 1, 'msg' => 'Erro ao logar o usuário'));
            }
        }else{
            $records = User::where('email', '=', Input::get('email'))->first();
            
            if(count($records) > 0){
                $user = User::find($records->id);
                $user->facebook = 'F';
                $user->save();
                
                if(Auth::loginUsingId($records->id)){
                    if(Auth::user()->deleted_at){
                        return json_encode(array('success' => false, 'errno' => 2, 'msg' => 'Usuario desabilitado'));
                    }

                    return json_encode(array('success' => true, 'errno' => 0, 'msg' => 'Usuário logado'));
                }else{
                    return json_encode(array('success' => false,'errno' => 1, 'msg' => 'Erro ao logar o usuário'));
                }
            }else{
                $user = new User();
                $user->login = '';
                $user->nome = Input::get('name');
                $user->email = Input::get('email');
                $user->facebook = 'F';
                $user->save();
                
                if(Auth::loginUsingId($user->id)){
                    if(Auth::user()->deleted_at){
                        return json_encode(array('success' => false, 'errno' => 2, 'msg' => 'Usuario desabilitado'));
                    }

                    return json_encode(array('success' => true, 'errno' => 0, 'msg' => 'Usuário logado'));
                }else{
                    return json_encode(array('success' => false,'errno' => 1, 'msg' => 'Erro ao logar o usuário'));
                }
            }
        }
    }
    
    public function Sair(){
        Auth::logout();
        return Redirect::to('/');
    }
    
}
