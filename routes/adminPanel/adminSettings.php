<?php
// Change Password
Route::get('/changePassword', function () {
    return view('adminPanel.changePassword');
})->name('vChangePassword');

//Edit Profile

Route::get('/getProfile', 'AdminPanel\AdminController@getProfile')->name('vGetProfile');

Route::post('/getInfoProfiles', 'AdminPanel\AdminController@getProfile')->name('vInfoProfiles');

Route::post('/updateProfile', 'AdminPanel\AdminController@updateProfile')->name('vUpdateProfile');

Route::post('/changePassword', 'AdminPanel\AdminController@changePassword')->name('changePassword');