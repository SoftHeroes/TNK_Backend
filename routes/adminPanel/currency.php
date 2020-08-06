<?php
Route::get('/currency', 'AdminPanel\CurrencyController@getCurrency')->name('vCurrency');
Route::post('/createCurrency', 'AdminPanel\CurrencyController@createCurrency')->name('vCreateCurrency');
Route::post('/updateCurrency', 'AdminPanel\CurrencyController@updateCurrency')->name('vUpdateCurrency');
Route::post('/deleteCurrency', 'AdminPanel\CurrencyController@deleteCurrency')->name('vDeleteCurrency');
Route::post('/restoreCurrency', 'AdminPanel\CurrencyController@restoreCurrency')->name('vRestoreCurrency');