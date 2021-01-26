<?php


namespace app\model;


use think\Model;

class Cate extends Model
{
    protected $pk = 'typeid';

    public function setCateNameAttr($value){
        return trim($value);
    }
}