<?php


namespace app\admin\controller;


use app\model\ArticleArticle;
use app\model\Comments;
use app\model\SystemUsers;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\facade\View;

class Comment extends Base
{
    public function index()
    {
        return view();
    }

    public function list()
    {
        $page = intval(input('page'));
        $limit = intval(input('limit'));
        $map = array();
        $uname = input('uname');
        if ($uname) {
            $map[] = ['uname', '=', $uname];
        }

        $articlename = input('articlename');
        if ($articlename) {
            $map[] = ['articlename', '=', $articlename];
        }
        $data = Comments::where($map);
        $count = $data->count();
        $comments = $data->order('id', 'desc')
            ->limit(($page - 1) * $limit, $limit)->select();
        foreach ($comments as &$comment)
        {
            $comment['user'] = SystemUsers::where('uid','=',$comment['uid'])->column('uname');
            $comment['book'] = ArticleArticle::where('articleid','=',$comment['articleid'])->column('articlename');
        }
        return json([
            'code' => 0,
            'msg' => '',
            'count' => $count,
            'data' => $comments
        ]);
    }

    public function delete(){
        $id = input('id');
        try {
            $comment = Comments::findOrFail($id);
            $result = $comment->delete();
            if ($result) {
                return ['err' => '0','msg' => '删除成功'];
            } else {
                return ['err' => '1','msg' => '删除失败'];
            }
        } catch (DataNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (ModelNotFoundException $e) {
            abort(404, $e->getMessage());
        }
    }

    public function deleteAll($ids){
        Comments::destroy($ids);
    }
}