<?php


namespace app\admin\controller;


use app\model\Cate;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\facade\View;

class Cates extends Base
{
    public function index()
    {
        return view();
    }

    public function list()
    {
        $page = intval(input('page'));
        $limit = intval(input('limit'));
        $data = Cate::order('sortid', 'desc');
        $count = $data->count();
        $cates = $data->limit(($page - 1) * $limit, $limit)->select();
        return json([
            'code' => 0,
            'msg' => '',
            'count' => $count,
            'data' => $cates
        ]);
    }

    public function create()
    {
        if (request()->isPost()) {
            $aname = trim(input('cate_name'));
            try {
                $cate = Cate::where('cate_name', '=', $aname)->findOrFail();
                return json(['err' => 1, 'msg' => '已经存在相同题材']);
            } catch (ModelNotFoundException $e) {
                $cate = new Cate();
                $cate->cate_name = $aname;
                $result = $cate->save();
                if ($result) {
                    return json(['err' => 0, 'msg' => '添加成功']);
                } else {
                    return json(['err' => 1, 'msg' => '添加失败']);
                }
            }
        }
        return view();
    }

    public function edit()
    {
        $id = input('sortid');
        try {
            $cate = Cate::findOrFail($id);
            if (request()->isPost()) {
                $cate->cate_name = trim(input('cate_name'));
                $result = $cate->save();
                if ($result) {
                    return json(['err' => 0, 'msg' => '修改成功']);
                } else {
                    return json(['err' => 1, 'msg' => '修改失败']);
                }
            }
            View::assign([
                'cate' => $cate,
            ]);
            return view();
        } catch (ModelNotFoundException $e) {
            return json(['err' => 1, 'msg' => $e->getMessage()]);
        }
    }

    public function search() {
        $name = input('cate_name');
        $where = [
            ['cate_name', 'like', '%' . $name . '%']
        ];
        $page = intval(input('page'));
        $limit = intval(input('limit'));
        $data = Cate::where($where)->order('sortid', 'desc');
        $count = $data->count();
        $cates = $data->limit(($page - 1) * $limit, $limit)->select();
        return json([
            'code' => 0,
            'msg' => '',
            'count' => $count,
            'data' => $cates
        ]);
    }

    public function delete()
    {
        $id = input('sortid');
        $result = Cate::destroy($id);
        if ($result) {
            return json(['err' => '0', 'msg' => '删除成功']);
        } else {
            return json(['err' => '1', 'msg' => '删除失败']);
        }
    }
}