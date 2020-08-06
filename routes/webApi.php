<?php

/*
|--------------------------------------------------------------------------
| Web related API Routes
|--------------------------------------------------------------------------
|
 */

// Route::post('/registerAdmin', 'API\AuthController@registerAdmin');

//User
Route::post('/appUsersLogin', 'API\UserController@loginAppUsers'); //To login if user already exist or create a user
Route::post('/appUsersLogout', 'API\UserController@logoutAppUsers'); //To logout the user and add his remaining balance to the portal provider's main balance
Route::post('/webUserLogin', 'API\UserController@loginWebUser'); //Web user login and provider verification.
Route::post('/getUserProfile', 'API\UserController@getUserProfile'); //getting user profile by userUUID.
Route::post('/updateUserProfile', 'API\UserController@updateUserProfile'); //updating user profile by userUUID.
Route::post('/updateUserSetting', 'API\UserController@updateUserSetting'); //updating user profile by userUUID.
Route::post('/followUser', 'API\UserController@FollowUser'); // Add Follow User.
Route::post('/followUserList', 'API\UserController@FollowUserList'); // List User Follow .

//Betting
Route::post('/storeBet', 'API\BettingController@storeBet'); //Placing bets
Route::post('/getAllBets', 'API\BettingController@getAllBets'); //Bet History user based and provider based
Route::post('/liveBetCount', 'API\BettingController@liveBetCount'); // get live bet data by Rules
Route::post('/liveCountBetData', 'API\BettingController@liveCountBetData'); // get live bet data by Games

//Rule
Route::post('/getAllRules', 'API\RuleController@getAllRules'); //Getting Rule List

//Game
Route::post('/getGames', 'API\GameController@getGames'); // Getting games by providerUUID

//Notification
Route::post('/addNotification', 'API\NotificationController@addNotification'); //Add new Notification
Route::post('/getNotification', 'API\NotificationController@getNotification'); //get all Notifications

// PortalProvider
Route::post('/getPortalProviders', 'API\PortalProviderController@getPortalProviders');
Route::post('/getPortalProviderConfig', 'API\PortalProviderController@getPortalProviderConfig');

// stock
Route::post('/getStock', 'API\StockController@Stock');
Route::post('/getAllStock', 'API\StockController@getAllStock');
Route::post('/getActiveGamesByCategory', 'API\StockController@getActiveGamesByCategory');

// Road map
Route::post('/getRoadMap', 'API\RoadMapsController@getRoadMap');

// country list
Route::post('/getCountryList', 'API\CountryController@getCountryList');

// messages chat
Route::post('/messages', 'API\ChatController@index');
Route::post('/messages/send', 'API\ChatController@store');

Route::post('/sendInvitation', 'API\UserController@sendInvitation'); // to set invitation to chat
Route::post('/getUserInvitationDetail', 'API\UserController@getUserInvitationDetail'); // get invitation details


// leader board API
Route::post('/getLeaderBoard', 'API\UserController@getLeaderBoard'); // to get leader board

// get User Bet Analysis
Route::post('/getUserBetAnalysis', 'API\UserController@getUserBetAnalysis'); // to get user bet analysis

// Visit User Profile API's
Route::post('/visitUserProfile', 'API\UserController@visitUserProfile'); // Visit User Profile API's

// Likes - Send like to user.
Route::post('/likes', 'API\UserController@sendLike');
