<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ErrorController as Error;

class HelperController extends Controller
{

    public static $messages = [
        'pt_br' => [
            //REQUISICAO
            'error.invalid.response' => 'Resposta inválida',
            'method.not.allowed' => 'O metodo GET não é permitido para essa requisição.',
            'error.querying.tracking' => 'Erro ao consultar o código de rastreio. Não foi possível conectar ao servidor.',
            'error.tracking.format' => 'Erro. Código de rastreio inválido.',
            'untracktable' => 'Pacote não rastreável. Não foi atualizado.',
            'info.not.updated' => 'Nenhuma alteração. O pacote não foi atualizado.',
            'updated' => 'O pacote recebeu uma atualização de rastreio.',
            'error.access.denied' => 'Acesso negado. Você precisa estar logado para acessar essa área.',
            'required.field.id' => 'O campo ID é obrigatório',
            'info.empty.result' => 'Não foram encontrados registros para a consulta.',

            //CADASTRO
            'info.update.success' => 'O pacote foi atualizado com sucesso!',
            'info.register.tracking.success' => 'O código de rastreio foi cadastrado com sucesso!',
            'info.register.tracking.failure' => 'Ocorreu um erro ao cadastrar o código de rastreio.',
            'required.field.user' => 'O campo usuário é obrigatório',
            'required.field.tracking' => 'O campo tracking é obrigatório',
            'required.field.description' => 'O campo descrição é obrigatório',
            'required.fiel.min' => 'O código de rastreio deve possuir 13 caracteres',
            'required.field.max' => 'O código de rastreio deve possuir 13 caracteres',
            'required.field.format' => 'O código de rastreio deve ter o padrão: AB123456789BR',
            'required.field.unique' => 'Erro. O código de rastreio já consta cadastrado na base de dados.',
            'error.tracking.required' => 'Erro. O código de rastreio é obrigatório.',
            'info.tracking.not.registered' => 'Código de rastreio não cadastrado na base de dados',

            //GERAL
            'error.exception' => 'Ocorreu um erro desconhecido, por favor tente novamente mais tarde.'
        ],
        'en_us' => [
            //REQUISICAO
            'error.invalid.response' => 'Invalid Response',
            'method.not.allowed' => 'The GET method is not allowed for this request.',
            'error.querying.tracking' => 'Error querying tracking code. Could not connect to the server.',
            'error.tracking.format' => 'Error. Tracking code invalid.',
            'untracktable' => 'Package untracktable. There was no update.',
            'info.not.updated' => 'No changes. The package was not updated.',
            'updated' => 'The package has receive a tracking update.',
            'error.access.denied' => 'Access denied. You must be logged in to access this area.',
            'required.field.tracking' => 'The field ID is required.',
            'info.empty.result' => 'No records were found for the query.',

            //CADASTRO
            'info.update.success' => 'The package has been successfully updated!',
            'info.register.tracking.success' => 'The tracking code has been successfully registered!',
            'info.register.tracking.failure' => 'There was an error registering the tracking code.',
            'required.field.user' => 'The field user is required',
            'required.field.tracking' => 'The field tracking is required',
            'required.field.description' => 'The field description is required',
            'required.fiel.min' => 'The tracking code must be 13 characteres',
            'required.field.max' => 'The tracking code must be 13 characteres',
            'required.field.format' => 'The tracking code must have the pattern: AB123456789BR',
            'required.field.unique' => 'Error. The tracking code already registered in the database.',
            'error.tracking.required' => 'Error. The tracking code is required.',
            'info.tracking.not.registered' => 'Tracking code not registered in the database.',

            //GERAL
            'error.exception' => 'An unknown error occurred, please try again later.'
        ]
    ];

    public function __construct()
    {
        
    }

    static function debug($arg, bool $exit = false)
    {
        echo '<pre>';
		print_r($arg);
		echo '</pre>';

		if($exit){
			die;
		}
    }

    static function dump($arg, bool $exit = false)
    {
        echo '<pre>';
		var_dump($arg);
		echo '</pre>';

		if($exit){
			die;
		}
    }

    static function response($response = null, $httpCode = 200, $success = true, $error = false)
    {
        if (!$response) {
            $response = self::$messages[self::$i18n]['error.invalid.response'];
            $httpCode = 403;
            $success = false;
        }

        if(!is_array($response)) {
            if(array_key_exists($response, self::$messages[self::$i18n])) {
                $response = self::$messages[self::$i18n][$response];
            }
        } else {

            if ($error) {
                foreach ($response as $err) {
                    if(is_string($err) || is_int($err)) {
                        if(array_key_exists($err, self::$messages[self::$i18n])) {
                            $arrErrors[] = self::$messages[self::$i18n][$err];
                        } else {
                            $arrErrors[] = $err;
                        }
                    } else {
                        $arrErrors[] = $err;
                    }
                }
                $response = $arrErrors;
            }
        }

        header('Content-type: application/json', true, $httpCode);
        echo json_encode(["success" => $success, "http_code" => $httpCode, "data" => $response]);
		exit;
    }

}