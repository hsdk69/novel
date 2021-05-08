<?php
use app\ExceptionHandle;
use app\Request;

// 容器Provider定义文件
return [
    'think\Request'          => Request::class,
    'think\exception\Handle' => ExceptionHandle::class,
    'redis' => \think\cache\driver\Redis::class,
    'bookModel' => \app\model\ArticleArticle::class,
    'chapterModel' => \app\model\ArticleChapter::class,
    'httpclient' => \GuzzleHttp\Client::class,
    'userModel' => \app\model\SystemUsers::class,
    'cateModel' => \app\model\Cate::class,
    'bannerModel' => \app\model\Banner::class,
    'commentModel' => \app\model\Comments::class,
    'favorModel' => \app\model\UserFavor::class
];
