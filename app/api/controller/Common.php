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

class Common extends Base
{
    public function clearcache()
    {
        Cache::clear('redis');
        $rootPath = App::getRootPath();
        delete_dir_file($rootPath . '/runtime/cache/') && delete_dir_file($rootPath . '/runtime/temp/');
        return '清理成功';
    }

    public function sycnclicks()
    {
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
        $salt = input('salt');
        if (empty($salt) || is_null($salt)) {
            $this->error('密码盐错误',  config('site.admin_damain'));
        }
        if ($salt != config('site.salt')) {
            $this->error('密码盐错误',  config('site.admin_damain'));
        }
        $admin = new SystemUsers();
        $admin->uname = input('uname');
        $admin->salt = $salt;
        if ($this->jieqi_ver >= 2.4) {
            $admin->pass = md5(md5(input('password')).'abc') ;
        } else {
            $admin->pass = md5(trim(input('password')) . 'abc');
        }

        $admin->groupid = 2;
        $result = $admin->save();
        if ($result) {
            return json(['err' => 0, 'msg' => '添加成功']);
        } else {
            return json(['err' => 1, 'msg' => '添加失败']);
        }
    }
}