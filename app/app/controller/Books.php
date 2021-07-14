<?php


namespace app\app\controller;


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
            $tops = ArticleArticle::where('toptime', '>', 0)->limit($num)->order('lastupdate', 'desc')->select();
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
            $ends = ArticleArticle::where('fullflag','=',1)->limit($num)->order('lastupdate', 'desc')->select();
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
        $num = input('num');
        $newest = cache('app:newest_homepage');
        if (!$newest) {
            $newest = ArticleArticle::limit($num)->order('articleid', 'desc')->select();
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

    public function getRankList()
    {
        $startItem = input('startItem');
        $pageSize = input('pageSize');
        $type = input('type');
        switch ($type) {
            case 'hot':
                $data = ArticleArticle::order('allvisit', 'desc'); break;
            case 'newest':
                $data = ArticleArticle::order('articleid', 'desc'); break;
            case 'ends':
                $data = ArticleArticle::where('fullflag','=',1)->order('lastupdate', 'desc'); break;
            case 'tops':
                $data = ArticleArticle::where('toptime', '>', 0)->order('lastupdate', 'desc'); break;
            case 'update':
                ArticleArticle::order('lastupdate', 'desc'); break;
            default:
                ArticleArticle::order('lastupdate', 'desc'); break;
        }
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