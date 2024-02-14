<?php

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', 3306),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
        'mysql2' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST_2', 'localhost'),
            'port' => env('DB_PORT_2', 3306),
            'database' => env('DB_DATABASE_2'),
            'username' => env('DB_USERNAME_2'),
            'password' => env('DB_PASSWORD_2'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ],
    ],
];