<?php


namespace app\service;


use app\model\Tail;
use think\facade\Db;

class TailService
{
    public function getlist($num, $end_point, $order = 'id')
    {
        $n = Tail::select()->count();
        if ($num > $n) {
            $num = $n;
        }
        $random_number_array = range(0, $n);
        shuffle($random_number_array );
        $random_number_array = array_slice($random_number_array ,0,$num);
        $tails = Tail::with('book.cate')->where('id','in',$random_number_array)->select();

        return $tails;
    }
}