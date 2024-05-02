<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use App\Http\Controllers\ErrorController as Error;
use App\Http\Controllers\HelperController as Helper;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private const AUTH_API_KEY = 'DpDudhy4q+z+flWI7T1qcC5SFb07D+ALslKhO5O6HMm3mkzejokvu1uEe96qEGzWosyGcD8/BWSc+SGWxas0sg==';
    public static $i18n = 'pt_br';

    public static function isAuthenticated()
    {
        $headers = getallheaders();
        $authBasicPrefix = 'Basic ';
        $authBasic = array_key_exists('Authorization', $headers) ? $headers['Authorization'] : false;

        if ($authBasic && 0 === strpos($authBasic, $authBasicPrefix)) {
            $authBasic = str_replace($authBasicPrefix, '', $authBasic);

            if ($authBasic != self::AUTH_API_KEY) {
                return false;
            }
            
            return true;
        }

        return false;
        
    }

    public static function getLanguage()
    {
        return self::$i18n;
    }

    public static function setLanguage($i18n = 'pt_br')
    {
        self::$i18n = $i18n;
        return true;
    }
}
