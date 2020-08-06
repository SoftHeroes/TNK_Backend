<?php

//Dashboard
Route::get('/dashboard', 'AdminPanel\DashboardController@loadDashboard')->name('vDashboard');


//Admin Logout
Route::get('/logout', 'AdminPanel\AdminController@adminLogout')->name('vLogout');

