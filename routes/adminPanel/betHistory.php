<?php

//BetHistory
Route::get('/betHistory', 'AdminPanel\BetHistoryController@betHistory')->name('vBetHistory');
Route::get('/betHistory/getSingleGameData/{gameUUID}', 'AdminPanel\BetHistoryController@getSingleGameData');
