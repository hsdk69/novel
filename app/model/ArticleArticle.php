<?php


namespace app\model;

use think\model\concern\SoftDelete;
use think\Model;

class ArticleArticle extends Model
{
    protected $pk = 'articleid';

    public static function onBeforeUpdate($book)
    {
        cache('book:' . $book->articleid, null);
        cache('bookInCate:' . $book->articleid, null);
    }

    public static function onAfterInsert($user)
    {
        cache('newestHomepage', null);
        cache('endsHomepage', null);
    }

    public function chapters()
    {
        return $this->hasMany(ArticleChapter::class, 'articleid', 'articleid');
    }

    public function cate()
    {
        return $this->hasOne(Cate::class, 'typeid', 'typeid');
    }

    public function setArticleNameAttr($value)
    {
        return trim($value);
    }
}