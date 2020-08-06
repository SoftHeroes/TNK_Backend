<?php

// monetaryLog
Route::get('/monetaryLog', 'AdminPanel\LogController@getPoolLog')->name('vMonetaryLog');
// activityLog
Route::get('/activityLog', 'AdminPanel\LogController@getActivityLog')->name('vActivityLog');



