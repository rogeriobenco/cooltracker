<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    </head>
    <style>
        .content {
            width: 70%;
            margin: auto;
            position:relative;
            font-family: 'Source Sans Pro', sans-serif;
        }

        @media (max-width: 799px) {
            .content{
                width: 100%;
            }
        }

        .table {
            width: 100%;
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
                        <th colspan="2" align="center" style="padding: 5px 10px 10px 5px; background-color:#68ACC9; border-top-left-radius:10px; border-top-right-radius: 10px;">
                            <div style="margin: 10px; width: 100%">
                                <img src="http://www.cooltracker.com.br/img/avatar.png" border="0" width="20%" style="float:left">
                            </div>
                            <div style="clear:both; margin: 0px 20px 10px 0px; width: 100%; text-align:right; color:#F4F4F4; font-size: 22px">
                                Rastreio de Encomendas
                                <!-- img src="http://www.cooltracker.com.br/images/lista_niver.png" border="0" width="45%"-->
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="2" style="height: 10px;"></td></tr>
                    <tr><td colspan="2" style="height: 2px; background-color: #F56954; border-top-left-radius: 10px; border-top-right-radius: 10px"></td></tr>
                    <tr style="background-color: #efefef">
                        <td colspan="2" style="padding: 10px; font-size: 12px">Olá, utilize o código abaixo no App para recuperar a sua senha:</td>
                    </tr>
                    <tr style="background-color: #efefef">
                        <td colspan="2" style="padding: 10px">
                            <center><b><big><big>{{ $remember_token }}</big></big></b></center>
                    </tr>

                    <tr style="background-color: #efefef"><td colspan="2" style="height: 20px;"></td></tr>

                    <tr style="background-color: #efefef">
                        <td colspan="2" style="padding: 10px; font-size: 12px">Se você não solicitou a recuperação da sua senha, por favor desconsidere essa mensagem.</td>
                    </tr>
                    <tr style="background-color: #efefef"><td colspan="2" style="height: 15px;"></td></tr>
                    <tr><td colspan="2" style="height: 2px; background-color: #F56954; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px"></td></tr>

                    <tr><td colspan="2" style="height: 10px;"></td></tr>
                    <tr>
                        <td colspan="2" align="center" style="padding:0; background-color:#EEEEEE; border-bottom-left-radius:10px; border-bottom-right-radius: 10px;">
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
