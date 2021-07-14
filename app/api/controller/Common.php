<?php


namespace app\api\controller;


use app\BaseController;
use app\model\ArticleArticle;
use think\db\exception\ModelNotFoundException;
use think\facade\Cache;
use think\facade\App;
use app\model\SystemUsers;
use think\facade\Db;
use think\facade\Env;

class Common extends Base
{
    public function clearcache()
    {
        Cache::clear('redis');
        $rootPath = App::getRootPath();
        delete_dir_file($rootPath . '/runtime/cache/') && delete_dir_file($rootPath . '/runtime/temp/');
        return '清理成功';
    }

    //清空月人气
    public function clearmhits()
    {
        $prefix = Env::get('database.prefix');
        Db::query("update ".$prefix."article_article set monthvisit=0");
    }

    //清空周人气
    public function clearwhits()
    {
        $prefix = Env::get('database.prefix');
        Db::query("update ".$prefix."article_article set weekvisit=0");
    }

    //清空日人气
    public function cleardhits()
    {
        $prefix = Env::get('database.prefix');
        Db::query("update ".$prefix."article_article set dayvisit=0");
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