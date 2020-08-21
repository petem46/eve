<?php

use App\Http\Controllers\PagesController;
use App\Http\Controllers\UploadsController;
use Illuminate\Support\Facades\Route;

Auth::routes();

// Route::get('/home', 'HomeController@index')->name('home');
// Route::get('/', 'HomeController@index')->name('home');

Route::get('/getAlliances', 'AllianceController@getAlliances');
Route::post('/emptyAlliancesTable', 'AllianceController@emptyAlliancesTable');
Route::post('/saveAllianceData', 'AllianceController@saveAllianceData');

Route::get('/getSystems', 'SystemController@getSystems');
Route::post('/emptySystemsTable', 'SystemController@emptySystemsTable');
Route::post('/saveSystemData', 'SystemController@saveSystemData');

Route::post('/saveLatest', 'AppController@saveLatest');

Route::get('/{any}', 'AppController@index')->where('any', '.*');

Route::group(['middleware' => ['auth']], function () {
});
