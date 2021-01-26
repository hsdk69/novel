<?php


namespace app\index\controller;


use app\model\ArticleChapter;
use think\facade\Db;
use think\facade\View;

class Rank extends Base
{
    protected $bookService;

    protected function initialize()
    {
        parent::initialize();
        $this->bookService = app('bookService');
    }

    public function index()
    {
        $op = input('cate');
        if (is_null($op) || empty($op)) $op = 'new';
        if ($op == 'new') {
            $books = cache('newest_homepage');
            if (!$books) {
                $books = $this->bookService->getBooks($this->end_point, 'lastupdate', '1=1', 30);
                cache('newest_homepage', $books, null, 'redis');
            }
        } elseif ($op == 'click') {
            $books = cache('hot_books');
            if (!$books) {
                $books = $this->bookService->getHotBooks($this->prefix, $this->end_point);
                cache('hot_books', $books, null, 'redis');
            }
        } elseif ($op == 'fullflag') {
            $books = cache('ends_homepage');
            if (!$books) {
                $books = $this->bookService->getBooks($this->end_point, 'lastupdate', [['fullflag', '=', '1']], 30);
                cache('ends_homepage', $books, null, 'redis');
            }
        }

        View::assign([
            'books' => $books,
            'op' => $op
        ]);
        return view($this->tpl);
    }
}