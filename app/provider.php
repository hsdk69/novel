<?php
use app\ExceptionHandle;
use app\Request;

// 容器Provider定义文件
return [
    'think\Request'          => Request::class,
    'think\exception\Handle' => ExceptionHandle::class,
    'redis' => \think\cache\driver\Redis::class,
    'bookService' => \app\service\BookService::class,
    'chapterService' => \app\service\ChapterService::class,
    'tailService' => \app\service\TailService::class,
    'booklogService' => \app\service\BookLogService::class,
    'httpclient' => \GuzzleHttp\Client::class
];
