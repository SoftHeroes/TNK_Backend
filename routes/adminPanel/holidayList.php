<?php
// Route::get('/holidayList', function () {
//     return view('adminPanel.holidayList');
// })->name('vHolidayList');
Route::get('/holidayList', 'AdminPanel\HolidayListController@getStockByEventColor')->name('vHolidayList');
Route::get('/holidayLists', 'AdminPanel\HolidayListController@getHolidayList');
Route::post('/createHolidayList', 'AdminPanel\HolidayListController@createHolidayList');
Route::post('/updateHolidayList', 'AdminPanel\HolidayListController@updateHolidayList');
Route::post('/deleteHolidayList', 'AdminPanel\HolidayListController@deleteHolidayList');