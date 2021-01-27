<?php


namespace app\mobile\controller;


use app\model\SystemUsers;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\facade\View;

class Account extends Base
{
    public function login()
    {
        if (request()->isPost()) {
//            $captcha = input('captcha');
//            if( !captcha_check($captcha ))
//            {
//                return ['err' => 1, 'msg' => '验证码错误'];
//            }
            $map = array();
            $map[] = ['uname', '=', trim(input('username'))];
            $map[] = ['groupid', '=', 3];
            $password = trim(input('password'));
            try {
                $user = SystemUsers::where($map)->findOrFail();
                $passsalt = md5($password.$user['salt']);
                if ($passsalt != $user['pass']) {
                    return json(['err' => 1, 'msg' => '密码错误']);
                } else {
                    $user->lastlogin = time();
                    $user->save();
                    cookie('xwx_user_id', $user->uid);
                    cookie('xwx_user', $user->uname);
                    cookie('xwx_nick_name', $user->name);
                    return json(['err' => 0, 'msg' => '登录成功']);
                }
            } catch (DataNotFoundException $e) {
                return json(['err' => 1, 'msg' => '用户名或密码错误']);
            } catch (ModelNotFoundException $e) {
                return json(['err' => 1, 'msg' => '用户名或密码错误']);
            }
        } else {
            View::assign([
                'header' => '用户登录'
            ]);
            return view($this->tpl);
        }
    }

    public function register()
    {
        if (request()->isPost()) {
//            $captcha = input('captcha');
//            if( !captcha_check($captcha ))
//            {
//                return ['err' => 1, 'msg' => '验证码错误'];
//            }
            $data = request()->param();
            $validate = new \app\validate\User();
            if ($validate->check($data)) {
                $uname =trim($data['username']);
                try {
                    SystemUsers::where('uname', '=', $uname)->findOrFail();
                    return json(['err' => 1, 'msg' => '用户名已经存在']);
                } catch (ModelNotFoundException $e) {
                    $user = new SystemUsers();
                    $user->uname = trim($data['username']);
                    //生成5位数的dwzkey
                    $key_str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
                    $salt = substr(str_shuffle($key_str), mt_rand(0, strlen($key_str) - 11), 5);
                    $user->salt = $salt;
                    $user->pass = md5(trim($data['password']).$salt);
                    $user->email = trim($data['email']);
                    $user->siteid = 0;
                    $user->groupid = 3;
                    $user->regdate = time();
                    $user->sex = 1;
                    $user->workid = 0;
                    $user->lastlogin = 0;
                    $result = $user->save();
                    if ($result) {
                        return json(['err' => 0, 'msg' => '注册成功，请登录']);
                    } else {
                        return json(['err' => 1, 'msg' => '注册失败，请尝试重新注册']);
                    }
                }
            } else {
                return json(['err' => 1, 'msg' => $validate->getError()]);
            }
        } else {
            View::assign([
                'header' => '用户注册'
            ]);
            return view($this->tpl);
        }
    }

    public function logout()
    {
        cookie('xwx_user', null);
        cookie('xwx_user_id', null);
        cookie('xwx_nick_name', null);
        cookie('xwx_user_mobile',null);
        cookie('xwx_vip_expire_time', null);
        $this->redirect('/login');
    }

    public function captcha()
    {
        ob_clean();
        return captcha();
    }
}