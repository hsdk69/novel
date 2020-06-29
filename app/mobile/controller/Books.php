<?php


namespace app\mobile\controller;


use app\common\RedisHelper;
use app\model\Book;
use app\model\Chapter;
use app\model\Comments;
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
        $book = cache('book:' . $id);
        if ($book == false) {
            try {
                $book_end_point = config('seo.book_end_point');
                if ($book_end_point == 'id') {
                    $book = Book::with(['chapters' => function ($query) {
                        $query->order('chapter_order');
                    }])->findOrFail($id);
                } else {
                    $book = Book::with(['chapters' => function ($query) {
                        $query->order('chapter_order');
                    }])->where('unique_id', '=', $id)->findOrFail();
                }
            } catch (DataNotFoundException $e) {
                abort(404, $e->getMessage());
            } catch (ModelNotFoundException $e) {
                abort(404, $e->getMessage());
            }
            cache('book:' . $id, $book, null, 'redis');
        }

        $redis = RedisHelper::GetInstance();
        $day = date("Y-m-d", time());
        //以当前日期为键，增加点击数
        $redis->zIncrBy('click:' . $day, 1, $book->id);


        $recommand = cache('randBooks:' . $book->cate_id);
        if (!$recommand) {
            $recommand = $this->bookService->getRecommand($book->cate_id, $this->end_point, 3);
            cache('randBooks:' . $book->tags, $recommand, null, 'redis');
        }
        $param = config('seo.tag_end_point');
        $tags = cache('tags:'.$id);
        if (!$tags) {
            //$tags = Tags::where('id', 'in', $tag->similar)->select();
            $tags = Db::query(
                "select * from " . $this->prefix . "tags where match(tag_name) 
            against ('" . $book->book_name. "') LIMIT 10");
            foreach ($tags as &$t) {
                if ($param == 'id') {
                    $t['param'] = $t['id'];
                } else if ($param == 'pinyin') {
                    $t['param'] = $t['pinyin'];
                } else {
                    $t['param'] = $t['jianpin'];
                }
            }
            cache('tags:'.$id, $tags, null, 'redis');
        }

        $start = cache('bookStart:' . $id);
        if ($start == false) {
            $db = Db::query('SELECT id FROM ' . $this->prefix . 'chapter WHERE book_id = ' . $book->id . ' ORDER BY id LIMIT 1');
            $start = $db ? $db[0]['id'] : -1;
            cache('bookStart:' . $id, $start, null, 'redis');
        }

        $comments = $this->getComments($book->id);

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
            'comments' => $comments,
            'clicks' => $clicks,
            'header' => $book->book_name,
            'c_url' => $this->c_url.'/'.$book->id
        ]);
        return view($this->tpl);
    }

    public function addfavor()
    {
        if (request()->isPost()) {
            if (is_null($this->uid)) {
                return json(['err' => 1, 'msg' => '用户未登录']);
            }
            $redis = RedisHelper::GetInstance();
            if ($redis->exists('favor_lock:' . $this->uid)) { //如果存在锁
                return json(['err' => 1, 'msg' => '操作太频繁']);
            } else {
                $redis->set('favor_lock:' . $this->uid, 1, 3); //写入锁
                $val = input('val');
                $book_id = input('book_id');

                $where[] = ['book_id', '=', $book_id];
                $where[] = ['user_id', '=', $this->uid];
                try {
                    UserFavor::where($where)->findOrFail();
                    return json(['err' => 1, 'msg' => '已加入书架']); //isfavor表示已收藏
                } catch (DataNotFoundException $e) {
                } catch (ModelNotFoundException $e) {
                    $userFaver = new UserFavor();
                    $userFaver->book_id = $book_id;
                    $userFaver->user_id = $this->uid;
                    $userFaver->save();
                    return json(['err' => 0, 'msg' => '成功加入书架']); //isfavor表示已收藏
                }
            }
        }
        return json(['err' => 1, 'msg' => '不是post请求']);
    }

    private function getComments($book_id)
    {
        $comments = cache('comments:' . $book_id);
        if (!$comments) {
            $comments = Comments::with('user')->where('book_id', '=', $book_id)
                ->order('create_time', 'desc')->limit(0, 5)->select();
            cache('comments:' . $book_id, $comments);
        }
        return $comments;
    }

    public function commentadd()
    {
        $book_id = input('book_id');
        $redis = RedisHelper::GetInstance();
        if ($redis->exists('comment_lock:' . $this->uid)) {
            return json(['msg' => '每10秒只能评论一次', 'err' => 1]);
        } else {
            $comment = new Comments();
            $comment->user_id = $this->uid;
            $comment->book_id = $book_id;
            $comment->content = strip_tags(input('comment'));
            $result = $comment->save();
            if ($result) {
                $redis->set('comment_lock:' . $this->uid, 1, 10); //加10秒锁
                cache('comments:' . $book_id, null);
                return json(['msg' => '评论成功', 'err' => 0]);
            } else {
                return json(['msg' => '评论失败', 'err' => 1]);
            }
        }
    }
}