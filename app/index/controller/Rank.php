<?php


namespace app\index\controller;


use app\model\Chapter;
use think\facade\View;

class Rank extends Base
{
    protected $bookService;

    protected function initialize()
    {
        parent::initialize();
        $this->bookService = new \app\service\BookService();
    }

    public function index()
    {
        return view($this->tpl);
    }

    public function getRanks()
    {
        $books = array();
        $op = input('op');
        $date = input('date');
        $time = date('Y-m-d', strtotime('-1 ' . $date));

        if ($op == 'click') {
            $books = $this->bookService->getHotBooks($this->prefix, $this->end_point, $time, 30);
        } elseif ($op == 'new') {
            $books = cache('newest_homepage');
            if (!$books) {
                $books = $this->bookService->getBooks($this->end_point, 'last_time', '1=1', 30);
                cache('newest_homepage', $books, null, 'redis');
            }
        } elseif ($op == 'end') {
            $books = cache('ends_homepage');
            if (!$books) {
                $books = $this->bookService->getBooks( $this->end_point, 'last_time', [['end', '=', '2']], 30);
                cache('ends_homepage', $books, null, 'redis');
            }
        }

        foreach ($books as &$book) {
            $book['date'] = date('Y-m-d H:i:s', $book['last_time']);
            $last_chapter = Chapter::where('book_id', '=', $book['id'])->order('chapter_order', 'desc')->limit(1)->find();
            $book['last_chapter'] = $last_chapter;
        }
        return json(['books' => $books, 'success' => 1]);
    }
}