<?php
Route::get('/notification', 'AdminPanel\NotificationController@getNotification')->name('vNotification');
Route::post('/createNotification', 'AdminPanel\NotificationController@createNotification')->name('vCreateNotification');
Route::post('/getUpdateNotification', 'AdminPanel\NotificationController@getUpdateNotification')->name('getUpdateNotification');
Route::post('/updateNotification', 'AdminPanel\NotificationController@updateNotification')->name('vUpdateNotification');
Route::post('/deleteNotification', 'AdminPanel\NotificationController@deleteNotification')->name('vDeleteNotification');
Route::post('/restoreNotification', 'AdminPanel\NotificationController@restoreNotification')->name('vRestoreNotification');
