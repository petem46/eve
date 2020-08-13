<?php

use App\Http\Controllers\PagesController;
use App\Http\Controllers\UploadsController;
use Illuminate\Support\Facades\Route;

Auth::routes();

// Route::get('/home', 'HomeController@index')->name('home');
// Route::get('/', 'HomeController@index')->name('home');


  Route::post('/saveLatest', 'AppController@saveLatest');

  Route::get('/{any}', 'AppController@index')->where('any', '.*');
  Route::group(['middleware' => ['auth']], function () {
  });
