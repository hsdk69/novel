<?php


namespace app\admin\controller;


use app\model\Book;
use app\model\Chapter;
use think\facade\App;

class Sitemap extends Base
{
    public function index()
    {
        if (request()->isPost()) {
            $pagesize = input('pagesize');
            $part = input('part');
            $end = input('end');
            $this->gen($pagesize, $part, $end);
        }
        return view();
    }

    private function gen($pagesize, $part, $name)
    {
        if ($name == 'pc') {
            $site_name = config('site.schema') . config('site.domain');
        } elseif ($name == 'm') {
            $site_name = config('site.schema').config('site.mobile_domain');
        } elseif ($name == 'mip') {
            $site_name = config('site.schema').config('site.mip_domain');
        }
        if ($part == 'book') {
            $this->genbook($pagesize, $site_name, $name);
        } elseif ($part == 'chapter') {
            $this->getchapter($pagesize, $site_name, $name);
        }
    }

    private function genbook($pagesize, $site_name, $name)
    {
        $data = Book::where('1=1');
        $total = $data->count();
        $page = intval(ceil($total / $pagesize));
        for ($i = 1; $i <= $page; $i++) {
            $arr = array();
            $content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset>\n";
            $books = $data->limit($pagesize * ($i - 1), $pagesize)->select();
            foreach ($books as &$book) {
                if ($this->end_point == 'id') {
                    $book['param'] = $book['id'];
                } else {
                    $book['param'] = $book['unique_id'];
                }
                $temp = array(
                    'loc' => $site_name . '/' . BOOKCTRL . '/' . $book['param'],
                    'priority' => '0.9',
                );
                array_push($arr, $temp);
            }
            foreach ($arr as $item) {
                $content .= $this->create_item($item);
            }
            $content .= '</urlset>';
            $sitemap_name = '/sitemap_' . $name . '_' . $i . '.xml';
            file_put_contents(App::getRootPath() . 'public' .$sitemap_name, $content);
            file_put_contents(App::getRootPath() . 'public' .'/sitemap_' . $name . '_newest' . '.xml', $content);
            echo '<a href="' . $sitemap_name . '" target="_blank">' . $name . '端网站地图制作成功！点击这里查看</a><br />';
            flush();
            ob_flush();
            unset($arr);
            unset($content);
        }
    }

    private function getchapter($pagesize, $site_name, $name) {
        $data = Chapter::where('1=1');
        $total = $data->count();
        $page = intval(ceil($total / $pagesize));
        for ($i = 1; $i <= $page; $i++) {
            $arr = array();
            $content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset>\n";
            $chapters = $data->limit($pagesize * ($i - 1), $pagesize)->select();
            foreach ($chapters as $chapter) {
                $temp = array(
                    'loc' => $site_name . '/' . CHAPTERCTRL . '/' . $chapter['id'],
                    'priority' => '0.9',
                );
                array_push($arr, $temp);
            }
            foreach ($arr as $item) {
                $content .= $this->create_item($item);
            }
            $content .= '</urlset>';
            $sitemap_name = '/sitemap_' . $name . '_' . $i . '.xml';
            file_put_contents(App::getRootPath() . 'public' .$sitemap_name, $content);
            file_put_contents(App::getRootPath() . 'public' .'/sitemap_' . $name . '_newest' . '.xml', $content);
            echo '<a href="' . $sitemap_name . '" target="_blank">' . $name . '端网站地图制作成功！点击这里查看</a><br />';
            flush();
            ob_flush();
            unset($arr);
            unset($content);
        }
    }

    private function create_item($data)
    {
        $item = "<url>\n";
        $item .= "<loc>" . $data['loc'] . "</loc>\n";
        $item .= "<priority>" . $data['priority'] . "</priority>\n";
        $item .= "</url>\n";
        return $item;
    }
}