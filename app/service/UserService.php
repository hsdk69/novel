<?php


namespace app\service;


use app\model\ArticleArticle;
use app\model\UserFavor;
use think\db\exception\ModelNotFoundException;

class UserService
{
    public function getFavors($uid, $end_point)
    {
        try {
            $where[] = ['uid', '=', $uid];
            $data = UserFavor::where($where)->order('lastupdate', 'desc')->paginate(10, false);
            $books = array();
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
                $books[] = $book->toArray();
            }
            $pages = $data->toArray();
            return [
                'books' => $books,
                'page' => [
                    'total' => $pages['total'],
                    'per_page' => $pages['per_page'],
                    'current_page' => $pages['current_page'],
                    'last_page' => $pages['last_page'],
                    'query' => request()->param()
                ]
            ];
        } catch (ModelNotFoundException $e) {
            abort(404, $e->getMessage());
        }
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