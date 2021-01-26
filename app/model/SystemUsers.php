<?php


namespace app\model;


use think\Model;

class SystemUsers extends Model
{
    protected $pk = 'uid';

    public function setUnameAttr($value)
    {
        return trim($value);
    }

    public function setSaltAttr($value)
    {
        return trim($value);
    }

    public function setEmailAttr($value)
    {
        return trim($value);
    }
}