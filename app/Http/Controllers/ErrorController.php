<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

class ErrorController extends Controller
{
    private static $errorCode = [
        401 => ['unauthorized' => 'Essa requisição não pode ser realizado via GET.'],
        403 => ['forbidden' => 'Esse metodo só aceita requisições autenticadas.'],
        404 => ['not_found' => 'A requisição solicitada não foi encontrada no servidor.']
    ];

    public function __construct()
    {

    }

    public static function response($strMsg = null, $httpCode = 0, $success = false)
    {
        $httpCode = (key_exists($strMsg, self::$errorCode)) ? $strMsg : $httpCode;
        $msg = '';

        switch ($httpCode) {
            case 0: $msg = 'Resposta inválida';
            break;
            default: $msg = $strMsg;
        }

        $jsonResponse = [
            'success' => ($httpCode) ? $success : false,
            'data' => [
                'httpCode' => $httpCode,
                'msg' => (key_exists($httpCode, self::$errorCode)) ? self::$errorCode[$httpCode] : $msg
            ]
        ];

        header('Content-type: application/json', true, $httpCode);
		echo json_encode($jsonResponse);
		exit;

    }
}