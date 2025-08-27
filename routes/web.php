<?php

use App\Controllers\OauthController;
use Helpers\Route;
use Helpers\View;

Route::get('/', function(){
    $title = 'BPJS Framework';
    return view('welcome/welcome',['title'=>$title]);
});
Route::get('/v1/callback',[OauthController::class, 'callback']);
Route::get('/v1/test-oauth',[OauthController::class, 'testOauth']);