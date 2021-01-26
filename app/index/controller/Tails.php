<?php


namespace app\index\controller;


use app\model\ArticleChapter;
use app\model\Tail;
use think\facade\Db;
use think\facade\View;

class Tails extends Base
{
    protected $tailService;
    protected $bookService;

    protected function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->tailService = app('tailService');
        $this->bookService = app('bookService');
    }

    public function list()
    {
        $tails = $this->tailService->getlist(40, $this->prefix);
        View::assign([
            'tails' => $tails,
            'count' => count($tails)
        ]);
        return view($this->tpl);
    }

    public function index()
    {
        $id = input('id');
        $tail = cache('tail' . $id);
        if ($tail == false) {
            $tail = Tail::with('book.cate')->where('tailcode', '=', $id)->findOrFail();
            $tail->book->articlename = $tail->tailname;
            $bigId = floor((double)($tail['articleid'] / 1000));
            $tail->book['cover'] = sprintf('/files/article/image/%s/%s/%ss.jpg',
                $bigId, $tail['articleid'], $tail['articleid']);
            $tail->book['chapters'] = ArticleChapter::where('articleid','=',$tail['articleid'])->select();
            cache('tail' . $id, $tail, null, 'redis');
        }
        $recommand = cache('randBooks:' . $tail->book->typeid);
        if (!$recommand) {
            $recommand = $this->bookService->getByCate($tail->book->typeid, $this->end_point, 10);
            cache('randBooks:' . $tail->book->typeid, $recommand, null, 'redis');
        }

        $authors = cache('booksForAuthor:' . $tail->book->authorid);
        if (!$authors) {
            $authors = $this->bookService->getByAuthor($tail->book->authorid, $this->end_point);
            cache('booksForAuthor:' . $tail->book->authorid, $authors, null, 'redis');
        }

        $start = cache('bookStart:' . $id);
        if ($start == false) {
            $db = Db::query('SELECT chapterid FROM ' . $this->prefix . 'article_chapter WHERE articleid = ' . $tail->articleid . ' ORDER BY chapterid LIMIT 1');
            $start = $db ? $db[0]['chapterid'] : -1;
            cache('bookStart:' . $id, $start, null, 'redis');
        }

        View::assign([
            'book' => $tail->book,
            'start' => $start,
            'authors' => $authors,
            'recommand' => $recommand,
        ]);
        return view($this->tpl);
    }
}