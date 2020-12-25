<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::controller('service', 'ServiceController');
Route::controller('relatorio', 'ReportController');
Route::controller('correios', 'CorreiosController');
Route::controller('usuarios', 'UserController');
Route::controller('login', 'LoginController');

/*Route::get('/', function()
{
	return View::make('hello');
});*/

//Utiliza o filtro 'auth' e redireciona para o login caso o usuario não esteja logado
Route::group(array('before' => 'auth'), function()
{
    Route::get('sair', 'LoginController@sair');
    Route::get('restrito', 'HomeController@restrito');
    
	Route::group(array('before' => 'auth.admin'), function()
	{
        //Route::controller('usuario', 'UserController');
    });
    
    //Route::controller('correios', 'CorreiosController');
    Route::controller('/', 'DashboardController');
   
});
