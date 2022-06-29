<?php

Route::post('/auth',            'AuthController@authenticate');
Route::post('/register',        'RegisterController@register');
Route::post('sellers/register', 'RegisterController@register');
