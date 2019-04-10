<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/info', function () {
    phpinfo();
});

//微信
Route::get('/weixin/valid','weixin\WxController@valid');
Route::post('/weixin/valid','weixin\WxController@valide');
Route::get('/weixin/accesstoken','weixin\WxController@getAccessToken');
Route::get('/weixin/test','weixin\WxController@test');

//数据库测试
Route::get('/sql','weixin\WxController@sql');