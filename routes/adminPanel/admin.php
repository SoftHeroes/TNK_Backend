<?php
//--------------- Admin Start
//Login
Route::get('/login', function () {
    return view('adminPanel.login');
})->name('vLogin');

Route::post('/adminLogin', 'AdminPanel\AdminController@adminLogin')->name('vAdminLogin');

//Logout -- in dashboard route as we need Auth middleware included on it


//Forget Password
Route::post('/forgetPassword', 'AdminPanel\AdminController@forgetPassword')->name('vForgetPassword');

Route::post('/otpCheck', 'AdminPanel\AdminController@otpCheck')->name('vOtpCheck');

Route::post('/resetPassword', 'AdminPanel\AdminController@resetPassword')->name('vResetPassword');

//Edit Profile


//--------------- Admin End
