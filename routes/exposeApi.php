<?php

/*
|--------------------------------------------------------------------------
| All Exposed Api goes here
|--------------------------------------------------------------------------
|
*/

Route::post('/getUserBalance', 'API\UserController@getUserBalance'); //to get user balance 

Route::post('/updateUserBalance', 'API\UserController@updateUserBalance'); //to update user balance 

Route::post('/logoutAndClearPool', 'API\UserController@logoutAndClearPool'); //to logout user and clear pool

Route::post('/getAllBets', 'API\BettingController@getAllBetsByPortalProviderUserID'); //to logout user and clear pool
