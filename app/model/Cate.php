<?php


namespace app\model;


use think\Model;

class Cate extends Model
{
    protected $pk = 'sortid';

    public function setCateNameAttr($value){
        return trim($value);
    }
}