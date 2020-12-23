<?php

class ReportController extends BaseController {
        
    public function getIndex($sendmail = 'true'){

        $totalUsers = User::count();
        $users      = User::take(10)->orderBy('id', 'DESC')->get();

        $totalTrackings = Tracking::where('ativo', '=', '1')->count();
        $trackings      = Tracking::take(10)->where('ativo', '=', '1')->orderBy('id', 'DESC')->get();

        if($sendmail == 'true') {

            /////ENVIO DO EMAIL //////////////////

            $data = array(
                'totalUsers'     => $totalUsers,
                'users'          => $users,
                'totalTrackings' => $totalTrackings,
                'trackings'      => $trackings,
            );

            $rcpto = ['rogeriobenco@gmail.com'];

            Mail::send('emails.report', $data, function($message) use ($rcpto){
                $message->from('rastreio@cooltracker.com.br', 'Rastreio - Cooltracker [ REPORT ]');
                $message->to($rcpto)->subject('Relatório Mensal ' . date('d/m/Y'));
            });

            BaseController::debug('Enviado por email');
            BaseController::debug('============================================');

        } else {

            echo '<pre>';

                echo 'Total de usuários no cadastrados:' . $totalUsers . '<br><br>';
                echo 'Os 10 últimos<br>';

                foreach($users as $user){

                    echo "&nbsp;&nbsp;&nbsp;{$user->nome} | {$user->login} | {$user->email} | {$user->facebook} | {$user->created_at}<br>";

                }

                echo '<br><br><br>';

                echo 'Total de rastreios cadastrados: ' . $totalTrackings . '<br><br>';
                echo 'Os 10 últimos<br>';

                foreach($trackings as $tracking){

                    echo "&nbsp;&nbsp;&nbsp;{$tracking->numero} | {$tracking->descricao} | {$tracking->status} | {$tracking->data_status} | {$tracking->created_at}<br>";

                }

            echo '</pre>';

        }

    }

}