<?php


namespace app\admin\controller;


use app\model\ArticleArticle;
use app\model\ArticleChapter;
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

    private function gen($pagesize, $part, $end)
    {
        if ($end == 'pc') {
            $site_name =  config('site.domain');
        } elseif ($end == 'm') {
            $site_name = config('site.mobile_domain');
        }
        if ($part == 'book') {
            $this->genbook($pagesize, $site_name, $end);
        } elseif ($part == 'chapter') {
            $this->genchapter($pagesize, $site_name, $end);
        } elseif ($part == 'tail') {
            $this->gentail($pagesize, $site_name, $end);
        }
    }

    private function genbook($pagesize, $site_name, $end)
    {
        $data = ArticleArticle::where('1=1');
        $total = $data->count();
        $page = intval(ceil($total / $pagesize));
        for ($i = 1; $i <= $page; $i++) {
            $arr = array();
            $content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset>\n";
            $books = $data->limit($pagesize * ($i - 1), $pagesize)->select();
            foreach ($books as &$book) {
                if ($this->end_point == 'id') {
                    $book['param'] = $book['articleid'];
                } else {
                    $book['param'] = $book['backupname'];
                }
                $temp = array(
                    'loc' => $site_name . '/' . $end . '/' . BOOKCTRL . '/' . $book['param'],
                    'priority' => '0.9',
                );
                array_push($arr, $temp);
            }
            foreach ($arr as $item) {
                $content .= $this->create_item($item);
            }
            $content .= '</urlset>';
            $sitemap_name = '/sitemap_book_' . $end . '_' . $i . '.xml';
            file_put_contents(App::getRootPath() . 'public' .$sitemap_name, $content);
            file_put_contents(App::getRootPath() . 'public' .'/sitemap_book_' . $end . '_newest' . '.xml', $content);
            echo '<a href="' . $sitemap_name . '" target="_blank">' . $end . '端网站地图制作成功！点击这里查看</a><br />';
            flush();
            ob_flush();
            unset($arr);
            unset($content);
        }
    }

    private function genchapter($pagesize, $site_name, $end) {
        $data = ArticleChapter::where('1=1');
        $total = $data->count();
        $page = intval(ceil($total / $pagesize));
        for ($i = 1; $i <= $page; $i++) {
            $arr = array();
            $content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset>\n";
            $chapters = $data->limit($pagesize * ($i - 1), $pagesize)->select();
            foreach ($chapters as $chapter) {
                $temp = array(
                    'loc' => $site_name . '/' . $end . '/' . CHAPTERCTRL . '/' . $chapter['chapterid'],
                    'priority' => '0.9',
                );
                array_push($arr, $temp);
            }
            foreach ($arr as $item) {
                $content .= $this->create_item($item);
            }
            $content .= '</urlset>';
            $sitemap_name = '/sitemap_chapter_' . $end . '_' . $i . '.xml';
            file_put_contents(App::getRootPath() . 'public' .$sitemap_name, $content);
            file_put_contents(App::getRootPath() . 'public' .'/sitemap_chapter_' . $end . '_newest' . '.xml', $content);
            echo '<a href="' . $sitemap_name . '" target="_blank">' . $end . '端网站地图制作成功！点击这里查看</a><br />';
            flush();
            ob_flush();
            unset($arr);
            unset($content);
        }
    }

    private function gentail($pagesize, $site_name, $end) {
        $data = Tail::where('1=1');
        $total = $data->count();
        $page = intval(ceil($total / $pagesize));
        for ($i = 1; $i <= $page; $i++) {
            $arr = array();
            $content = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset>\n";
            $tails = $data->limit($pagesize * ($i - 1), $pagesize)->select();
            foreach ($tails as &$tail) {
                $temp = array(
                    'loc' => $site_name . '/' . $end . '/tail/' . $tail['tailcode'],
                    'priority' => '0.9',
                );
                array_push($arr, $temp);
            }
            foreach ($arr as $item) {
                $content .= $this->create_item($item);
            }
            $content .= '</urlset>';
            $sitemap_name = '/sitemap_tail_' . $end . '_' . $i . '.xml';
            file_put_contents(App::getRootPath() . 'public' . $sitemap_name, $content);
            file_put_contents(App::getRootPath() . 'public' . '/sitemap_tail_' . $end . '_newest' . '.xml', $content);
            echo '<a href="' . $sitemap_name . '" target="_blank">' . $end . '端网站地图制作成功！点击这里查看</a><br />';
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