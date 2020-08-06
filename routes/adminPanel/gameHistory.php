<?php

//getAllGames
Route::get('/gameHistory', 'AdminPanel\GameController@getAllGames')->name('vGameHistory');
Route::get('/gameHistory/getGameDetail/{gameUUID}', 'AdminPanel\GameController@getGameDetail');
