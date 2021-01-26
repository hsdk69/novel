<?php
use app\ExceptionHandle;
use app\Request;

// 容器Provider定义文件
return [
    'think\Request'          => Request::class,
    'think\exception\Handle' => ExceptionHandle::class,
    'redis' => \think\cache\driver\Redis::class,
    'authorService' => \app\service\AuthorService::class,
    'bookService' => \app\service\BookService::class,
    'chapterService' => \app\service\ChapterService::class,
    'promotionService' => \app\service\PromotionService::class,
    'tagsService' => \app\service\TagsService::class,
    'userService' => \app\service\UserService::class,
    'booklogService' => \app\service\BookLogService::class
];
