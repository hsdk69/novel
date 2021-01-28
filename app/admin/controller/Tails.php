<?php


namespace app\admin\controller;


use app\model\ArticleArticle;
use app\model\Tail;

class Tails extends Base
{
    public function index()
    {
        return view();
    }

    public function list()
    {
        $page = intval(input('page'));
        $limit = intval(input('limit'));
        $data = Tail::order('id', 'desc');
        $count = $data->count();
        $tails = $data->limit(($page - 1) * $limit, $limit)->select();
        foreach ($tails as &$tail) {
            $tail['articlename'] = ArticleArticle::where('articleid', '=', $tail['articleid'])
                ->column('articlename');
        }
        return json([
            'code' => 0,
            'msg' => '',
            'count' => $count,
            'data' => $tails
        ]);
    }

    public function delete()
    {
        $uid = input('id');
        $result = Tail::destroy($uid);
        if ($result) {
            return json(['err' => '0', 'msg' => '删除成功']);
        } else {
            return json(['err' => '1', 'msg' => '删除失败']);
        }
    }
}