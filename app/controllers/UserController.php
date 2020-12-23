<?php

class UserController extends BaseController{
    public function getIndex(){
        return View::make('usuarios.adicionar');
    }

    public function getListar(){
        $usuarios = User::all();
        return View::make('usuarios.listar', array('usuarios' => $usuarios));
    }
    
    public function getCadastro(){
        return View::make('usuarios.adicionar');
    }
    
    public function postCadastro(){
        /*if(Auth::user()->tipo != 'administrador'){
            return Redirect::to('restrito');
        }*/

        //BaseController::debug(Input::all(), TRUE);
        
        $rules = array(
            'nome'            => 'required|min:5',
            'username'        => 'required|min:5',
            'password'        => 'required|min:6',
            'email'           => 'required|email|unique:usuarios,email',
        );
        $messages = array(
            'required' => 'O campo :attribute é obrigatório.',
            'min'      => 'A :attribute deve ter no mínimo :min caracteres.',
            'unique'   => 'O :attribute já existe.'
        );
        $validator = Validator::make(Input::all(), $rules, $messages);
        if ($validator->fails()) {
            return Redirect::back()->withErrors($validator)->withInput();
        }
        
        //BaseController::debug(Input::all(), TRUE);

        $usuario = new User();
        $usuario->nome  = Input::get('nome');
        $usuario->login = Input::get('username');
        $usuario->senha = Hash::make(Input::get('password'));
        $usuario->email = Input::get('email');

        if(!$usuario->save())
            return Redirect::back()->with('flash_error', 'Nome de usuário ou senha inválido.')->withInput();
        
        $user = User::where('login', '=', Input::get('username'))->first();
        Auth::login($user);
        return Redirect::to('/')->with('success', 'Usuário adicionado com sucesso');
    }
    
    public function getDesabilitar(){
        return Redirect::to('restrito');
    }
    
    public function postDesabilitar(){
        if(Input::has('id')){
            $registro = User::where('id', '=', Input::get('id'))->update(array('ativo' => 0));
            return json_decode(json_encode($registro), true);
        }
        return json_decode(array('sucesso' => false));
    }
    
    public function getHabilitar(){
        return Redirect::to('restrito');
    }
    
    public function postHabilitar(){
        if(Input::has('id')){
            $registro = User::where('id', '=', Input::get('id'))->update(array('ativo' => 1));
            return json_decode(json_encode($registro), true);
        }
        return json_decode(array('sucesso' => false));
    }

    public function getRecuperarSenha(){
        return View::make('usuarios.recovery');
    }

    public function postRecuperarSenha(){
        return View::make('login.index');
    }
}

