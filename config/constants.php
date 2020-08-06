<?php

/* Constant message to display in production environments. */

return [
    'default_error_response' => 'Oops... Something Went Wrong!',

    'alert_mail_id' => env('ALERT_MAIL_ID', null),

    'mail_from_address' => env('MAIL_FROM_ADDRESS', null),

    'create_game_odds_chunk' => env('CREATE_GAME_ODDS_CHUNK', 300),

    'create_game_chunk' => env('CREATE_GAME_CHUNK', 3000),

    'socket_live_game_time_sec' => env('SOCKET_LIVE_GAME_TIME_SEC', 10),

    'socket_live_stock_data_time_sec' => env('SOCKET_LIVE_STOCK_DATA_TIME_SEC', 1),

    'image_file_type_accept' => env('IMAGE_FILE_TYPE_ACCEPT'),

    'portal_provider_admin_image_path' => 'admin/portalProvider/',

    'web_app_link' => env('WEB_APP_LINK'),

    'image_path_user' => env('IMAGE_PATH_USER'),

    'image_path_admin' => env('IMAGE_PATH_ADMIN'),

    'image_path_avatar' => env('IMAGE_PATH_AVATAR'),

    'integer_max_length' => env('INTEGER_MAX_LENGTH',11),

    'double_max_length' => env('DOUBLE_MAX_LENGTH',11),

    'string_max_length' => env('STRING_MAX_LENGTH',255)

];
