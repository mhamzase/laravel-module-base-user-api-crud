<?php

Route::get('/login/{provider}', 'AuthController@redirectToProvider');
Route::get('/login/{provider}/callback', 'AuthController@handleProviderCallback');
