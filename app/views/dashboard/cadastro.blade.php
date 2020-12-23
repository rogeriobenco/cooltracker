@extends('template')

    @section('content')
        <div class="content-inner">
            <!-- Page Header-->
            <header class="page-header">
                <div class="container-fluid">
                <h2 class="no-margin-bottom">Cadastro</h2>
                </div>
            </header>
            <!-- Breadcrumb-->
            <div class="breadcrumb-holder container-fluid">
                <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('cadastro') }} ">Cadastro</a></li>
                <li class="breadcrumb-item active">Cadastro</li>
                </ul>
            </div>
            <section class="tables">
                <div class="container d-flex align-items-center">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header d-flex align-items-center">
                                    <h3 class="h4">
                                        Cadastre sua nova encomenda
                                    </h3>
                                </div>
                                <div class="card-body">
                                    @if(Session::has('message'))
                                        <div class="badge">
                                            {{ Session::get('message') }}
                                        </div>
                                        <br/><br/>
                                    @endif
                                    {{ Form::open(array('url' => url('cadastro'), 'method' => 'POST', 'class' => 'form-horizontal tasi-form')) }}
                                        <label class="col-sm-3 form-control-label"></label>
                                        <div class="col-sm-9">
                                            <div class="form-group-material">
                                                <input id="register-username" type="text" name="tracking" class="input-material">
                                                <label for="register-username" class="label-material">Código de Rastreio</label>
                                                @if($errors->has('tracking')) {{ $errors->first('tracking') }} @endif
                                            </div>
                                            <div class="form-group-material">
                                                <input id="register-email" type="text" name="descricao" class="input-material">
                                                <label for="register-email" class="label-material">Descrição</label>
                                                @if($errors->has('descricao')) {{ $errors->first('descricao') }} @endif
                                            </div>
                                            <button type="submit" id="login" class="btn btn-primary">Cadastrar</button>
                                        </div>
                                    {{ Form::close() }}
                                </div>
                            </div>
                        </div>
                </div>
            </section>


    @stop