@extends('templateLogin')

    @section('content')
        <div class="page login-page">
            <div class="container d-flex align-items-center">
                <div class="form-holder has-shadow">
                    <div class="row">
                        <!-- Logo & Information Panel-->
                        <div class="col-lg-6">
                            <div class="info d-flex align-items-center">
                                <div class="content">
                                    <div class="logo">
                                        <h1>Cooltracker</h1>
                                    </div>
                                    <p>A maneira mais fácil e divertida de<br/>acompanhar suas entregas dos Correios.</p>
                                </div>
                            </div>
                        </div>
                        <!-- Form Panel    -->
                        <div class="col-lg-6 bg-white"> 
                            <div class="form d-flex align-items-center">
                                <div class="content">
                                    @if(Session::has('message'))
                                        <div class="badge bg-red">
                                            {{ Session::get('message') }}
                                        </div>
                                        <br/><br/>
                                    @endif
                                    {{ Form::open(array('url' => 'login', 'method' => 'POST', 'id' => 'login-form')) }}
                                        <div class="form-group">
                                            <input id="login-username" type="text" name="usuario" required="" class="input-material">
                                            <label for="login-username" class="label-material">Nome de Usuário</label>
                                            @if($errors->has('usuario')) {{ $errors->first('usuario') }} @endif
                                        </div>
                                        <div class="form-group">
                                            <input id="login-password" type="password" name="senha" required="" class="input-material">
                                            <label for="login-password" class="label-material">Senha</label>
                                            @if($errors->has('senha')) {{ $errors->first('senha') }} @endif
                                        </div>
                                        <button type="submit" id="login" class="btn btn-primary">Entrar</button>
                                    {{ Form::close() }}
                                    <a href="#" class="forgot-pass">Esqueceu a senha?</a><br><small>Não tem uma conta? </small><a href="#" class="signup">Crie!</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="copyrights text-center">
                <p>Design by <a href="http://bencosys.com.br" class="external">Bencosys</a>
                </p>
            </div>

        </div>
    @stop