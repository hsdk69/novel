<?php


namespace app\index\controller;


use app\model\ArticleChapter;
use think\facade\Db;
use think\facade\View;

class Rank extends Base
{
    public function index()
    {
        $op = input('op');
        if (is_null($op) || empty($op)) $op = 'new';
        View::assign([
            'op' => $op
        ]);
        return view($this->tpl);
    }
}