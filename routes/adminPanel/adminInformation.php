<?php
Route::get('/getAdminInformation', 'AdminPanel\AdminInformationController@getAdminInformation')->name('vGetAdminInformation');

Route::post('/addAdminInformation', 'AdminPanel\AdminInformationController@addAdminInformation')->name('vAddAdminInformation');
Route::post('/updateAdminInformation', 'AdminPanel\AdminInformationController@updateAdminInformation')->name('vUpdateAdminInformation');
Route::post('/deleteAdminInformation', 'AdminPanel\AdminInformationController@deleteAdminInformation')->name('vDeleteAdmin');
Route::post('/changePasswordAdminInformation', 'AdminPanel\AdminInformationController@changePasswordAdminInformation')->name('vChangePasswordAdminInformation');
Route::post('/restoreAdminInformation', 'AdminPanel\AdminInformationController@restoreAdminInformation')->name('vRestoreAdminInformation');
?>