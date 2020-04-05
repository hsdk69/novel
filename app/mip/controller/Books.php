<?php


namespace app\mip\controller;


use app\common\RedisHelper;
use app\model\Book;
use app\model\Chapter;
use app\model\UserFavor;
use app\service\BookService;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\facade\View;

class Books extends Base
{
    protected $bookService;

    public function initialize()
    {
        parent::initialize();
        $this->bookService = app('bookService');
    }

    public function index()
    {
        $id = input('id');
        $book = cache('mip:book:' . $id);
        $tags = cache('tags:book:' . $id);
        if ($book == false) {
            try {
                $book_end_point = config('seo.book_end_point');
                if ($book_end_point == 'id') {
                    $book = Book::with('cate')->findOrFail($id);
                } else {
                    $book = Book::with('cate')->where('unique_id', '=', $id)->findOrFail();
                }
            } catch (DataNotFoundException $e) {
                abort(404, $e->getMessage());
            } catch (ModelNotFoundException $e) {
                abort(404, $e->getMessage());
            }
            $tags = [];
            if (!empty($book->tags) || is_null($book->tags)) {
                $tags = explode('|', $book->tags);
            }
            cache('mip:book:' . $id, $book, null, 'redis');
            cache('tags:book:' . $id, $tags, null, 'redis');
        }

//        $last_chapter = cache('last_chapter:' . $book->id);
//        if (!$last_chapter) {
//            $query = Db::query('SELECT * FROM ' . $this->prefix .
//                'chapter WHERE id = (SELECT MAX(id) FROM (SELECT id FROM xwx_chapter WHERE book_id=?) as a)',
//                [$book['id']]);
//            if (count($query) > 0) {
//                $last_chapter = $query[0];
//            } else {
//                $last_chapter = array();
//            }
//            cache('last_chapter:' . $book->id, $last_chapter, 'null', 'redis');
//        }

        $chapters = cache('mip:chapters:' . $book->id);
        if (!$chapters) {
            $chapters = Chapter::where('book_id', '=', $book->id)
                ->order('id', 'desc')->limit(10)->select();
            cache('mip:chapters:' . $book->id, $chapters, 'null', 'redis');
        }

        $redis = RedisHelper::GetInstance();
        $day = date("Y-m-d", time());
        //以当前日期为键，增加点击数
        $redis->zIncrBy('click:' . $day, 1, $book->id);


        $recommand = cache('randBooks:' . $book->cate_id);
        if (!$recommand) {
            $recommand = $this->bookService->getRecommand($book->cate_id, $this->end_point);
            cache('randBooks:' . $book->tags, $recommand, null, 'redis');
        }

        $start = cache('bookStart:' . $id);
        if ($start == false) {
            $db = Db::query('SELECT id FROM ' . $this->prefix . 'chapter WHERE book_id = ' . $book->id . ' ORDER BY id LIMIT 1');
            $start = $db ? $db[0]['id'] : -1;
            cache('bookStart:' . $id, $start, null, 'redis');
        }

        $isfavor = 0;
        if (!is_null($this->uid)) {
            $where[] = ['user_id', '=', $this->uid];
            $where[] = ['book_id', '=', $book->id];
            try {
                $userfavor = UserFavor::where($where)->findOrFail();
                $isfavor = 1;
            } catch (DataNotFoundException $e) {
            } catch (ModelNotFoundException $e) {
            }
        }

        $clicks = cache('bookClicks:' . $book->id);
        if (!$clicks) {
            $clicks = $this->bookService->getClicks($book->id, $this->prefix);
            cache('bookClicks:' . $book->id, $clicks);
        }

        View::assign([
            'book' => $book,
            'tags' => $tags,
            'start' => $start,
            'recommand' => $recommand,
            'isfavor' => $isfavor,
            'clicks' => $clicks,
            'chapters' => $chapters,
            'mobile_url' => $this->mobile_url,
            'header' => $book->book_name,
            'c_url' => $this->c_url.'/'.$book->id
        ]);
        return view($this->tpl);
    }
}