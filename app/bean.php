<?php

use Swoft\Db\Pool;
use Swoft\Http\Server\HttpServer;
use Swoft\Task\Swoole\TaskListener;
use Swoft\Task\Swoole\FinishListener;
use Swoft\Server\SwooleEvent;
use Swoft\Db\Database;
use Swoft\Redis\RedisDb;

return  [
    // 通用配置
    'config'  => [
        'env' => env('ENV', 'dev')
    ],

    // 日志配置项
    'noticeHandler'      => [
        'class'     => \App\Log\LogHandler::class,
        'logFile'   => '@runtime/log/' . date('Ymd') . '/notice.log',
        'formatter' => bean('lineFormatter'),
        'levels'    => 'notice,info,debug,trace',
    ],
    'applicationHandler' => [
        'class'     => \App\Log\LogHandler::class,
        'logFile'   => '@runtime/log/' . date('Ymd') . '/error.log',
        'formatter' => bean('lineFormatter'),
        'levels'    => 'error,warning',
    ],
    'logger'     => [
        'class'        => \App\Log\Logger::class,
        'flushRequest' => true,
        'enable'       => true,
        'handlers'     => [
            'application' => bean('applicationHandler'),
            'notice'      => bean('noticeHandler'),
        ],
    ],

    // http server配置项
    'httpServer' => [
        'class'    => HttpServer::class,
        'port'     => 80,
        'on'       => [
            SwooleEvent::TASK   => bean(TaskListener::class),  // Enable task must task and finish event
            SwooleEvent::FINISH => bean(FinishListener::class)
        ],
        /* @see HttpServer::$setting */
        'setting'  => [
            'task_worker_num'       => 1,
            'worker_num'            => 2,
            'task_enable_coroutine' => true,
            'reload_async'          => true,
            'log_file'              => alias(\App\Constant\Constant::LOG_FILE),
            'max_request'           => 10000,
            'dispatch_mode'         => 1,
            'package_max_length'    => 8 * 1024 * 1024,
            'buffer_output_size'    => 64 * 1024 * 1024,
            'max_coroutine'         => 50000,
            'heartbeat_idle_time'   => 600,
            'heartbeat_check_interval' => 60,
        ]
    ],
    'httpDispatcher' => [
        // Add global http middleware
        'middlewares' => [
            \App\Middleware\CorsMiddleware::class,
        ],
        'afterMiddlewares' => [
            \Swoft\Http\Server\Middleware\ValidatorMiddleware::class
        ]
    ],

    // 数据库连接池配置,更多配置项查看官方文档
    'db'         => [
        'class'    => Database::class,
        'dsn'      => config('db.dsn'),
        'username' => config('db.username'),
        'password' => config('db.password'),
    ],
    'db.pool'    => [
        'class'       => Pool::class,
        'database'    => bean('db'),
        'minActive'   => 10,
        'maxActive'   => 20,
        'maxWait'     => 0,
        'maxWaitTime' => 0,
        'maxIdleTime' => 60,
    ],

    // redis连接池配置,更多配置项查看官方文档
    'redis'      => [
        'class'         => RedisDb::class,
        'host'          => config('redis.host'),
        'port'          => config('redis.port'),
        'database'      => config('redis.database'),
        'option'   => [
            'prefix'      => '',
            'serializer' => Redis::SERIALIZER_PHP
        ],
    ],
    'redis.pool' => [
        'class'       => \Swoft\Redis\Pool::class,
        'redisDb'     => bean('redis'),
        'minActive'   => 10,
        'maxActive'   => 20,
        'maxWait'     => 0,
        'maxWaitTime' => 0,
        'maxIdleTime' => 60,
    ]
];
