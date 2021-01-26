<?php


namespace app\mobile\controller;


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
        View::assign([
            'header' => '排行',
        ]);
        return view($this->tpl);
    }

    public function getRanks()
    {
        $books = array();
        $op = input('op');

        if ($op == 'click') {
            $books = cache('hot_books');
            if (!$books) {
                $books = $this->bookService->getHotBooks($this->prefix, $this->end_point);
                cache('hot_books', $books, null, 'redis');
            }
        } elseif ($op == 'new') {
            $books = cache('newest_homepage');
            if (!$books) {
                $books = $this->bookService->getBooks($this->end_point, 'lastupdate', '1=1', 30);
                cache('newest_homepage', $books, null, 'redis');
            }
        } elseif ($op == 'fullflag') {
            $books = cache('ends_homepage');
            if (!$books) {
                $books = $this->bookService->getBooks($this->end_point, 'lastupdate', [['fullflag', '=', '1']], 30);
                cache('ends_homepage', $books, null, 'redis');
            }
        }

        foreach ($books as &$book) {
            $book['date'] = date('Y-m-d H:i:s', $book['lastupdate']);
        }
        return json(['books' => $books, 'success' => 1]);
    }
}