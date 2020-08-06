<?php
Route::get('/accessPolicy', 'AdminPanel\AccessPolicyController@getAccessPolicy')->name('vAccessPolicy');
Route::post('/updateIsAllowAll', 'AdminPanel\AccessPolicyController@updateAccessPolicy')->name('vUpdateAccessPolicy');
Route::post('/createAccessPolicy', 'AdminPanel\AccessPolicyController@createAccessPolicy')->name('vCreateAccessPolicy');
Route::post('/deleteAccessPolicy', 'AdminPanel\AccessPolicyController@deleteAccessPolicy')->name('vDeleteAccessPolicy');
Route::post('/restoreAccessPolicy', 'AdminPanel\AccessPolicyController@restoreAccessPolicy')->name('vRestoreAccessPolicy');