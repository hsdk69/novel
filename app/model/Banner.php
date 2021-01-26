<?php


namespace app\model;


use think\Model;

class Banner extends Model
{
    public function book()
    {
        return $this->hasOne(ArticleArticle::class, 'articleid', 'articleid');
    }

    public function setTitleAttr($value)
    {
        return trim($value);
    }
}