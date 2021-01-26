<?php


namespace app\model;


use think\Model;

class Comments extends Model
{
    public function book(){
        return $this->belongsTo(ArticleArticle::class, 'articleid', 'articleid');
    }

    public function user(){
        return $this->belongsTo(SystemUsers::class, 'uid', 'uid');
    }
}