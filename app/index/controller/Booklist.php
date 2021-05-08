<?php


namespace app\index\controller;

use app\model\Cate;
use think\facade\View;

class Booklist extends Base
{
    public function index()
    {
        $cate_selector = -1;
        $words_selector = -1;
        $end_selector = -1;


        $cate = (int)input('sortid');
        $cate_model = Cate::where('sortid', $cate)->find();
        if ($cate == 0 || $cate == -1) {

        } else {
            $cate_selector = $cate;
        }
//        $arr = array();
//        if ($cate == 0 || $cate == '-1') {
//            foreach ($cates as $c) {
//                array_push($arr, $c['sortid']);
//            }
//        } else {
//            array_push($arr, $cate);
//            $cate_selector = $cate;
//        }

        $words = (int)input('words');
        if ($words == 0 || $words == '-1') {

        } else {
            $words_selector = $words;
        }
        $fullflag = (int)input('fullflag');
        if ($fullflag == 0 || $fullflag == -1) {

        } else {
            $end_selector = $fullflag;
        }

        View::assign([
            'cate_selector' => $cate_selector,
            'words_selector' => $words_selector,
            'end_selector' => $end_selector,
            'cate' => $cate_model
        ]);
        return view($this->tpl);
    }
}