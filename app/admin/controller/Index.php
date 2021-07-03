<?php


namespace app\admin\controller;


use DirectoryIterator;
use GuzzleHttp\Client;
use http\Exception;
use think\facade\App;
use think\facade\Cache;
use think\facade\Db;
use think\facade\View;

class Index extends Base
{
    public function index()
    {
        return view();
    }

    public function info()
    {
        $site_name = config('site.site_name');
        $domain = config('site.domain');
        $mobile_domain = config('site.mobile_domain');
        $server = config('site.server');
        $imgServer = config('site.imgServer');
        $api_key = config('site.api_key');
        $app_key = config('site.app_key');
        $front_tpl = config('site.tpl');
        $jieqi_ver = config('site.jieqi_ver');

        $dirs = array();
        $dir = new DirectoryIterator(App::getRootPath() . 'public/template/');
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                array_push($dirs, $fileinfo->getFilename());
            }
        }

        View::assign([
            'site_name' => $site_name,
            'domain' => $domain,
            'mobile_domain' => $mobile_domain,
            'server' => $server,
            'imgServer' => $imgServer,
            'api_key' => $api_key,
            'app_key' => $app_key,
            'front_tpl' => $front_tpl,
            'tpl_dirs' => $dirs,
            'jieqi_ver' => $jieqi_ver
        ]);
        return view();
    }

    public function update()
    {
        if (request()->isPost()) {
            $site_name = input('site_name');
            $domain = input('domain');
            $mobile_domain = input('mobile_domain');
            $server = input('server');
            $imgServer = input('imgServer');
            $api_key = input('api_key');
            $app_key = input('app_key');
            $front_tpl = input('front_tpl');
            $jieqi_ver = input('jieqi_ver');
            $site_code = <<<INFO
<?php
return [
    'domain' => '{$domain}',
    'site_name' => '{$site_name}',
    'mobile_domain' => '{$mobile_domain}',
    'server' => '{$server}',
    'imgServer' => '{$imgServer}',
    'api_key' => '{$api_key}', 
    'app_key' => '{$app_key}',
    'tpl' => '{$front_tpl}',
    'jieqi_ver' => '{$jieqi_ver}'
 ];
INFO;
            $file = App::getRootPath() . 'config/site.php';
            if (!file_exists($file)) {
                return json(['err' => 1, 'msg' => '配置文件不存在']);
            }
            file_put_contents($file, $site_code);
            return json(['err' => 0, 'msg' => '修改成功']);
        }
    }

    public function seo()
    {
        if (request()->post()) {
            $book_end_point = input('book_end_point');
            $name_format = input('name_format');
            $num = input('sitemap_gen_num');
            $code = <<<INFO
        <?php
        return [
            'book_end_point' => '{$book_end_point}',  //分别为id和name两种形式
            'name_format' => '{$name_format}', //pure是纯拼音,permalink是拼音带连接字符串，abbr是拼音首字母，abbr_permalink是首字母加连接字符串
            'sitemap_gen_num' => '{$num}' //生成最近的1000条，如果想要全部生成，则填0
        ];
INFO;
            file_put_contents(App::getRootPath() . 'config/seo.php', $code);
            return json(['err' => 0, 'msg' => '保存成功']);
        } else {
            $book_end_point = config('seo.book_end_point');
            $name_format = config('seo.name_format');
            $num = config('seo.sitemap_gen_num');
            View::assign([
                'book_end_point' => $book_end_point,
                'name_format' => $name_format,
                'sitemap_gen_num' => $num
            ]);
            return view();
        }
    }

    public function clearCache()
    {
        Cache::clear('redis');
        Cache::clear('pay');
        $rootPath = App::getRootPath();
        delete_dir_file($rootPath . '/runtime/');
        return json(['err' => 0, 'msg' => '清理缓存']);
    }

    public function routeconfig()
    {
        $path = App::getRootPath() . 'public/routeconf.php';
        if (request()->isPost()) {
            $conf = input('json');
            file_put_contents($path, $conf);
            return json(['err' => 0, 'msg' => '保存成功']);
        }
        $conf = file_get_contents($path);
        View::assign('json', $conf);
        return view();
    }

    public function upgrade()
    {
        try {
            $client = new Client();
            $srcUrl = App::getRootPath() . "/ver.txt";
            $localVersion = (int)str_replace('.', '', file_get_contents($srcUrl));
            $server = "https://cdn.jsdelivr.net/gh/hiliqi/novel/";
            $serverFileUrl = $server . "/ver.txt";
            $res = $client->request('GET', $serverFileUrl); //读取版本号
            $serverVersion = (int)str_replace('.', '', $res->getBody());
            echo '<p></p>';

            if ($serverVersion > $localVersion) {
                for ($i = $localVersion + 1; $i <= $serverVersion; $i++) {
                    $res = $client->request('GET', "https://cdn.jsdelivr.net/gh/hiliqi/raccoon_up/novel/" . $i . ".json");
                    if ((int)($res->getStatusCode()) == 200) {
                        $json = json_decode($res->getBody(), true);

                        foreach ($json['update'] as $value) {
                            $data = $client->request('GET', $server . '/' . $value)->getBody(); //根据配置读取升级文件的内容
                            $saveFileName = App::getRootPath() . $value;
                            $dir = dirname($saveFileName);
                            if (!file_exists($dir)) {
                                mkdir($dir, 0777, true);
                            }
                            file_put_contents($saveFileName, $data, true); //将内容写入到本地文件
                            echo '<p style="padding-left:15px;font-weight: 400;color:#999;">升级文件' . $value . '</p>';
                        }

                        foreach ($json['delete'] as $value) {
                            $flag = unlink(App::getRootPath() . $value);
                            if ($flag) {
                                echo '<p style="padding-left:15px;font-weight: 400;color:#999;">删除文件' . $value . '</p>';
                            } else {
                                echo '<p style="padding-left:15px;font-weight: 400;color:#999;">删除文件失败</p>';
                            }
                        }

                        foreach ($json['sql'] as $value) {
                            //Db::execute('ALTER TABLE aaa ADD `name` INT(0) NOT NULL DEFAULT 0');
                            $value = str_replace('[prefix]', $this->prefix, $value);
                            Db::execute($value);
                            echo '<p style="padding-left:15px;font-weight: 400;">成功执行以下SQL语句：' . $value . '</p>';
                        }
                    }
                }
                echo '<p style="padding-left:15px;font-weight: 400;color:#999;">升级完成</p>';
                file_put_contents($srcUrl, (string)$res->getBody(), true); //将版本号写入到本地文件
                echo '<p style="padding-left:15px;font-weight: 400;color:#999;">覆盖版本号</p>';
            } else {
                echo '<p style="padding-left:15px;font-weight: 400;color:#999;">已经是最新版本！当前版本是' . $localVersion . '</p>';
            }
        } catch (Exception $e) {
            echo '<p style="padding-left:15px;font-weight: 400;color:#999;">' . $e->getMessage() . '</p>';
        }
    }
}