<?php

Route::resource('terms','TermController');
Route::resource('translations','TranslationController');
Route::get('trans/{language}/{category}','TranslationController@getTrans');

Route::get('terms/{term}/children', 'TermController@children');

Route::get('module-info/{module?}', 'ModuleController@info' );
