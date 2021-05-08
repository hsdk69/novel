<?php


namespace app\model;


use think\Model;

class Cate extends Model
{
    protected $pk = 'sortid';

    public function setCateNameAttr($value){
        return trim($value);
    }

    function getCates($order, $where, $num)
    {
        if ($num == 0) {
            $cates = Cate::order($order)->where($where)->select();
        } else {
            if (strlen($num) == 3) {
                $arr = explode(',',$num);
                $cates = Cate::where($where)
                    ->limit($arr[0],$arr[1])->order($order)->select();
            } else {
                $cates = Cate::order($order)->where($where)->limit($num)->select();
            }
        }
        return $cates;
    }
}