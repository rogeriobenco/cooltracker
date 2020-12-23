@extends('template')

    @section('notifications')
        @if($atualizacoes > 0)    
            <span class="badge bg-red">{{ $atualizacoes }}</span>
        @endif
    @stop

    @section('content')
        <div class="content-inner">
            <!-- Page Header-->
            <header class="page-header">
                <div class="container-fluid">
                <h2 class="no-margin-bottom">Encomendas</h2>
                </div>
            </header>
            <!-- Breadcrumb-->
            <div class="breadcrumb-holder container-fluid">
                <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Encomendas</li>
                </ul>
            </div>
            <section class="tables">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-close">
                                    <div class="dropdown">
                                        <button type="button" id="closeCard1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="dropdown-toggle"><i class="fa fa-ellipsis-v"></i></button>
                                        <div aria-labelledby="closeCard1" class="dropdown-menu dropdown-menu-right has-shadow">
                                            <a href="#" class="dropdown-item remove"> <i class="fa fa-times"></i>Close</a>
                                            <a href="#" class="dropdown-item edit"> <i class="fa fa-gear"></i>Edit</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-header d-flex align-items-center">
                                    <h3 class="h4">
                                        @if($records->getTotal() > 1)
                                            Você possui {{ $records->getTotal() }} encomendas cadastradas
                                        @elseif($records->getTotal() == 1)
                                            Você possui {{ $records->getTotal() }} encomenda cadastrada
                                        @else
                                            Você ainda não possui nenhuma encomenda cadastrada
                                        @endif
                                    </h3>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 5%"><i class="fa fa-flag fa-2x"></i></th>         <!-- Origem    -->
                                                    <th style="width: 10%"><i class="fa"></i></th>                      <!-- Codigo    -->
                                                    <th style="width: 5%"></th>                                         <!-- Vago      -->
                                                    <th style="width: 37%"><i class="fa fa-file-text-o fa-2x"></i></th> <!-- Descricao -->
                                                    <th style="width: 20%"><i class="fa"></i></th>                      <!-- Status    -->
                                                    <th style="width: 14%"><i class="fa fa-calendar fa-2x"></i></th>    <!-- Data      -->
                                                    <th style="width: 14%"><i class="fa fa-gears fa-2x"></i></th>       <!-- Tarefas   -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($records as $row)
                                                <tr id="more-info-{{ $row->numero }}" class="@if($row->novo_status == 1) success @endif text-muted" rel="{{ $row->numero }}" style="cursor:pointer">
                                                    <td class="more-info text-muted" rel="{{ $row->numero }}"><img src='{{url('img/flags/24/' . strtolower(substr($row->numero, -2))) }}.png' border='0' style="width:24px" /></td>
                                                    <td class="more-info text-muted" rel="{{ $row->numero }}">{{ $row->numero }}</td>
                                                    <td class="more-info text-muted" rel="{{ $row->numero }}"><img src='{{url('img/smiles/24/' . HelpersController::limpaString($row->icone)) }}.png' border='0' style="width:24px" /></td>
                                                    <td class="more-info text-muted" rel="{{ $row->numero }}">{{ $row->descricao }}</td>
                                                    <td class="more-info text-muted" rel="{{ $row->numero }}">{{ $row->status }}</td>
                                                    <td class="more-info text-muted" rel="{{ $row->numero }}">@if(date('Y', strtotime($row->data_status)) > 2000) {{ date('d/m/Y H:i:s', strtotime($row->data_status)) }} @endif</td>
                                                    <td>
                                                        <!-- button class="btn btn-primary btn-xs" class='operations' onclick="editar('{{ $row->numero }}')"><i class="fa fa-pencil"></i></button -->
                                                        <button class="btn btn-danger btn-xs" class='operations'onclick="excluir('{{ $row->numero }}')"><i class="fa fa-trash-o "></i></button>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="7" class="loading text-center" id="loading-{{ $row->numero }}" style="display:none; text-align:center; border:none !important"><i class="fa fa-spinner fa-spin fa-1x"></i><br/><br/></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="7" class="detalhe" id="detalhe-{{ $row->numero }}" style="display: none; border:none; background-color:#EEF5F9 !important">
                                                        <div class="card" style="background-color:#EEF5F9 !important">
                                                            <div class="card-body">
                                                                <table class="table table-responsive table-condensed" style="background-color:#EEF5F9 !important">
                                                                    <thead>
                                                                        <th style="width: 25%"><i class="text-muted fa fa-calendar"></i></th>    <!-- Data     -->
                                                                        <th style="width: 25%"><i class="text-muted fa fa-map-o"></i></th>       <!-- Local    -->
                                                                        <th style="width: 25%"><i class="text-muted fa fa-file-text-o"></i></th> <!-- Acao     -->
                                                                        <th style="width: 25%"><i class="text-muted fa fa-file-text"></i></th>   <!-- Detalhes -->
                                                                    </thead>
                                                                    <tbody id="{{ $row->numero }}"></tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                        <div id="pagination" style="margin-left: 30px">
                                            {{ $records->links() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Page Footer-->
            <footer class="main-footer">
                <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                    <p>Your company &copy; 2017-2019</p>
                    </div>
                    <div class="col-sm-6 text-right">
                    <p>Design by <a href="https://bootstrapious.com/admin-templates" class="external">Bootstrapious</a></p>
                    <!-- Please do not remove the backlink to us unless you support further theme's development at https://bootstrapious.com/donate. It is part of the license conditions. Thank you for understanding :)-->
                    </div>
                </div>
                </div>
            </footer>
        </div>
    @stop

    @section('scripts')
        <script>

            function cleanUpSpecialChars(str){
                str = str.replace(/[ÀÁÂÃÄ]/g,"A");
                str = str.replace(/[àáâãä]/g,"a");
                str = str.replace(/[ÈÉÊË]/g,"E");
                str = str.replace(/[èéêë]/g,"e");
                str = str.replace(/[ÌÍÎÏ]/g,"I");
                str = str.replace(/[ìíîï]/g,"i");
                str = str.replace(/[ÒÓÔÕÖ]/g,"O");
                str = str.replace(/[òóôõö]/g,"o");
                str = str.replace(/[ÙÚÛÜ]/g,"U");
                str = str.replace(/[ùúûü]/g,"u");
                
                return str;
            }
        
            let arrTrack = [];
            let arrStatusFinal = ['entregue', 'entrega efetuada', 'objeto entregue ao destinatÃ¡rio', 'objeto entregue ao destinatário'];

            $(function(){

                $('.more-info').click(function(){
                    let numero = $(this).attr('rel');

                    //REQUEST AJAX IF "detalhe" IS NOT VISIBLE
                    if(! $('#detalhe-' + numero).is(':visible')){

                        $.ajax({
                            type: 'GET',
                            dataType: 'json',
                            url: './detalhes/' + numero,
                            beforeSend: function(){
                                $('#loading-' + numero).toggle();
                            },
                            success: function(data){

                                $('#loading-' + numero).css({display:"none"});
                                $('#detalhe-' + numero).toggle();

                                if(data.length > 0) {

                                    if($.inArray(numero, arrTrack) < 0) {
                                        $.each(data, function(idx, item){
                                            let final = ($.inArray(item.acao, arrStatusFinal) > -1) ? '<span class="label label-success label-mini">&nbsp;<i class="fa fa-check"></i>&nbsp;</span>' : '';
                                            $('#' + numero).append('<tr>' +
                                                '<td class="text-muted">' + item.data + '</td>' +
                                                '<td class="text-muted">' + item.local + '</td>' +
                                                '<td class="text-muted">' + item.acao + '</td>' +
                                                '<td class="text-muted">' + item.detalhes + '</td>' +
                                                '</tr>' +
                                                '</table>');
                                        });
                                        arrTrack.push(numero);
                                    }
                                    atualiza(numero);

                                }else{

                                    $('#' + numero).html('<tr>' +
                                        '<td>&nbsp;</td>' +
                                        '<td>Nenhuma informção localizada</td>' +
                                        '<td>&nbsp;</td>' +
                                        '<td>&nbsp;</td>' +
                                        '</tr></table>');

                                }

                            }
                        });

                    }else{

                        //HIDE "detalhe"
                        $('#detalhe-' + numero).toggle();
                    }

                });

            });

            function atualiza(numero){

                $.ajax({
                    type: 'GET',
                    dataType: 'json',
                    url: './read/' + numero,
                    beforeSend: function(){

                    },
                    success: function(data){
                        //console.log(data);
                        $('#more-info-' + numero).removeClass('success');

                        let valor_atual = $('#atualizacoes').text();
                        if((valor_atual - 1) > 1){
                            $('#atualizacoes').html(valor_atual - 1);
                            $('.atualizacoes').html((valor_atual - 1) + ' encomendas receberam atualizações.');
                        }else{
                            if((valor_atual -1) == 1){
                                $('#atualizacoes').html(valor_atual - 1);
                                $('.atualizacoes').html((valor_atual - 1) + ' encomenda recebeu atualização.');
                            }else{
                                $('#atualizacoes').html('0');
                                $('.atualizacoes').html('Nenhuma encomenda recebeu atualização.');
                            }
                        }
                    }
                });
            }

            function excluir(numero){
                if(confirm("Tem certeza que deseja excluir a encomenda?")){
                    $.ajax({
                        type: 'GET',
                        dataType: 'json',
                        url: './delete/' + numero,
                        beforeSend: function(){

                        },
                        success: function(data){
                            $('#more-info-' + numero).fadeOut();

                        }
                    });
                }
            }

        </script>
    @stop