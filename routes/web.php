<?php

require_once app_path() . '/Helpers/CommonUtility.php';

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */
//language reset
Route::get('/set-language/{lang}', 'LocalizationController@set')->name('set.language');

// Route::get('/documentation/{format?}', function ($format = null) {

//     if($format == '.json'){
//         $path = storage_path().'/app/apidoc/collection.json';
//         return Response::download($path);
//     }

//     return view('apidoc.index');
// })->name('apidoc.json');

Route::get('/updateSession', 'AdminPanel\AdminController@updateSession')->name('updateSession');

Route::get('/getCookie/{tag}', function ($tag) {
    if (IsAuthEnv()) {
        return abort(404);
    }

    return request()->cookie($tag);
});

Route::get('/getSession', function () {
    if (IsAuthEnv()) {
        return abort(404);
    }

    return session(str_replace(".", "_", request()->ip()) . 'ECGames');
});

Route::get('betHistory', ['uses' => 'AdminPanel\BetHistoryController@betHistory', 'as' => 'betHistory']);
Route::get('gameHistory', ['uses' => 'AdminPanel\GameController@getAllGames', 'as' => 'gameHistory']);
