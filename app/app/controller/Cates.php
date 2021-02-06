<?php


namespace app\app\controller;

use app\model\Banner;
use app\model\ArticleArticle;
use app\model\ArticleChapter;
use app\model\Cate;

class Cates extends Base
{
    public function getList()
    {
        $cates = cache('cates ');
        if (!$cates ) {
            $cates  = Cates::select();
            cache('cates', $cates, null, 'redis');
        }

        $result = [
            'success' => 1,
            'cates' => $cates
        ];
        return json($result);
    }

    public function getBookList()
    {
        $startItem = input('startItem');
        $pageSize = input('pageSize');

        $map = array();
        $cate_selector = -1;
        $words_selector = -1;
        $end_selector = -1;

        $cate = (int)input('sortid');
        $cate_name = '全部';
        $cate_model = Cate::where('sortid', $cate)->find();
        if (isset($cate_model)) {
            $cate_name = $cate_model->cate_name;
        }
        if ($cate == 0 || $cate == -1) {

        } else {
            $map[] = ['sortid', '=', $cate];
            $cate_selector = $cate;
        }
        $words = (int)input('words');
        if ($words == 0 || $words == '-1') {

        } else {
            $map[] = ['words', '<=', $words];
            $words_selector = $words;
        }
        $fullflag = (int)input('fullflag');
        if ($fullflag == -1) {

        } else {
            $map[] = ['fullflag', '=', $fullflag];
            $end_selector = $fullflag;
        }
        $data = ArticleArticle::where($map)->order('lastupdate', 'desc');
        $count = $data->count();
        $books = $data->limit($startItem, $pageSize)->select();
        foreach ($books as &$book) {
            $bigId = floor((double)($book['articleid'] / 1000));
            $book['cover'] = $this->server . sprintf('/files/article/image/%s/%s/%ss.jpg',
                    $bigId, $book['articleid'], $book['articleid']);
        }

        return json([
            'success' => 1,
            'books' => $books,
            'count' => $count
        ]);
    }

    public function getBanners()
    {
        $num = input('num');
        $banners = cache('bannersHomepage');
        if (!$banners) {
            $banners = Banner::with('book')->order('banner_order','desc')->select();
            foreach ($banners as &$banner) {
                $banner['pic'] = $this->server . $banner['pic'];
            }
            cache('bannersHomepage',$banners, null, 'redis');
        }
        return json(['success' => 1, 'banners' => $banners]);
    }
}