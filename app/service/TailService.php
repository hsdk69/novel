<?php


namespace app\service;


use app\model\Tail;
use think\facade\Db;

class TailService
{
    public function getlist($num, $order = 'id')
    {
//        $n = Tail::select()->count();
//        if ($num > $n) {
//            $num = $n;
//        }
//        $random_number_array = range(0, $n);
//        shuffle($random_number_array );
//        $random_number_array = array_slice($random_number_array ,0,$num);
//        $tails = Tail::with('book.cate')->where('id','in',$random_number_array)->select();
        $data = Tail::with('book.cate')->order('id', 'desc')
            ->paginate([
                'list_rows' => $num,
                'query' => request()->param(),
            ]);
        $tails = $data->toArray();
        return [
            'tails' => $tails['data'],
            'page' => [
                'total' => $tails['total'],
                'per_page' => $tails['per_page'],
                'current_page' => $tails['current_page'],
                'last_page' => $tails['last_page'],
                'query' => request()->param()
            ]
        ];
    }
}