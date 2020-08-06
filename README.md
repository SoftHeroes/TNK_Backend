<p align="center"><img src="readme/img/logo.png" width="400"></p>

## About EC Gaming version_29.05.2020

EC Gaming is Casino stock base game.

## Setup Guide
Clone/checkout from [repo](https://github.com/tnklaos/stockadmin_new)

- Install php [composer](https://getcomposer.org/) dependency management <br />
    - [windows](https://getcomposer.org/doc/00-intro.md#installation-windows)<br />
    - [Linux / Unix / macOS](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos)<br />

- Run Composer command everything you change branch or pull from server
    
        composer update

- Create .env similar to .env.example  **Don't delete this file make copy of this file**

- Set variables name in .env : <br/>
    >DB_DATABASE={DBName}<br/>
    >DB_DATABASE={DBUser}<br/>
    >DB_PASSWORD={DBUserPassword}<br/>
    >APP_TIMEZONE={TimeZone}<br/>

- Then generate API key by running bellow command in project directory.
    
        php artisan key:generate
        
- Run bellow command to cache config file
    
        php artisan config:cache

- Populate DB and all default value by bellow command
    
        php artisan migrate

- Run bellow command to list out all available command present in system
    
        php artisan

- All process related scripts are present in **scripts/SupervisorConf** directory

- Refresh remote branch 

        git remote update origin --prune

- Delete all local branches

        git branch -D `git branch --merged | grep -v \* | xargs`

- Log DB Query Use this

        DB::connection()->enableQueryLog();
        $queries = DB::getQueryLog();

- Generate API Doc

        php artisan apidoc:generate

- Run bellow command for updating/refresh auto load generate file 

        composer dump-autoload

Available Validation [Rules](https://laravel.com/docs/5.7/validation#available-validation-rules)

## Important!!
## check every time when trying to create folder if already exist or not
- by using </br>
        
        file_exist($dir); 
- or </br>
        
        File::isDirectory($dir);

## When trying to create folder ( ON SERVER )
- When using mkdir </br>
- don't do </br>

        mkdir($dir,666,true);

- avoid permission please do </br>

        mkdir($dir,0666,true);
    
    Put 0 (zero) on the first start when giving permission

## Giving Permission On Server

    sudo chown -R www-data:www-data /var/www/html/StockAdmin/public/

    sudo chown -R www-data:www-data /var/www/html/StockAdmin/public/images/*


## Optional

-   By default camelCase is not enabled by phpMyAdmin, to enable that below line need to be added into my.ini file into your mysql server.
-   Path: /mysql/bin/my.ini (Tested on XAMPP).
-   Add this line `lower_case_table_names = 2`.
-   Refresh database by `php artisan migrate:refresh`.


