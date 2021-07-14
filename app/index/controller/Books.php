<?php


namespace app\index\controller;


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
                $map[] = ['articleid', '=', $book['articleid']];
                $map[] = ['chaptertype', '=', 0];
                $book['chapters'] = ArticleChapter::where($map)->select();
            } catch (ModelNotFoundException $e) {
                abort(404, $e->getMessage());
            }
            cache('book:' . $id, $book, null, 'redis');
        }
        $ip = request()->ip();
        if (empty(cookie('click:' . $ip))) {
            $book->hits = $book->allvisit + 1;
            $book->mhits = $book->monthvisit + 1;
            $book->whits = $book->weekvisit + 1;
            $book->dhits = $book->dayvisit + 1;
            $book->save();
            cookie('click:' . $ip, $ip);
        }

        $start = cache('bookStart:' . $id);
        if ($start == false) {
            $db = Db::query('SELECT chapterid FROM ' . $this->prefix . 'article_chapter WHERE articleid = '
                . $book->articleid . ' and chaptertype=0 ORDER BY chapterid LIMIT 1');
            $start = $db ? $db[0]['chapterid'] : -1;
            cache('bookStart:' . $id, $start, null, 'redis');
        }

        View::assign([
            'book' => $book,
            'start' => $start,
        ]);
        return view($this->tpl);
    }

    private function getComments($articleid)
    {
        $comments = cache('comments:' . $articleid);
        if (!$comments) {
            $comments = Comments::with('user')->where('articleid', '=', $articleid)
                ->order('create_time', 'desc')->limit(0, 5)->select();
            cache('comments:' . $articleid, $comments);
        }
        return $comments;
    }

    public function commentadd()
    {
        $articleid = input('articleid');
        $flag = cookie('comment_lock:' . $this->uid);
        if (!empty($flag)) {
            return json(['msg' => '每10秒只能评论一次', 'err' => 1]);
        } else {
            $comment = new Comments();
            $comment->uid = $this->uid;
            $comment->$articleid = $articleid;
            $comment->content = strip_tags(input('comment'));
            $result = $comment->save();
            if ($result) {
                cookie('comment_lock:' . $this->uid, 1, 10); //加10秒锁
                cache('comments:' . $articleid, null);
                return json(['msg' => '评论成功', 'err' => 0]);
            } else {
                return json(['msg' => '评论失败', 'err' => 1]);
            }
        }
    }
}