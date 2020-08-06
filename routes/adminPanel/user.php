<?php

//userDetails
Route::get('/userDetails', 'AdminPanel\UserController@getUserDetails')->name('vUserDetails');
// Route::post('addUserDetails','AdminPanel\UserController@addUserDetails')->name('AddUserDetails');
// Route::post('updateUserDetails','AdminPanel\UserController@updateUserDetails')->name('UpdateUserDetails');
// Route::post('deleteUserDetails','AdminPanel\UserController@deleteUserDetails')->name('DeleteUserDetails');

//user Profile
Route::get('/userProfile/{userUUID}/get', 'AdminPanel\UserController@getUserProfile')->name('vUserProfile');

//User online history
Route::get('/userOnlineHistory', 'AdminPanel\UserController@getUserOnlineHistory')->name('vUserOnlineHistory');