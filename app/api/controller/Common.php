<?php


namespace app\api\controller;


use app\BaseController;
use app\common\RedisHelper;
use app\model\ArticleArticle;
use think\db\exception\ModelNotFoundException;
use think\facade\Cache;
use think\facade\App;
use app\model\Clicks;
use app\model\VipCode;
use app\model\ChargeCode;
use app\model\SystemUsers;

class Common extends BaseController
{
    public function clearcache()
    {
        $key = input('api_key');
        if (empty($key) || is_null($key)) {
            return 'api密钥不能为空！';
        }
        if ($key != config('site.api_key')) {
            return 'api密钥错误！';
        }
        Cache::clear('redis');
        $rootPath = App::getRootPath();
        delete_dir_file($rootPath . '/runtime/cache/') && delete_dir_file($rootPath . '/runtime/temp/');
        return '清理成功';
    }

    public function sycnclicks()
    {
        $key = input('api_key');
        if (empty($key) || is_null($key)) {
            return 'api密钥不能为空！';
        }
        if ($key != config('site.api_key')) {
            return 'api密钥错误！';
        }
        $day = input('date');
        if (empty($day)) {
            $day = date("Y-m-d", strtotime("-1 day"));
        }
        $redis = RedisHelper::GetInstance();
        $hots = $redis->zRevRange('click:' . $day, 0, 10, true);
        $now = date('Y-m-d');
        foreach ($hots as $k => $v) {
            try {
                $book = ArticleArticle::findOrFail((int)$k);
                $book->allvisit = $book->allvisit + (int)$v;
                $book->dayvisit = (int)($book->allvisit / 30);
                $book->weekvisit = (int)($book->allvisit / 7);
                $book->monthvisit = (int)($book->allvisit / 2);

                $result = $book->save();
                if ($result) {
                    $redis->zRem('click:' . $day, $k); //同步到数据库之后，删除redis中的这个日期的这本漫画的点击数
                }
            } catch (ModelNotFoundException $e) {

            }
        }
        return json(['success' => 1, 'msg' => '同步完成']);
    }

    public function resetpwd()
    {
        $api_key = input('api_key');
        if (empty($api_key) || is_null($api_key)) {
            $this->error('api密钥错误',  config('site.admin_damain'));
        }
        if ($api_key != config('site.api_key')) {
            $this->error('api密钥错误',  config('site.admin_damain'));
        }
        $salt = input('salt');
        if (empty($salt) || is_null($salt)) {
            $this->error('密码盐错误',  config('site.admin_damain'));
        }
        if ($salt != config('site.salt')) {
            $this->error('密码盐错误',  config('site.admin_damain'));
        }
        $username = input('username');
        if (empty($username) || is_null($username)) {
            $this->error('用户名不能为空',  config('site.admin_damain'));
        }
        $pwd = input('password');
        if (empty($pwd) || is_null($pwd)) {
            $this->error('密码不能为空',  config('site.admin_damain'));
        }
        SystemUsers::create([
            'username' => $username,
            'password' => trim($pwd)
        ]);
        $this->success('新管理员创建成功',  config('site.admin_damain'));
    }
}