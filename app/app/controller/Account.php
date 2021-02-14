<?php


namespace app\app\controller;


use app\model\SystemUsers;
use app\validate\Phone;
use app\validate\User as UserValidate;
use think\db\exception\ModelNotFoundException;
use Firebase\JWT\JWT;

class Account extends Base
{
    public function register()
    {
        $data = request()->param();
        $validate = new UserValidate();
        if ($validate->check($data)) {
            $uname =trim($data['username']);
            try {
                SystemUsers::where('uname', '=', $uname)->findOrFail();
                return json(['err' => 1, 'msg' => '用户名已经存在']);
            } catch (ModelNotFoundException $e) {
                $user = new SystemUsers();
                $user->uname = trim($data['username']);
                $salt = gen_uid(5);
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
                    return json(['success' => 1, 'msg' => '注册成功，请登录']);
                } else {
                    return json(['success' => 0, 'msg' => '注册失败，请尝试重新注册']);
                }
            }
        } else {
            return json(['success' => 0, 'msg' => $validate->getError()]);
        }
    }

    public function phonereg() {
        $data = request()->param();
        if (verifycode($data['code'], $data['mobile']) == 0) {
            return json(['success' => 0, 'msg' => '验证码错误']);
        }
        $validate = new Phone();
        if ($validate->check($data)) {
            try {
                return json(['success' => 0, 'msg' => '手机号已存在，请直接登录']);
            } catch (ModelNotFoundException $e) {
                $user = new SystemUsers();
                $user->uname = trim($data['username']);
                $salt = gen_uid(5);
                $user->salt = $salt;
                $user->pass = md5(trim($data['password']).$salt);
                $user->mobile = trim($data['mobile']);
                $user->siteid = 0;
                $user->groupid = 3;
                $user->regdate = time();
                $user->sex = 1;
                $user->workid = 0;
                $user->lastlogin = 0;
                $result = $user->save();
                if ($result) {
                    return json(['success' => 1, 'msg' => '注册成功，请登录']);
                } else {
                    return json(['success' => 0, 'msg' => '注册失败，请尝试重新注册']);
                }
            }
        }
    }

    public function login()
    {
        $map = array();
        $map[] = ['uname', '=', trim(input('username'))];
        $map[] = ['groupid', '=', 3];
        $password = trim(input('password'));
        try {
            $user = SystemUsers::where($map)->findOrFail();
            $this->uid = $user->uid;
            $key = config('site.api_key');
            $token = [
                "iat" => time(), //签发时间
                "nbf" => time(), //在什么时候jwt开始生效  （这里表示生成100秒后才生效）
                "exp" => time() + 60 * 60 * 24, //token 过期时间
                "uid" => $user->uid, //记录的userid的信息，这里是自已添加上去的，如果有其它信息，可以再添加数组的键值对
                "nick_name" => $user->name,
                "email" => $user->email,
            ];
            $utoken = JWT::encode($token, $key, "HS256");
            $userInfo = [];
            $userInfo['uid'] = $user->uid;
            $userInfo['uname'] = $user->uname;
            $userInfo['nick_name'] = $user->name;
            $userInfo['email'] = $user->email;
            $userInfo['utoken'] = $utoken;

            return json(['success' => 1, 'userInfo' => $userInfo]);
        } catch (ModelNotFoundException $e) {
            return json(['success' => 0, 'msg' => '用户名或密码错误']);
        }
    }

    public function autologin() {
        $data = request()->param();
        $map = array();
        $map[] = ['suid', '=', trim($data['suid'])];
        $key = config('site.api_key');
        try {
            $user = SystemUsers::where($map)->findOrFail();
            if ($user->delete_time > 0) {
                return json(['success' => 0, 'msg' => '用户被锁定']);
            } else {
                $key = config('site.api_key');
                $token = [
                    "iat" => time(), //签发时间
                    "nbf" => time(), //在什么时候jwt开始生效  （这里表示生成100秒后才生效）
                    "exp" => time() + 60 * 60 * 24, //token 过期时间
                    "uid" => $user->uid, //记录的userid的信息，这里是自已添加上去的，如果有其它信息，可以再添加数组的键值对
                    "nick_name" => $user->nick_name,
                    "email" => $user->email,
                ];
                $utoken = JWT::encode($token, $key, "HS256");
                $userInfo = [];
                $userInfo['uid'] = $user->uid;
                $userInfo['uname'] = $user->uname;
                $userInfo['nick_name'] = $user->nick_name;
                $userInfo['email'] = $user->email;
                $userInfo['utoken'] = $utoken;

                return json(['success' => 1, 'userInfo' => $userInfo]);
            }
        }  catch (ModelNotFoundException $e) {
            $user = new SystemUsers();
            $user->uname = trim($data['username']);
            $salt = gen_uid(5);
            $user->salt = $salt;
            $user->pass = md5(trim($data['password']).$salt);
            $user->mobile = trim($data['mobile']);
            $user->siteid = 0;
            $user->groupid = 3;
            $user->regdate = time();
            $user->sex = 1;
            $user->workid = 0;
            $user->lastlogin = 0;
            $result = $user->save();
            if ($result) {
                $token = [
                    "iat" => time(), //签发时间
                    "nbf" => time(), //在什么时候jwt开始生效  （这里表示生成100秒后才生效）
                    "exp" => time() + 60 * 60 * 24, //token 过期时间
                    "uid" => $user->id, //记录的userid的信息，这里是自已添加上去的，如果有其它信息，可以再添加数组的键值对
                    "suid" => $user->suid,
                    "level" => $user->level
                ];
                $utoken = JWT::encode($token, $key, "HS256");
                $user['utoken'] = $utoken;
                $user['balance'] = 0;
                return json(['success' => 1, 'userInfo' => $user]);
            } else {
                return json(['success' => 0, 'msg' => '登录失败，请尝试重新登录']);
            }
        }
    }

    public function phonelogin() {
        $data = request()->param();
        if (verifycode($data['code'], $data['mobile']) == 0) {
            return json(['success' => 0, 'msg' => '验证码错误']);
        }
        $validate = new Phone();
        if ($validate->check($data)) {
            $key = config('site.api_key');
            try{
                $user = SystemUsers::where('mobile', '=', trim($data['mobile']))->findOrFail();
                $this->uid = $user->uid;
                $token = [
                    "iat" => time(), //签发时间
                    "nbf" => time(), //在什么时候jwt开始生效  （这里表示生成100秒后才生效）
                    "exp" => time() + 60 * 60 * 24, //token 过期时间
                    "uid" => $user->uid, //记录的userid的信息，这里是自已添加上去的，如果有其它信息，可以再添加数组的键值对
                    "nick_name" => $user->name,
                    "email" => $user->email,
                ];
                $utoken = JWT::encode($token, $key, "HS256");
                $user['utoken'] = $utoken;
                return json(['success' => 1, 'userInfo' => $user]);
            } catch (ModelNotFoundException $e) {
                return json(['success' => 0, 'msg' => '手机号不存在']);
            }
        } else {
            return json(['success' => 0, 'msg' => $validate->getError()]);
        }
    }

    public function logout() {
        $utoken = input('utoken');
        $key = config('site.api_key');
        $info = JWT::decode($utoken, $key, array('HS256', 'HS384', 'HS512', 'RS256' ));
        $arr = (array)$info;
        if ($this->uid == $arr['uid']){
            unset($this->uid);
        }
        return json(['success' => 1, 'msg' => '登出成功']);
    }

    public function checkAuth()
    {
        $utoken = input('utoken');
        if (isset($utoken)) {
            $json = $this->getAuth($utoken);
        } else {
            $json = json(['success' => 0, 'msg' => '传递参数错误']);
        }
        return $json;
    }
}