<?php
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/saveSelectPortalProvider', 'AdminPanel\AdminController@saveSelectPortalProvider')->name('saveSelectPortalProvider');

Route::post('/saveIncludeDeletedSession', 'AdminPanel\AdminController@saveIncludeDeletedSession')->name('saveIncludeDeletedSession');

Route::post('/removeIncludeDeletedSession', 'AdminPanel\AdminController@removeIncludeDeletedSession')->name('removeIncludeDeletedSession');
//To register users to the admin table. We can use this in future for registering the users.
// Route::post('/registerAdmin', 'API\AuthController@registerAdmin');
