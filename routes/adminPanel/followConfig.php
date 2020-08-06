<?php
// followBetRule
Route::get('/followBetRule', 'AdminPanel\FollowConfigController@getFollowBetRule')->name('vFollowBetRule');
Route::post('/createFollowBetRule', 'AdminPanel\FollowConfigController@createFollowBetRule')->name('vCreateFollowBetRule');
Route::post('/updateFollowBetRule', 'AdminPanel\FollowConfigController@updateFollowBetRule')->name('vUpdateFollowBetRule');
Route::post('/deleteFollowBetRule', 'AdminPanel\FollowConfigController@deleteFollowBetRule')->name('vDeleteFollowBetRule');
Route::post('/restoreFollowBetRule', 'AdminPanel\FollowConfigController@restoreFollowBetRule')->name('vRestoreFollowBetRule');


// FollowBetSetup
Route::get('/followBetSetup', 'AdminPanel\FollowConfigController@getFollowBetSetup')->name('vFollowBetSetup');
Route::post('/createFollowBetSetup', 'AdminPanel\FollowConfigController@createFollowBetSetup')->name('vCreateFollowBetSetup');
Route::post('/updateFollowBetSetup', 'AdminPanel\FollowConfigController@updateFollowBetSetup')->name('vUpdateFollowBetSetup');
Route::post('/deleteFollowBetSetup', 'AdminPanel\FollowConfigController@deleteFollowBetSetup')->name('vDeleteFollowBetSetup');
Route::post('/restoreFollowBetSetup', 'AdminPanel\FollowConfigController@restoreFollowBetSetup')->name('vRestoreFollowBetSetup');