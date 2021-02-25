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
        $data = $this->tailService->getlist(20, $this->end_point, 'id');
        unset($data['page']['query']['page']);
        $param = '';
        foreach ($data['page']['query'] as $k => $v) {
            $param .= '&' . $k . '=' . $v;
        }
        View::assign([
            'tails' => $data['tails'],
            'page' => $data['page'],
            'param' => $param,
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
        $recommand = cache('randBooks:' . $tail->book->sortid);
        if (!$recommand) {
            $recommand = $this->bookService->getByCate($tail->book->sortid, $this->end_point, 10);
            cache('randBooks:' . $tail->book->sortid, $recommand, null, 'redis');
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
            'recommand' => $recommand,
        ]);
        return view($this->tpl);
    }
}