<?php


namespace app\app\controller;


use app\common\RedisHelper;
use app\model\Author;
use app\model\ArticleArticle;
use app\model\Comments;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;

class Books extends Base
{
    public function getNewest()
    {
        $num = input('num');
        $newest = cache('app:newest_homepage');
        if (!$newest) {
            $newest = ArticleArticle::limit($num)->order('lastupdate', 'desc')->select();
            foreach ($newest as &$book) {
                $bigId = floor((double)($book['articleid'] / 1000));
                $book['cover'] = $this->server . sprintf('/files/article/image/%s/%s/%ss.jpg',
                        $bigId, $book['articleid'], $book['articleid']);
            }
            cache('newest_homepage', $newest, null, 'redis');
        }
        $result = [
            'success' => 1,
            'newest' => $newest
        ];
        return json($result);
    }

    public function getHot()
    {
        $num = input('num');
        $hot_books = cache('app:hot_books');
        if (!$hot_books) {
            $hot_books = ArticleArticle::limit($num)->order('allvisit', 'desc')->select();
            foreach ($hot_books as &$book) {
                $bigId = floor((double)($book['articleid'] / 1000));
                $book['cover'] = $this->server . sprintf('/files/article/image/%s/%s/%ss.jpg',
                        $bigId, $book['articleid'], $book['articleid']);
            }
            cache('hot_books', $hot_books, null, 'redis');
        }
        $result = [
            'success' => 1,
            'hots' => $hot_books
        ];
        return json($result);
    }

    public function getTops()
    {
        $num = input('num');
        $tops = cache('app:tops_homepage');
        if (!$tops) {
            $tops = ArticleArticle::where('is_top', '=', '1')->limit($num)->order('lastupdate', 'desc')->select();
            foreach ($tops as &$book) {
                $bigId = floor((double)($book['articleid'] / 1000));
                $book['cover'] = $this->server . sprintf('/files/article/image/%s/%s/%ss.jpg',
                        $bigId, $book['articleid'], $book['articleid']);
            }
            cache('tops_homepage', $tops, null, 'redis');
            $result = [
                'success' => 1,
                'tops' => $tops
            ];
            return json($result);
        }
    }

    public function getEnds()
    {
        $num = input('num');
        $ends = cache('app:ends_homepage');
        if (!$ends) {
            $ends = ArticleArticle::limit($num)->order('lastupdate', 'desc')->select();
            foreach ($ends as &$book) {
                $bigId = floor((double)($book['articleid'] / 1000));
                $book['cover'] = $this->server . sprintf('/files/article/image/%s/%s/%ss.jpg',
                        $bigId, $book['articleid'], $book['articleid']);
            }
            cache('ends_homepage', $ends, null, 'redis');
        }
        $result = [
            'success' => 1,
            'ends' => $ends
        ];
        return json($result);
    }

    public function getupdate()
    {
        $startItem = input('startItem');
        $pageSize = input('pageSize');
        $data = ArticleArticle::order('lastupdate', 'desc');
        $count = $data->count();
        $books = $data->limit($startItem, $pageSize)->select();
        foreach ($books as &$book) {
            $bigId = floor((double)($book['articleid'] / 1000));
            $book['cover'] = $this->server . sprintf('/files/article/image/%s/%s/%ss.jpg',
                    $bigId, $book['articleid'], $book['articleid']);
        }
        $result = [
            'success' => 1,
            'books' => $books,
            'count' => $count
        ];
        return json($result);
    }

    public function search()
    {
        $keyword = input('keyword');
        $num = input('num');
        $redis = RedisHelper::GetInstance();
        $redis->zIncrBy($this->redis_prefix . 'hot_search:', 1, $keyword);
        $hot_search_json = $redis->zRevRange($this->redis_prefix . 'hot_search', 1, 4, true);
        $hot_search = array();
        foreach ($hot_search_json as $k => $v) {
            $hot_search[] = $k;
        }
        $books = cache('appsearchresult:' . $keyword);
        if (!$books) {
            $books = ArticleArticle::where('articlename', 'like', '%' . $keyword . '%')
                ->limit($num)->select();
            foreach ($books as &$book) {
                $bigId = floor((double)($book['articleid'] / 1000));
                $book['cover'] = $this->server . sprintf('/files/article/image/%s/%s/%ss.jpg',
                        $bigId, $book['articleid'], $book['articleid']);
            }
            cache('appsearchresult:' . $keyword, $books, null, 'redis');
        }
        $result = [
            'success' => 1,
            'books' => $books,
            'count' => count($books),
            'hot_search' => $hot_search
        ];
        return json($result);
    }

    public function detail()
    {
        $id = input('id');
        $book = cache('app:book:' . $id);
        if ($book == false) {
            try {
                $book = ArticleArticle::with('cate')->findOrFail($id);
                $bigId = floor((double)($book['articleid'] / 1000));
                $book['cover'] = $this->server . sprintf('/files/article/image/%s/%s/%ss.jpg',
                        $bigId, $book['articleid'], $book['articleid']);
            } catch (DataNotFoundException $e) {
                return json(['success' => 0, 'msg' => '该小说不存在']);
            } catch (ModelNotFoundException $e) {
                return json(['success' => 0, 'msg' => '该小说不存在']);
            }
            cache('book:' . $id, $book, null, 'redis');
        }

        $redis = RedisHelper::GetInstance();
        $day = date("Y-m-d", time());
        //以当前日期为键，增加点击数
        $redis->zIncrBy('click:' . $day, 1, $book->articleid);

        $start = cache('bookStart:' . $id);
        if ($start == false) {
            $db = Db::query('SELECT chapterid FROM ' . $this->prefix . 'article_chapter WHERE articleid = '
                . $book->articleid . ' and chaptertype=0 ORDER BY chapterid LIMIT 1');
            $start = $db ? $db[0]['chapterid'] : -1;
            cache('bookStart:' . $id, $start, null, 'redis');
        }

        $book['start'] = $start;
        $result = [
            'success' => 1,
            'book' => $book
        ];
        return json($result);
    }

    public function getRecommend()
    {
        $num = input('num');
        $articleid = input('articleid');
        try {
            $book = ArticleArticle::findOrFail($articleid);
            $recommends = cache('randBooks:' . $book->sortid);
            if (!$recommends) {
                $recommends = ArticleArticle::with('cate')->where('sortid', '=', $book->sortid)
                    ->limit($num)->select();
                foreach ($recommends as &$book) {
                    $bigId = floor((double)($book['articleid'] / 1000));
                    $book['cover'] = $this->server . sprintf('/files/article/image/%s/%s/%ss.jpg',
                            $bigId, $book['articleid'], $book['articleid']);
                }
                cache('randBooks:' . $book->sortid, $recommends, null, 'redis');
            }
            $result = [
                'success' => 1,
                'recommends' => $recommends
            ];
            return json($result);
        } catch (ModelNotFoundException $e) {
            return ['success' => 0, 'msg' => '小说不存在'];
        }
    }
}