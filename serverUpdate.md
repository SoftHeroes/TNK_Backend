<p align="center"><img src="readme/img/logo.png" width="400"></p>

## Server Update Guide

- Turn down server with below command
    
        php artisan down

- Login to supervisor and stop all process (*required login credential*)

        <serverLink>/supervisor

- check for pending Jobs in DB by bellow query (*If found any start* **queueWork** *job*)

        SELECT COUNT(1) FROM jobs WHERE available_at <= UNIX_TIMESTAMP(CURRENT_TIMESTAMP());

- Now, check for failed jobs in DB by bellow query 

        SELECT COUNT(1) FROM failed_jobs;

- If found any jobs in error. move them to job table by bellow query.\
**Note**: check jobs moved successfully and Commit or rollback based on query results

        START TRANSACTION;
        INSERT INTO jobs(
            payload,
            queue,
            attempts,
            reserved_at,
            available_at,
            created_at
            )
        SELECT 
            payload,
            queue,
            0,
            NULL,
            UNIX_TIMESTAMP(CURRENT_TIMESTAMP()),
            UNIX_TIMESTAMP(CURRENT_TIMESTAMP()) 
        FROM failed_jobs;

        DELETE FROM failed_jobs;
        -- COMMIT;
        -- ROLLBACK;
        
- take updated code by bellow command (*use git credential*)
    
        sudo git pull

- Update all package by bellow command
    
        sudo composer update

- check .env.example for new variables and update new variables in .env

- Now, If your upgrading production.Set APP_ENV=local.

- Update config cache,So new variables take effect
    
        php artisan config:cache

- Update Database by bellow command
    
        php artisan migrate

- Now, If your upgrading production.Set back APP_ENV=production.

- Update Api Docs by bellow command
    
        sudo php artisan apidoc:generate

- Restart supervisor
    
        service supervisor restart

- turn server up
    
        php artisan up