<?php
Route::get('/invitationSetup', 'AdminPanel\InvitationSetupController@getInvitationSetup')->name('vInvitationSetup');
Route::post('/createInvitationSetup', 'AdminPanel\InvitationSetupController@createInvitationSetup')->name('vCreateInvitationSetup');
Route::post('/updateInvitationSetup', 'AdminPanel\InvitationSetupController@updateInvitationSetup')->name('vUpdateInvitationSetup');
Route::post('/deleteInvitationSetup', 'AdminPanel\InvitationSetupController@deleteInvitationSetup')->name('vDeleteInvitationSetup');
Route::post('/restoreInvitationSetup', 'AdminPanel\InvitationSetupController@restoreInvitationSetup')->name('vRestoreInvitationSetup');