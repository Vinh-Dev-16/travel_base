<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Firebase Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Firebase settings. You will need to download
    | your service account JSON file from Firebase Console and place it in
    | the storage directory.
    |
    */

    'firebase' => [
        'credential_path' => function_exists('storage_path') 
            ? storage_path('app/firebase-service-account.json')
            : getcwd() . '/storage/app/firebase-service-account.json',
        'database_uri' => function_exists('env') 
            ? env('FIREBASE_DATABASE_URI', 'https://your-project-id-default-rtdb.firebaseio.com/')
            : 'https://your-project-id-default-rtdb.firebaseio.com/',
    ],

    /*
    |--------------------------------------------------------------------------
    | MongoDB Configuration
    |--------------------------------------------------------------------------
    |
    | MongoDB connection settings for your application.
    |
    */

    'mongodb' => [
        'connection' => function_exists('env') 
            ? env('MONGODB_CONNECTION', 'mongodb://localhost:27017')
            : 'mongodb://localhost:27017',
        'database' => function_exists('env') 
            ? env('MONGODB_DATABASE', 'travel_db')
            : 'travel_db',
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Configuration
    |--------------------------------------------------------------------------
    |
    | Redis connection settings for caching and sessions.
    |
    */

    'redis' => [
        'host' => function_exists('env') 
            ? env('REDIS_HOST', '127.0.0.1')
            : '127.0.0.1',
        'port' => function_exists('env') 
            ? env('REDIS_PORT', 6379)
            : 6379,
        'password' => function_exists('env') 
            ? env('REDIS_PASSWORD', null)
            : null,
        'database' => function_exists('env') 
            ? env('REDIS_DATABASE', 0)
            : 0,
    ],
];
