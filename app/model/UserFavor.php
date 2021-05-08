<?php


namespace app\model;


use think\Model;

class UserFavor extends Model
{
    public function getFavors($order, $where, $pagesize)
    {
        $end_point = config('seo.book_end_point');
        $data = UserFavor::where($where)->order($order)->paginate($pagesize, false);
        foreach ($data as &$favor) {
            $book = ArticleArticle::findOrFail($favor->articleid);
            if ($end_point == 'id') {
                $book['param'] = $book['articleid'];
            } else {
                $book['param'] = $book['backupname'];
            }
            $bigId = floor((double)($book['articleid'] / 1000));
            $book['cover'] = sprintf('/files/article/image/%s/%s/%ss.jpg',
                $bigId, $book['articleid'], $book['articleid']);
            $book['cate'] = Cate::where('sortid','=',$book['sortid'])->findOrFail();
            $favor['book'] = $book;
        }
        $arr = $data->toArray();
        $paged = array();
        $paged['favors'] = $arr['data'];
        $paged['page'] = [
            'total' => $arr['total'],
            'per_page' => $arr['per_page'],
            'current_page' => $arr['current_page'],
            'last_page' => $arr['last_page'],
            'query' => request()->param()
        ];
        return $paged;
    }

    public function delFavors($uid, $ids)
    {
        $where[] = ['uid', '=', $uid];
        $where[] = ['articleid', 'in', $ids];
        $favors = UserFavor::where($where)->selectOrFail();
        foreach ($favors as $favor) {
            $favor->delete();
        }
    }
}