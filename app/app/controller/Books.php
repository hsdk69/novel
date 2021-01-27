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
    protected $bookService;

    public function initialize()
    {
        parent::initialize();
        $this->bookService = app('bookService');
    }

    public function getNewest()
    {
        $newest = cache('app:newest_homepage');
        if (!$newest) {
            $newest = ArticleArticle::limit(10)->order('lastupdate', 'desc')->select();
            foreach ($newest as &$book) {
                $bigId = floor((double)($book['articleid'] / 1000));
                $book['cover'] = $this->url . sprintf('/files/article/image/%s/%s/%ss.jpg',
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
        $hot_books = cache('app:hot_books');
        if (!$hot_books) {
            $hot_books = $this->bookService->getHotBooks($this->prefix, $this->end_point);
            foreach ($hot_books as &$book) {
                $bigId = floor((double)($book['articleid'] / 1000));
                $book['cover'] = $this->url . sprintf('/files/article/image/%s/%s/%ss.jpg',
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
        $tops = cache('app:tops_homepage');
        if (!$tops) {
            $tops = ArticleArticle::where('is_top', '=', '1')->limit(10)->order('last_time', 'desc')->select();
            foreach ($tops as &$book) {
                $bigId = floor((double)($book['articleid'] / 1000));
                $book['cover'] = $this->url . sprintf('/files/article/image/%s/%s/%ss.jpg',
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
        $ends = cache('app:ends_homepage');
        if (!$ends) {
            $ends = ArticleArticle::limit(10)->order('lastupdate', 'desc')->select();
            foreach ($ends as &$book) {
                $bigId = floor((double)($book['articleid'] / 1000));
                $book['cover'] = $this->url . sprintf('/files/article/image/%s/%s/%ss.jpg',
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

    public function getupdate() {
        $startItem = input('startItem');
        $pageSize = input('pageSize');
        $data = ArticleArticle::order('last_time', 'desc');
        $count = $data->count();
        $books = $data->limit($startItem, $pageSize)->select();
        foreach ($books as &$book) {
            $bigId = floor((double)($book['articleid'] / 1000));
            $book['cover'] = $this->url . sprintf('/files/article/image/%s/%s/%ss.jpg',
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
        $redis = RedisHelper::GetInstance();
        $redis->zIncrBy($this->redis_prefix . 'hot_search:', 1, $keyword);
        $hot_search_json = $redis->zRevRange($this->redis_prefix . 'hot_search', 1, 4, true);
        $hot_search = array();
        foreach ($hot_search_json as $k => $v) {
            $hot_search[] = $k;
        }
        $books = cache('appsearchresult:' . $keyword);
        if (!$books) {
            $books = $this->bookService->search($keyword, 20);
            foreach ($books as &$book) {
                $bigId = floor((double)($book['articleid'] / 1000));
                $book['cover'] = $this->url . sprintf('/files/article/image/%s/%s/%ss.jpg',
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
                $book = ArticleArticle::with('cate')->find($id);
                $bigId = floor((double)($book['articleid'] / 1000));
                $book['cover'] = $this->url . sprintf('/files/article/image/%s/%s/%ss.jpg',
                        $bigId, $book['articleid'], $book['articleid']);
            } catch (DataNotFoundException $e) {
                return json(['success' => 0, 'msg' => '该漫画不存在']);
            } catch (ModelNotFoundException $e) {
                return json(['success' => 0, 'msg' => '该漫画不存在']);
            }
            cache('book:' . $id, $book, null, 'redis');
        }

        $redis = RedisHelper::GetInstance();
        $day = date("Y-m-d", time());
        //以当前日期为键，增加点击数
        $redis->zIncrBy('click:' . $day, 1, $book->id);

        $start = cache('bookStart:' . $id);
        if ($start == false) {
            $db = Db::query('SELECT chapterid FROM ' . $this->prefix . 'article_chapter WHERE articleid = ' . $book->articleid . ' ORDER BY chapterid LIMIT 1');
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

    public function getComments()
    {
        $book_id = input('book_id');
        $comments = cache('comments:' . $book_id);
        if (!$comments) {
            $comments = Comments::with('user')->where('book_id', '=', $book_id)
                ->order('create_time', 'desc')->limit(0, 10)->select();
            cache('comments:' . $book_id, $comments);
        }
        $result = [
            'success' => 1,
            'comments' => $comments
        ];
        return json($result);
    }

    public function getRecommend()
    {
        $articleid = input('articleid');
        try {
            $book = ArticleArticle::findOrFail($articleid);
            $recommends = cache('randBooks:' . $book->typeid);
            if (!$recommends) {
                $recommends = $this->bookService->getByCate($book->typeid, $this->end_point, 10);
                foreach ($recommends as &$book) {
                    $bigId = floor((double)($book['articleid'] / 1000));
                    $book['cover'] = $this->url . sprintf('/files/article/image/%s/%s/%ss.jpg',
                            $bigId, $book['articleid'], $book['articleid']);
                }
                cache('randBooks:' . $book->typeid, $recommends, null, 'redis');
            }
            $result = [
                'success' => 1,
                'recommends' => $recommends
            ];
            return json($result);
        } catch (DataNotFoundException $e) {
            return ['success' => 0, 'msg' => '漫画不存在'];
        } catch (ModelNotFoundException $e) {
            return ['success' => 0, 'msg' => '漫画不存在'];
        }
    }
}