<?php


namespace app\service;

use app\model\ArticleArticle;
use app\model\Cate;
use app\model\User;
use app\model\UserFavor;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;

class UserService
{
    public function getAdminPagedUsers($status, $where, $orderBy, $order)
    {
        if ($status == 1) { //正常用户
            $data = User::where($where)->order($orderBy, $order);
        } else {
            $data = User::onlyTrashed()->where($where)->order($orderBy, $order);
        }
        $financeService = new FinanceService();
        $page = config('page.back_end_page');
        $users = $data->paginate(
            [
                'list_rows'=> $page,
                'query' => request()->param(),
                'var_page' => 'page',
            ])->each(function ($item, $key) use($financeService){
            $item['balance'] = $financeService->getBalance($item->id);
        });
        return [
            'users' => $users,
            'count' => $data->count()
        ];
    }

    public function getFavors($uid, $end_point)
    {
        try {
            $where[] = ['uid', '=', $uid];
            $data = UserFavor::where($where)->order('create_time', 'desc')->paginate(5, false);
            $books = array();
            foreach ($data as &$favor) {
                $book = ArticleArticle::findOrFail($favor->articleid);
                $cate = Cate::findOrFail($book->typeid);
                $book['cate'] = $cate;
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
        try {
            $favor = UserFavor::where($where)->findOrFail();
            $favor->delete();
        } catch (ModelNotFoundException $e) {
            abort(404, $e->getMessage());
        }
     }
}