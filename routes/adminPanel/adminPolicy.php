<?php
Route::get('/adminPolicy', 'AdminPanel\AdminPolicyController@getAdminPolicy')->name('vAdminPolicy');
Route::post('/adminPolicy', 'AdminPanel\AdminPolicyController@addAdminPolicy')->name('AddAdminPolicy');
Route::post('/updateAdminPolicy', 'AdminPanel\AdminPolicyController@updateAdminPolicy')->name('UpdateAdminPolicy');
Route::post('/deleteAdminPolicy', 'AdminPanel\AdminPolicyController@deleteAdminPolicy')->name('DeleteAdminPolicy');
Route::post('/restoreAdminPolicy', 'AdminPanel\AdminPolicyController@restoreAdminPolicy')->name('RestoreAdminPolicy');