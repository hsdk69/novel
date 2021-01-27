<?php


namespace app\admin\controller;

use think\db\exception\ModelNotFoundException;
use think\facade\View;
use app\model\SystemUsers;

class Admins extends Base
{
    public function index()
    {
        return view();
    }

    public function list()
    {
        $page = intval(input('page'));
        $limit = intval(input('limit'));
        $data = SystemUsers::where('groupid', '=', 2);
        $count = $data->count();
        $admins = $data->order('uid', 'desc')
            ->limit($page - 1, $limit)->select();
        return json([
            'code' => 0,
            'msg' => '',
            'count' => $count,
            'data' => $admins
        ]);
    }

    public function search() {
        $uname = input('uname');
        $where[] = ['groupid', '=', 2];
        $where[] = ['uname', 'like', '%' . $uname . '%'];
        $page = intval(input('page'));
        $limit = intval(input('limit'));
        $data = SystemUsers::where($where);
        $count = $data->count();
        $admins = $data->order('uid', 'desc')
            ->limit($page - 1, $limit)->select();
        return json([
            'code' => 0,
            'msg' => '',
            'count' => $count,
            'data' => $admins
        ]);
    }

    public function create()
    {
        if (request()->isPost()) {
            $data = request()->param();
            try {
                SystemUsers::where('uname', '=', trim($data['uname']))->findOrFail();
                return json(['err' => 1, 'msg' => '存在同名账号']);
            } catch (ModelNotFoundException $e) {
                $admin = new SystemUsers();
                $admin->uname = $data['uname'];
                $admin->salt = 'abc';
                $admin->pass = md5(trim($data['password']) . 'abc');
                $admin->groupid = 2;
                $result = $admin->save();
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
        $data = request()->param();
        try {
            $admin = SystemUsers::where('uid', '=', $data['uid'])->findOrFail();
            if (request()->isPost()) {
                $admin->uname = $data['uname'];
                if (empty($data['password']) || is_null($data['password'])) {

                } else {
                    $admin->pass = md5(trim($data['password']) . 'abc');
                }
                $result = $admin->save();
                if ($result) {
                    return json(['err' => 0, 'msg' => '修改成功']);
                } else {
                    return json(['err' => 1, 'msg' => '修改失败']);
                }
            }
            View::assign([
                'admin' => $admin,
            ]);
        } catch (ModelNotFoundException $e) {
            return json(['err' => 1, 'msg' => $e->getMessage()]);
        }
        return view();
    }

    public function delete()
    {
        $uid = input('uid');
        $count = count(SystemUsers::select());
        if ($count <= 1) {
            return json(['err' => '1', 'msg' => '至少保留一个管理员账号']);
        }
        $result = SystemUsers::destroy($uid);
        if ($result) {
            return json(['err' => '0', 'msg' => '删除成功']);
        } else {
            return json(['err' => '1', 'msg' => '删除失败']);
        }
    }
}