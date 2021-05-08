<?php


namespace app\model;

use think\model\concern\SoftDelete;
use think\Model;

class ArticleArticle extends Model
{
    protected $pk = 'articleid';

    public static function onBeforeUpdate($book)
    {
        cache('book:' . $book->articleid, null);
        cache('bookInCate:' . $book->articleid, null);
    }

    public static function onAfterInsert($user)
    {
        cache('newestHomepage', null);
        cache('endsHomepage', null);
    }

    public function chapters()
    {
        return $this->hasMany(ArticleChapter::class, 'articleid', 'articleid');
    }

    public function cate()
    {
        return $this->hasOne(Cate::class, 'sortid', 'sortid');
    }

    public function setArticleNameAttr($value)
    {
        return trim($value);
    }

    public function getBooks($order, $where, $num)
    {
        $end_point = config('seo.book_end_point');
        if ($num == 0) {
            $books = ArticleArticle::with('cate')->where($where)->order($order)->select();
        } else {
            if (strlen($num) == 3) {
                $arr = explode(',',$num);
                $books = ArticleArticle::with('cate')->where($where)
                    ->limit($arr[0],$arr[1])->order($order)->select();
            } else {
                $books = ArticleArticle::with('cate')->where($where)->limit($num)->order($order)->select();
            }
        }

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

    public function getPagedBooks($order, $where, $pagesize)
    {
        $end_point = config('seo.book_end_point');
        $where = str_replace("sortid='-1'", 'true', $where);
        $where = str_replace("words<='-1'", 'true', $where);
        $where = str_replace("fullflag='-1'", 'true', $where);
        $data = ArticleArticle::where($where)->with('cate')->order($order)
            ->paginate([
                'list_rows' => $pagesize,
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
        $arr = $data->toArray();
        $pagedBooks = array();
        $pagedBooks['books'] = $arr['data'];
        $pagedBooks['page'] = [
            'total' => $arr['total'],
            'per_page' => $arr['per_page'],
            'current_page' => $arr['current_page'],
            'last_page' => $arr['last_page'],
            'query' => request()->param()
        ];
        return $pagedBooks;
    }
}