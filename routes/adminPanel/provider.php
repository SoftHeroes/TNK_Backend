<?php

// ProviderGameSetup
Route::get('/providerGameSetup', 'AdminPanel\ProviderGameSetupController@getProviderGameSetup')->name('vProviderGameSetup');
Route::post('/portalProviderSelect', 'AdminPanel\ProviderGameSetupController@portalProviderSelect')->name('PortalProviderSelect');
Route::post('/providerGameSetup', 'AdminPanel\ProviderGameSetupController@selectProviderGameSetup')->name('ProviderGameSetup');
Route::post('/updateProviderPayout', 'AdminPanel\ProviderGameSetupController@updateProviderPayout')->name('updateProviderPayout');
Route::post('/creditRequest', 'AdminPanel\ProviderGameSetupController@creditRequest')->name('vCreditRequest');
Route::post('/creditRequestManagement', 'AdminPanel\ProviderGameSetupController@creditRequestManagementUpDate')->name('CreditRequestManagement');

// provider
Route::get('/providerRequestBalance', 'AdminPanel\ProviderGameSetupController@getProviderRequestBalance')->name('vProviderRequestBalance');
Route::get('/providerBalance', 'AdminPanel\ProviderGameSetupController@getProviderBalance')->name('vProviderBalance');
Route::post('/updateProviderInfo', 'AdminPanel\ProviderGameSetupController@updateProviderInfo')->name('vUpdateProviderInfo');
Route::get('/providerInfo', 'AdminPanel\ProviderGameSetupController@providerInfo')->name('vProviderInfo');
Route::get('/providerList', 'AdminPanel\ProviderGameSetupController@getProviderList')->name('vProviderList');

Route::post('/addProviderList', 'AdminPanel\ProviderGameSetupController@addProviderList')->name('vAddProviderList');
Route::post('/updateProviderList', 'AdminPanel\ProviderGameSetupController@updateProviderList')->name('vUpdateProviderList');
Route::post('/providerLogoutUser', 'AdminPanel\ProviderGameSetupController@providerLogoutAllUser')->name('ProviderLogoutAllUser');


//new

Route::get('/providerSelect', 'AdminPanel\ProviderGameSetupController@getProviderSelect')->name('ProviderSelect');

// Provider Config
Route::get('/providerConfig', 'AdminPanel\ProviderGameSetupController@getProviderConfig')->name('vProviderConfig');
Route::post('/addProviderConfig', 'AdminPanel\ProviderGameSetupController@addProviderConfig')->name('vAddProviderConfig');
Route::post('/updateProviderConfig', 'AdminPanel\ProviderGameSetupController@updateProviderConfig')->name('vUpdateProviderConfig');
Route::post('/deleteProviderConfig', 'AdminPanel\ProviderGameSetupController@deleteProviderConfig')->name('vDeleteProviderConfig');
Route::post('/restoreProviderConfig', 'AdminPanel\ProviderGameSetupController@restoreProviderConfig')->name('vRestoreProviderConfig');



