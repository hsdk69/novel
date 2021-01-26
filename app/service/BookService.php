<?php


namespace app\service;

use app\model\ArticleArticle;
use app\model\ArticleChapter;
use app\model\UserBuy;
use think\facade\Db;

class BookService
{
    public function getPagedBooks($num, $end_point, $order = 'articleid', $where = '1=1')
    {
        $data = ArticleArticle::where($where)->with('cate')->order($order, 'desc')
            ->paginate([
                'list_rows' => $num,
                'query' => request()->param(),
            ]);
        foreach ($data as &$book) {
            if ($end_point == 'id') {
                $book['param'] = $book['articleid'];
            } else {
                $book['param'] = $book['backupname'];
            }
            $bigId = floor((double)($book['articleid'] / 1000));
            $book['cover'] = sprintf('/files/article/image/%s/%s/%ss.jpg',
                $bigId, $book['articleid'], $book['articleid']);
        }
        $books = $data->toArray();
        return [
            'books' => $books['data'],
            'page' => [
                'total' => $books['total'],
                'per_page' => $books['per_page'],
                'current_page' => $books['current_page'],
                'last_page' => $books['last_page'],
                'query' => request()->param()
            ]
        ];
    }

    public function getBooks($end_point, $order = 'lastupdate', $where = '1=1', $num = 6)
    {
        $books = ArticleArticle::with('cate')->where($where)
            ->limit($num)->order($order, 'desc')->select();
        foreach ($books as &$book) {
            if ($end_point == 'id') {
                $book['param'] = $book['articleid'];
            } else {
                $book['param'] = $book['backupname'];
            }
            $bigId = floor((double)($book['articleid'] / 1000));
            $book['cover'] = sprintf('/files/article/image/%s/%s/%ss.jpg',
                $bigId, $book['articleid'], $book['articleid']);
        }
        return $books;
    }

    public function getByCate($cate_id, $end_point, $num = 30)
    {
        $books = ArticleArticle::with('cate')->where('typeid', '=', $cate_id)
            ->limit($num)->select();
        foreach ($books as &$book) {
            if ($end_point == 'id') {
                $book['param'] = $book['articleid'];
            } else {
                $book['param'] = $book['backupname'];
            }
            $bigId = floor((double)($book['articleid'] / 1000));
            $book['cover'] = sprintf('/files/article/image/%s/%s/%ss.jpg',
                $bigId, $book['articleid'], $book['articleid']);
        }
        return $books;
    }

    public function getByAuthor($authorid, $end_point, $num = 10) {
        $books = ArticleArticle::with('cate')->where('authorid', '=', $authorid)
            ->limit($num)->select();
        foreach ($books as &$book) {
            if ($end_point == 'id') {
                $book['param'] = $book['articleid'];
            } else {
                $book['param'] = $book['backupname'];
            }
            $bigId = floor((double)($book['articleid'] / 1000));
            $book['cover'] = sprintf('/files/article/image/%s/%s/%ss.jpg',
                $bigId, $book['articleid'], $book['articleid']);
        }
        return $books;
    }

    public function getRand($num, $prefix, $end_point)
    {
        $books = Db::query('SELECT a.articleid,a.articlename,a.keywords,a.roles,a.author,a.intro,a.fullflag,b.cate_name FROM 
(SELECT ad1.articleid,articlename,keywords,roles,author,intro,fullflag
FROM ' . $prefix . 'article_article AS ad1 JOIN (SELECT ROUND(RAND() * 
((SELECT MAX(id) FROM ' . $prefix . 'article_article)-(SELECT MIN(id) FROM ' . $prefix . 'article_article))+(SELECT MIN(id) FROM ' . $prefix . 'article_article)) AS id)
 AS t2 WHERE ad1.id >= t2.id ORDER BY ad1.id LIMIT ' . $num . ') as a
 INNER JOIN ' . $prefix . 'cate as b on a.typeid = b.typeid');
        foreach ($books as &$book) {
            if ($end_point == 'id') {
                $book['param'] = $book['articleid'];
            } else {
                $book['param'] = $book['backupname'];
            }
            $bigId = floor((double)($book['articleid'] / 1000));
            $book['cover'] = sprintf('/files/article/image/%s/%s/%ss.jpg',
                $bigId, $book['articleid'], $book['articleid']);
        }
        return $books;
    }

    public function search($keyword,$end_point, $prefix)
    {
        $books = ArticleArticle::where('articlename', 'like', '%' . $keyword . '%')->select();
        foreach ($books as &$book) {
            if ($end_point == 'id') {
                $book['param'] = $book['articleid'];
            } else {
                $book['param'] = $book['backupname'];
            }
            $bigId = floor((double)($book['articleid'] / 1000));
            $book['cover'] = sprintf('/files/article/image/%s/%s/%ss.jpg',
                $bigId, $book['articleid'], $book['articleid']);
        }
        return $books;
    }

    public function getHotBooks($prefix, $end_point, $num = 10)
    {
        $books = ArticleArticle::with('cate')->order('allvisit','desc')->limit($num)->select();
        foreach ($books as &$book) {
            if ($book) {
                if ($end_point == 'id') {
                    $book['param'] = $book['articleid'];
                } else {
                    $book['param'] = $book['backupname'];
                }
                $bigId = floor((double)($book['articleid'] / 1000));
                $book['cover'] = sprintf('/files/article/image/%s/%s/%ss.jpg',
                    $bigId, $book['articleid'], $book['articleid']);
            }
        }
        return $books;
    }
}