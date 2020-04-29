<?php


namespace app\service;

use app\admin\controller\Base;
use app\model\Book;
use app\model\Chapter;
use app\model\UserBuy;
use mysql_xdevapi\Expression;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;

class BookService
{
    public function getPagedBooksAdmin($status, $where = '1=1')
    {
        if ($status == 1) {
            $data = Book::with('cate')->where($where);
        } else {
            $data = Book::onlyTrashed()->with('cate')->where($where);
        }
        $page = config('page.back_end_page');
        $books = $data->order('id', 'desc')
            ->paginate(
                [
                    'list_rows'=> $page,
                    'query' => request()->param(),
                    'var_page' => 'page',
                ]);
        foreach ($books as &$book) {
            $book['chapter_count'] = Chapter::where('book_id','=', $book->id)->count();
        }
        return [
            'books' => $books,
            'count' => $data->count()
        ];
    }

//    public function getBooksById($ids)
//    {
//        if (empty($ids) || strlen($ids) <= 0) {
//            return [];
//        }
//        $exp = new Expression('field(id,' . $ids . ')');
//        try {
//            $books = Book::where('id', 'in', $ids)->with(['author,chapters'])->order($exp)->selectOrFail();
//            foreach ($books as &$book) {
//                $book['chapter_count'] = count($book->chapters);
//            }
//            return $books;
//        } catch (DataNotFoundException $e) {
//            return [];
//        } catch (ModelNotFoundException $e) {
//            return [];
//        } catch (DbException $e) {
//            return [];
//        }
//    }

    public function getPagedBooks($num, $end_point, $order = 'id', $where = '1=1')
    {
        $data = Book::where($where)->with('cate')->order($order, 'desc')
            ->paginate([
                'list_rows'=> $num,
                'query' => request()->param(),
            ]);
        foreach ($data as &$book) {
            //$book['chapter_count'] = Chapter::where('book_id','=',$book->id)->count();
            if ($end_point == 'id') {
                $book['param'] = $book['id'];
            } else {
                $book['param'] = $book['unique_id'];
            }
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

    public function getBooks($end_point, $order = 'last_time', $where = '1=1', $num = 6 )
    {
        $books = Book::with(['cate','author'])->where($where)
            ->limit($num)->order($order, 'desc')->select();
        foreach ($books as &$book) {
            //$book['chapter_count'] = Chapter::where('book_id','=',$book->id)->count();
            $book['taglist'] = explode('|', $book->tags);
            if ($end_point == 'id') {
                $book['param'] = $book['id'];
            } else {
                $book['param'] = $book['unique_id'];
            }
//            $last_chapter = Chapter::where('book_id','=',$book['id'])->order('chapter_order','desc')->limit(1)->find();
//            $book['last_chapter'] = $last_chapter;
        }
        return $books;
    }

    public function getMostChargedBook($end_point)
    {
        $data = UserBuy::with(['book' => ['author']])->field('book_id,sum(money) as sum')
            ->group('book_id')->select();
        if (count($data) > 0) {
            foreach ($data as &$item) {
                $book = $item['book'];
                //$book['chapter_count'] = Chapter::where('book_id','=',$book->id)->count();
                $book['taglist'] = explode('|', $item['book']['tags']);
                $item['book'] = $book;
                if ($end_point == 'id') {
                    $book['param'] = $book['id'];
                } else {
                    $book['param'] = $book['unique_id'];
                }
            }
            $arr = $data->toArray();
            array_multisort(array_column($arr, 'sum'), SORT_DESC, $arr);
            return $arr;
        } else {
            return [];
        }
    }

    public function getRecommand($cate_id, $end_point, $num = 10)
    {
        $books = Book::whereOr('cate_id','=',$cate_id)->limit($num)->select();
        foreach ($books as &$book) {
            //$book['chapter_count'] = Chapter::where('book_id', '=', $book['id'])->count();
            if ($end_point == 'id') {
                $book['param'] = $book['id'];
            } else {
                $book['param'] = $book['unique_id'];
            }
        }
        return $books;
    }

    public function getByCate($cate_id, $end_point)
    {
        $books = Book::with('cate')->where('cate_id', '=', $cate_id)->select();
        foreach ($books as &$book) {
            //$book['chapter_count'] = Chapter::where('book_id', '=', $book['id'])->count();
            if ($end_point == 'id') {
                $book['param'] = $book['id'];
            } else {
                $book['param'] = $book['unique_id'];
            }
        }
        return $books;
    }

    public function getByAuthor($id, $end_point) {
        $books = Book::where('author_id', '=', $id)->select();
        foreach ($books as &$book) {
            //$book['chapter_count'] = Chapter::where('book_id', '=', $book['id'])->count();
            if ($end_point == 'id') {
                $book['param'] = $book['id'];
            } else {
                $book['param'] = $book['unique_id'];
            }
        }
        return $books;
    }

    public function getRand($num, $prefix, $end_point)
    {
        $books = Db::query('SELECT a.id,a.book_name,a.summary,a.end,b.author_name FROM 
(SELECT ad1.id,book_name,summary,end,author_id,cover_url
FROM ' . $prefix . 'book AS ad1 JOIN (SELECT ROUND(RAND() * ((SELECT MAX(id) FROM ' . $prefix . 'book)-(SELECT MIN(id) FROM ' . $prefix . 'book))+(SELECT MIN(id) FROM ' . $prefix . 'book)) AS id)
 AS t2 WHERE ad1.id >= t2.id ORDER BY ad1.id LIMIT ' . $num . ') as a
 INNER JOIN author as b on a.author_id = b.id');
        foreach ($books as &$book) {
            //$book['chapter_count'] = Chapter::where('book_id', '=', $book['id'])->count();
            if ($end_point == 'id') {
                $book['param'] = $book['id'];
            } else {
                $book['param'] = $book['unique_id'];
            }
        }
        return $books;
    }

    public function search($keyword, $prefix)
    {
        return Db::query(
            "select * from " . $prefix . "book where delete_time=0 and match(book_name,summary,author_name,nick_name,role_name) 
            against ('" . $keyword . "' IN NATURAL LANGUAGE MODE)"
        );

//        $map[] = ['delete_time','=',0];
//        $map[] = ['book_name','like','%'.$keyword.'%'];
//        return Book::where($map)->select();
    }

    public function getHotBooks($prefix, $end_point, $date = '1900-01-01', $num = 10)
    {
        $data = Db::query("SELECT book_id,SUM(clicks) as clicks FROM " . $prefix . "clicks WHERE cdate>=:cdate
 GROUP BY book_id ORDER BY clicks DESC LIMIT :num", ['cdate' => $date, 'num' => $num]);
        $books = array();
        foreach ($data as $val) {
            $book = Book::with('cate')->find($val['book_id']);
            if($book) {
                $book['chapter_count'] = Chapter::where('book_id','=',$book->id)->count();
                $book['taglist'] = explode('|', $book->tags);
                $book['clicks'] = $val['clicks'];
                if ($end_point == 'id') {
                    $book['param'] = $book['id'];
                } else {
                    $book['param'] = $book['unique_id'];
                }
                array_push($books, $book);
            }

        }
        return $books;
    }

    public function getClicks($book_id, $prefix)
    {
        $clicks = Db::query("SELECT click FROM(SELECT book_id,
 sum(clicks) as click FROM " . $prefix . "clicks GROUP BY book_id) as a WHERE book_id=:book_id", ['book_id' => $book_id]);
        if (empty($clicks)) {
            return 0;
        }
        return $clicks[0]['click'];
    }
}