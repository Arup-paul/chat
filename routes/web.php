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

Route::get('/userList','MessageController@userList')->name('user.list');
Route::get('/userMessage/{id}','MessageController@userMessage')->name('user.message');
Route::get('/deleteSingleMessage/{id}','MessageController@deleteSingleMessage')->name('delete.single.message');
Route::get('/deleteAllMessage/{id}','MessageController@deleteAllMessage')->name('delete.all.message');
Route::post('/sendmessage','MessageController@sendMessage')->name('user.send.message');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
