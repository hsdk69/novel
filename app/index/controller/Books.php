<?php


namespace app\index\controller;


use app\common\RedisHelper;
use app\model\ArticleArticle;
use app\model\ArticleChapter;
use app\model\Comments;
use app\model\UserFavor;
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
                    $book = ArticleArticle::with('cate')->where('articleid', '=', $id)->findOrFail();
                } else {
                    $book = ArticleArticle::with('cate')->where('backupname', '=', $id)->findOrFail();
                }
                $bigId = floor((double)($book['articleid'] / 1000));
                $book['cover'] = sprintf('/files/article/image/%s/%s/%ss.jpg',
                    $bigId, $book['articleid'], $book['articleid']);
                $book['chapters'] = ArticleChapter::where('articleid','=',$book['articleid'])->select();
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


        $recommand = cache('randBooks:' . $book->typeid);
        if (!$recommand) {
            $recommand = $this->bookService->getByCate($book->typeid, $this->end_point, 10);
            cache('randBooks:' . $book->typeid, $recommand, null, 'redis');
        }


        $start = cache('bookStart:' . $id);
        if ($start == false) {
            $db = Db::query('SELECT chapterid FROM ' . $this->prefix . 'article_chapter WHERE articleid = ' . $book->articleid . ' ORDER BY chapterid LIMIT 1');
            $start = $db ? $db[0]['chapterid'] : -1;
            cache('bookStart:' . $id, $start, null, 'redis');
        }

        $comments = $this->getComments($book->articleid);

        $isfavor = 0;
        if (isset($this->uid)) {
            $where[] = ['uid', '=', $this->uid];
            $where[] = ['articleid', '=', $book->articleid];
            try {
                $userfavor = UserFavor::where($where)->findOrFail();
                $isfavor = 1;
            } catch (ModelNotFoundException $e) {
                $isfavor = 0;
            }
        }
        View::assign([
            'book' => $book,
            'start' => $start,
            'recommand' => $recommand,
            'isfavor' => $isfavor,
            'comments' => $comments,
        ]);
        return view($this->tpl);
    }

    private function getComments($articleid, $num = 5)
    {
        $comments = cache('comments:' . $articleid);
        if (!$comments) {
            $comments = Comments::with('user')->where('articleid', '=', $articleid)
                ->order('create_time', 'desc')->limit(0, $num)->select();
            cache('comments:' . $articleid, $comments);
        }
        return $comments;
    }

    public function commentadd()
    {
        $articleid = input('articleid');
        $redis = RedisHelper::GetInstance();
        if ($redis->exists('comment_lock:' . $this->uid)) {
            return json(['msg' => '每10秒只能评论一次', 'err' => 1]);
        } else {
            $comment = new Comments();
            $comment->uid = $this->uid;
            $comment->$articleid = $articleid;
            $comment->content = strip_tags(input('comment'));
            $result = $comment->save();
            if ($result) {
                $redis->set('comment_lock:' . $this->uid, 1, 10); //加10秒锁
                cache('comments:' . $articleid, null);
                return json(['msg' => '评论成功', 'err' => 0]);
            } else {
                return json(['msg' => '评论失败', 'err' => 1]);
            }
        }
    }
}