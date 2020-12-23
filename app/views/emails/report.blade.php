<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    </head>
    <style>
        .content {
            width: 80%;
            margin: auto;
            position:relative;
            font-family: 'Source Sans Pro', sans-serif;
        }

        @media (max-width: 799px) {
            .content{
                width: 99%;
            }
        }

        .table {
            width: 90%;
            margin: 0;
            position:relative;
            float:left;
            font-family: 'Source Sans Pro', sans-serif;
        }
    </style>
    <body>
        <div class="content">
            <!-- PRINCIPAL -->
            <table border="0" class="table" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th colspan="1" align="center" style="padding: 5px 10px 10px 5px; background-color:#68ACC9; border-top-left-radius:10px; border-top-right-radius: 10px;">
                            <div style="margin: 10px; width: 100%">
                                <img src="http://www.cooltracker.com.br/img/avatar.png" border="0" width="20%" style="float:left">
                            </div>
                            <div style="clear:both; margin: 0px 20px 10px 0px; width: 100%; text-align:right; color:#F4F4F4; font-size: 18px">
                                Rastreio de Encomendas [ REPORT ]
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="1" style="height: 10px;"></td></tr>
                    <tr><td colspan="1" style="height: 2px; background-color: #F56954; border-top-left-radius: 10px; border-top-right-radius: 10px"></td></tr>

                    <tr style="background-color: #efefef">
                        <td colspan="1" style="padding: 10px; font-size: 12px">Olá, esse é o relatório semanal das atividades do app</td>
                    </tr>
                    <tr style="background-color: #efefef"><th colspan="1" style="height: 40px;"></th></tr>

                    <!-- USERS -->
                    <tr style="background-color: #efefef">
                        <td colspan="1">
                            <table border="0" class="table" cellpadding="0" cellspacing="0" style="width: 100%">
                                <thead>
                                    <tr style="background-color: #efefef"><th colspan="5" style="height: 30px;"></th></tr>
                                    <tr style="background-color: #efefef">
                                        <th colspan="5" style="padding: 10px">
                                            <b><big>Total de usuários no cadastrados: </big></b>&nbsp;&nbsp;
                                            <b><big><big>{{ $totalUsers }}</big></big></b>
                                        </th>
                                    </tr>
                                    <tr style="background-color: #efefef"><th colspan="5" style="height: 20px;"></th></tr>
                                    <tr style="background-color: #efefef">
                                        <th colspan="5" style="padding: 10px; font-size: 12px">Os 10 últimos</th>
                                    </tr>
                                    <tr style="background-color: #efefef"><th colspan="5" style="height: 20px;"></th></tr>
                                    <tr style="background-color: #efefef">
                                        <th style="padding: 3px; font-size: 9px; font-weight: bold; border-bottom: 3px double #CECECE;">Nome</th>
                                        <th style="padding: 3px; font-size: 9px; font-weight: bold; border-bottom: 3px double #CECECE;">Login</th>
                                        <th style="padding: 3px; font-size: 9px; font-weight: bold; border-bottom: 3px double #CECECE;">Email</th>
                                        <th style="padding: 3px; font-size: 9px; font-weight: bold; border-bottom: 3px double #CECECE;">&nbsp;</th>
                                        <th style="padding: 3px; font-size: 9px; font-weight: bold; border-bottom: 3px double #CECECE;">Data</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach($users as $user)

                                        <tr style="background-color: #efefef">
                                            <td style="padding: 3px; font-size: 9px; font-weight: normal; border-bottom: 1px solid #CECECE;">{{ utf8_decode($user->nome) }}</td>
                                            <td style="padding: 3px; font-size: 9px; font-weight: normal; border-bottom: 1px solid #CECECE;">{{ $user->login }}</td>
                                            <td style="padding: 3px; font-size: 9px; font-weight: normal; border-bottom: 1px solid #CECECE;">{{ $user->email }}</td>
                                            <td style="padding: 3px; border-bottom: 1px solid #CECECE;">
                                                @if($user->facebook) <img src="http://www.cooltracker.com.br/assets/img/facebook.png" border="0" style="width: 12px; height: 12px;"> @endif
                                            </td>
                                            <td colspan="5" style="padding: 3px; font-size: 9px; font-weight: normal; border-bottom: 1px solid #CECECE;">{{ date('d/m/Y', strtotime($user->created_at)) }}</td>
                                        </tr>

                                    @endforeach

                                </tbody>
                            </table>
                            <!-- END USERS -->
                        </td>
                    </tr>
                    <tr style="background-color: #efefef"><th colspan="1" style="height: 20px;"></th></tr>

                    <!-- TRACKINGS -->
                    <tr style="background-color: #efefef">
                        <td>
                            <table border="0" class="table" cellpadding="0" cellspacing="0">
                                <thead>
                                    <tr style="background-color: #efefef"><th colspan="5" style="height: 30px;"></th></tr>
                                    <tr style="background-color: #efefef">
                                        <th colspan="5" style="padding: 10px">
                                            <b><big>Total de rastreios cadastrados: </big></b>&nbsp;&nbsp;
                                            <b><big><big>{{ $totalTrackings }}</big></big></b>
                                        </th>
                                    </tr>
                                    <tr style="background-color: #efefef"><th colspan="5" style="height: 20px;"></th></tr>
                                    <tr style="background-color: #efefef">
                                        <th colspan="5" style="padding: 10px; font-size: 12px">Os 10 últimos</th>
                                    </tr>
                                    <tr style="background-color: #efefef"><th colspan="5" style="height: 20px;"></th></tr>
                                    <tr style="background-color: #efefef">
                                        <th style="padding: 3px; font-size: 10px; font-weight: bold; width: 15%; border-bottom: 3px double #CECECE;">Número</th>
                                        <th style="padding: 3px; font-size: 10px; font-weight: bold; width: 30%; border-bottom: 3px double #CECECE;">Descrição</th>
                                        <th style="padding: 3px; font-size: 10px; font-weight: bold; width: 15%; border-bottom: 3px double #CECECE;">Status</th>
                                        <th style="padding: 3px; font-size: 10px; font-weight: bold; width: 20%; border-bottom: 3px double #CECECE;">Atualizado em</th>
                                        <th style="padding: 3px; font-size: 10px; font-weight: bold; width: 20%; border-bottom: 3px double #CECECE;">Data</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach($trackings as $tracking)

                                        <tr style="background-color: #efefef">
                                            <td style="padding: 3px; font-size: 9px; font-weight: normal; border-bottom: 1px solid #CECECE;">{{ $tracking->numero }}</td>
                                            <td style="padding: 3px; font-size: 9px; font-weight: normal; border-bottom: 1px solid #CECECE;">{{ utf8_decode($tracking->descricao) }}</td>
                                            <td style="padding: 3px; font-size: 9px; font-weight: normal; border-bottom: 1px solid #CECECE;">{{ utf8_decode($tracking->status) }}</td>
                                            <td style="padding: 3px; font-size: 9px; font-weight: normal; border-bottom: 1px solid #CECECE;">{{ date('d/m/Y', strtotime($tracking->data_status)) }}</td>
                                            <td style="padding: 3px; font-size: 9px; font-weight: normal; border-bottom: 1px solid #CECECE;">{{ date('d/m/Y', strtotime($tracking->created_at)) }}</td>
                                        </tr>

                                    @endforeach

                                </tbody>
                            </table>
                            <!-- END TRACKINGS -->
                        </td>
                    </tr>

                    <tr style="background-color: #efefef"><td colspan="1" style="height: 30px;"></td></tr>
                    <tr><td colspan="1" style="height: 2px; background-color: #F56954; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px"></td></tr>
                    <tr><td colspan="1" style="height: 10px;"></td></tr>
                    <tr>
                        <td colspan="1" align="center" style="padding:0; background-color:#EEEEEE; border-bottom-left-radius:10px; border-bottom-right-radius: 10px;">
                            <div style="margin:15px 0px 5px 10px; width:100%; position:relative; float:left">
                                <img src="http://www.cooltracker.com.br/img/logo_bencosys.png" border="0" width="17%" style="float:left">
                            </div>
                            <div style="margin:0px 0px 10px 12px; width:100%; position:relative; float:left; color:#383B3F; font-size:10px; text-align:left">
                                www.bencosys.com.br - contato@bencosys.com.br
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <!-- FIM PRINCIPAL -->
        </div>
    </body>
</html>
