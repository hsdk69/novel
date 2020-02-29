<?php


namespace app\mobile\controller;


use app\common\RedisHelper;
use app\model\User;
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
            $map[] = ['username', '=', trim(input('username'))];
            $map[] = ['password', '=', md5(strtolower(trim(input('password'))) . config('site.salt'))];
            try {
                $user = User::withTrashed()->where($map)->findOrFail();
                if ($user->delete_time > 0) {
                    return json(['err' => 1, 'msg' => '用户被锁定']);
                } else {
                    $user->last_login_time = time();
                    $user->save();
                    cookie('xwx_user_id', $user->id);
                    cookie('xwx_user', $user->username);
                    cookie('xwx_nick_name', $user->nick_name);
                    cookie('xwx_user_mobile', $user->mobile);
                    cookie('xwx_vip_expire_time', $user->vip_expire_time);
                    return json(['err' => 0, 'msg' => '登录成功']);
                }
            } catch (DataNotFoundException $e) {
                return json(['err' => 1, 'msg' => '用户名或密码错误']);
            } catch (ModelNotFoundException $e) {
                return json(['err' => 1, 'msg' => '用户名或密码错误']);
            }
        } else {
            View::assign([
                'site_name' => config('site.site_name'),
                'url' => config('site.url'),
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
            $ip = request()->ip();
            $redis = RedisHelper::GetInstance();
            if ($redis->exists('user_reg:' . $ip)) {
                return json(['err' => 1, 'msg' => '操作太频繁']);
            } else {
                $data = request()->param();
                $validate = new \app\validate\User();
                if ($validate->check($data)) {
                    $email =trim(input('email'));
                    try {
                        User::where('email', '=', $email)->findOrFail();
                        return json(['err' => 1, 'msg' => '邮箱已存在']);
                    } catch (DataNotFoundException $e) {
                    } catch (ModelNotFoundException $e) {
                    }
                    try {
                        User::where('username', '=', trim(request()->param('username')))->findOrFail();
                        return json(['err' => 1, 'msg' => '用户名已经存在']);
                    } catch (DataNotFoundException $e) {
                    } catch (ModelNotFoundException $e) {
                        $user = new User();
                        $user->username = trim($data['username']);
                        $user->password = trim($data['password']);
                        $user->email = trim($data['email']);
                        $pid = cookie('xwx_promotion');
                        if (!$pid) {
                            $pid = 0;
                        }
                        $user->pid = $pid; //设置用户上线id
                        $user->reg_ip = request()->ip();
                        $result = $user->save();
                        if ($result) {
                            $redis->set('user_reg:'.$ip,1,60); //写入锁
                            if ($pid > 0) {
                                try {
                                    $puser = User::findOrFail($pid);
                                    if ($puser) {
                                        if ($puser->vip_expire_time < time()) { //说明vip已经过期
                                            $puser->vip_expire_time = time() + 24 * 60 * 60;
                                        } else { //vip没过期，则在现有vip时间上增加
                                            $puser->vip_expire_time = $user->vip_expire_time + 24 * 60 * 60;
                                        }
                                    }
                                } catch (DataNotFoundException $e) {
                                } catch (ModelNotFoundException $e) {
                                }
                            }
                            return json(['err' => 0, 'msg' => '注册成功，请登录']);
                        } else {
                            return json(['err' => 1, 'msg' => '注册失败，请尝试重新注册']);
                        }
                    }
                } else {
                    return json(['err' => 1, 'msg' => $validate->getError()]);
                }
            }
        } else {
            View::assign([
                'site_name' => config('site.site_name'),
                'url' => config('site.schema').config('site.url'),
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