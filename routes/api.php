<?php

use Illuminate\Http\Request;

//login & register
Route::post('register', 'UserController@register');
Route::post('login', 'UserController@login');
//api

Route::middleware(['jwt.verify'])->group(function () {

    Route::post('project', 'ProjectController@store');
    Route::get('project', 'ProjectController@getAll');
    Route::get('project/{id}', 'ProjectController@show');
    Route::put('project/{id}', 'ProjectController@update');
    Route::delete('project/{id}', 'ProjectController@destroy');
    Route::post('/project/search', 'ProjectController@getSearchResults');
    

    Route::post('services', 'ServicesController@tambah');
    Route::get('/services', "ServicesController@getAll");
    Route::get('/services/{id}', "ServicesController@show");
    Route::put('/services/{id}', "ServicesController@update");
    Route::delete('services/{id}', 'ServicesController@destroy');

    Route::post('/req/{id}', 'ReqController@index');
   
});
