<?php

class DashboardController extends BaseController {
        

    public function getIndex($termo = null){
        //if(Auth::guest()) return Redirect::to('login');

        if(Input::get('search')) {

            $termo = Input::get('search');

            $records = Tracking::where('usuario_id', '=', Auth::id())
            ->where('ativo', '=', 1)
            ->where(function($query) use ($termo){
                $query->where('numero', '=', "'{$termo}'")
                ->orWhere('descricao', 'LIKE', "%{$termo}%");
            })
            ->orderBy('novo_status', 'DESC')
            ->orderBy('atualiza_status', 'DESC')
            ->orderBy('id', 'DESC')
            ->paginate(15);
    
            $atualizacoes = Tracking::where('usuario_id', '=', Auth::id())
            ->where('ativo', '=', 1)
            ->where('novo_status', '=', '1')
            ->where(function($query) use ($termo){
                $query->where('numero', '=', "'{$termo}'")
                ->orWhere('descricao', 'LIKE', "%{$termo}%");
            })->count();

        } else {
        
            $records = Tracking::where('usuario_id', '=', Auth::id())
            ->where('ativo', '=', 1)
            ->orderBy('novo_status', 'DESC')
            ->orderBy('atualiza_status', 'DESC')
            ->orderBy('id', 'DESC')
            ->paginate(15);

            $atualizacoes = Tracking::where('usuario_id', '=', Auth::id())
            ->where('ativo', '=', 1)
            ->where('novo_status', '=', '1')
            ->count();

        }

        $dados = [
            'records'      => $records,
            'atualizacoes' => $atualizacoes
        ];
        
        return View::make('dashboard.index', $dados);
    }

    public function postIndex(){
        $termo = Input::get('search');
        
        $records = Tracking::where('usuario_id', '=', Auth::id())
        ->where('ativo', '=', 1)
        ->where(function($query) use ($termo){
            $query->where('numero', '=', "'{$termo}'")
            ->orWhere('descricao', 'LIKE', "'%{$termo}%'");
        })->orderBy('id', 'DESC')->paginate(15);

        $atualizacoes = Tracking::where('usuario_id', '=', Auth::id())
        ->where('ativo', '=', 1)
        ->where('novo_status', '=', '1')
        ->where(function($query) use ($termo){
            $query->where('numero', '=', "'{$termo}'")
            ->orWhere('descricao', 'LIKE', "'%{$termo}%'");
        })->count();
        
        $dados = [
            'records'      => $records,
            'atualizacoes' => $atualizacoes
        ];
        //var_dump($dados);

        return View::make('dashboard.index', $dados);
    }
    
    public function getDetalhes($track_id){
        
        $records = TrackingHistory::where('rastreio_id', '=', $track_id)
        ->orderBy('data_sql', 'DESC')
        ->get();
        
        return json_encode($records);
    }
    
    public function getRead($numero){
        $update = Tracking::where('numero', '=', $numero)->update(array('novo_status' => 0));
        
        return json_encode($update);
    }
    
    public function getDelete($numero){
        $update = Tracking::where('numero', '=', $numero)->update(array('ativo' => 0));
        
        return json_encode($update);
    }
    
    public function getCadastro(){
        $atualizacoes = Tracking::where('usuario_id', '=', Auth::id())->where('ativo', '=', 1)->where('novo_status', '=', '1')->count();
        $dados = ['atualizacoes' => $atualizacoes];

        return View::make('dashboard.cadastro', $dados);
    }
    
    public function postCadastro(){
        $rules = array(
            'tracking' => 'required|Min:13|Max:13',
            'descricao' => 'required'
        );
        
        $messages = array(
            'required' => 'O campo :attribute é obrigatório.',
            'min' => 'O campo :attribute deve conter 13 caracteres.',
            'max' => 'O campo :attribute deve conter 13 caracteres.'
        );
        
        $validator = Validator::make(Input::all(), $rules, $messages);

        if ($validator->fails()) {

            $message = "
                <div class='alert alert-danger alert-dismissable'>
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                    Erro ao cadastrar a encomenda. Verifique o código e a descrição.
                </div>
            ";

            return Redirect::back()->with('message', $message)->withInput();
            //return Redirect::back()->withErrors($validator)->withInput();
        }
        
        $tracking = new Tracking();
        $tracking->usuario_id = Auth::id();
        $tracking->numero = Input::get('tracking');
        $tracking->descricao = Input::get('descricao');
        $tracking->status = 'nao rastreavel';
        $tracking->data_status = '1970-01-01 00:00:00';
        $tracking->atualiza_status = 1;
        $tracking->novo_status = 1;
        $tracking->ativo = 1;

        if($tracking->save()){

            $message = "
                <div class='alert alert-success alert-dismissable'>
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                    Encomenda cadastrada!
                </div>
            ";

            return Redirect::back()->with('message', $message);

        }else{

            $message = "
                <div class='alert alert-danger alert-dismissable'>
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                    Erro ao cadastrar a encomenda!
                </div>
            ";

            return Redirect::back()->with('message', $message);

        }

    }
    
    public function getEmail(){
        $user = new stdClass();
        $user->email = 'rogeriobenco@gmail.com';
        $user->nome = 'Rogério';
        
        $data = array(
                'cod_rastreio' => 'AB123456789BR',
                'detail'       => 'saiu para entrega'
        );
        
        Mail::send('emails.rastreio', $data, function($message) use ($user){
          $message->from('rastreio@cooltracker.com.br', 'Rastreio - Cooltracker');
          $message->to($user->email, $user->nome)->subject('Novidades sobre sua encomenda!! ' . date('d/m/Y H:i:s'));
        });
    }

    public function getVersion(){
        Artisan::version();
    }

}
