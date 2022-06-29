<?php

Route::patch('users/change-password',       'UserController@changePassword');

Route::resources([ 'users'       => 'UserController']);
Route::resources([ 'roles'       => 'RoleController']);
Route::resources([ 'permissions' => 'PermissionController']);

Route::get('auth',       'AuthController@getAuthUser')->name('auth.getAuthUser');
Route::patch('profile',  'AuthController@updateProfile')->name('auth.getAuthUser');
Route::get('logout',     'AuthController@logout')->name('auth.logout');
