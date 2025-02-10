<?php

use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default'  => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
'stack' => [
    'driver' => 'stack',
    'channels' => ['daily'],  // Change 'null' to 'daily'
],

        'single'     => [
            'driver' => 'single',
            'path'   => storage_path('logs/laravel.log'),
            'level'  => 'debug',
        ],

        'daily'      => [
            'driver'         => 'monolog',
            'handler'        => Monolog\Handler\RotatingFileHandler::class,
            'handler_with'   => [
                'filename'       => storage_path('logs/laravel.log'),
                'maxFiles'       => 7,    // Same as your current 'days' => 7
                'filePermission' => 0664, // Add this line
                'useLocking'     => true, // Add this line
            ],
            'formatter'      => Monolog\Formatter\LineFormatter::class,
            'formatter_with' => [
                'format'     => "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
                'dateFormat' => "Y-m-d H:i:s",
            ],
            'level'          => 'debug',
        ],

        'slack'      => [
            'driver'   => 'slack',
            'url'      => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji'    => ':boom:',
            'level'    => 'critical',
        ],

        'papertrail' => [
            'driver'       => 'monolog',
            'level'        => 'debug',
            'handler'      => SyslogUdpHandler::class,
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
            ],
        ],

        'stderr'     => [
            'driver'  => 'monolog',
            'handler' => StreamHandler::class,
            'with'    => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog'     => [
            'driver' => 'syslog',
            'level'  => 'debug',
        ],

        'errorlog'   => [
            'driver' => 'errorlog',
            'level'  => 'debug',
        ],
    ],

];
