<?php


namespace app\model;


use think\Model;

class ArticleChapter extends Model
{
    protected $pk = 'chapterid';

    public function book()
    {
        return $this->belongsTo(ArticleArticle::class, 'articleid', 'articleid');
    }

    public function setChapternameAttr($value)
    {
        return trim($value);
    }
}