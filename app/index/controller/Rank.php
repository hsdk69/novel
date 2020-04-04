<?php


namespace app\index\controller;


use app\model\Chapter;
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
                $books = $this->bookService->getBooks($this->end_point, 'last_time', '1=1', 30);
                foreach ($books as &$book) {
                    $book['clicks'] = Db::query('SELECT SUM(clicks) as clicks FROM xwx_clicks WHERE book_id='.$book['id'])[0]['clicks'];
                }
                cache('newest_homepage', $books, null, 'redis');
            }
        } elseif ($op == 'click') {
            $books = cache('hot_books');
            if (!$books) {
                $books = $this->bookService->getHotBooks($this->prefix, $this->end_point);
                foreach ($books as &$book) {
                    $book['clicks'] = Db::query('SELECT SUM(clicks) as clicks FROM xwx_clicks WHERE book_id='.$book['id'])[0]['clicks'];
                }
                cache('hot_books', $books, null, 'redis');
            }
        } elseif ($op == 'end') {
            $books = cache('ends_homepage');
            if (!$books) {
                $books = $this->bookService->getBooks($this->end_point, 'last_time', [['end', '=', '1']], 30);
                foreach ($books as &$book) {
                    $book['clicks'] = Db::query('SELECT SUM(clicks) as clicks FROM xwx_clicks WHERE book_id='.$book['id'])[0]['clicks'];
                }
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