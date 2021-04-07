<?php


namespace app\service;

use app\model\ArticleChapter;
use think\facade\App;

class ChapterService
{

    public function getLastChapter($book_id)
    {
        return ArticleChapter::where('book_id', '=', $book_id)->order('chapter_order', 'desc')->limit(1)->find();
    }
}