<?php


namespace app\admin\controller;


use app\model\ArticleArticle;
use app\model\ArticleChapter;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\facade\View;

class Chapters extends Base
{
    public function index()
    {
        $articleid = input('articleid');
        View::assign('articleid', $articleid);
        return \view();
    }

    public function list() {
        $articleid = input('articleid');
        $page = intval(input('page'));
        $limit = intval(input('limit'));
        $data = ArticleChapter::where('articleid', '=', $articleid)->order('chapterorder', 'desc');
        $count = $data->count();
        $chapters = $data->limit(($page - 1) * $limit, $limit)->select();

        return json([
            'code' => 0,
            'msg' => '',
            'count' => $count,
            'data' => $chapters
        ]);
    }


    public function edit()
    {
        $id = input('chapterid');
        try {
            $chapter = ArticleChapter::findOrFail(input('id'));
            if (request()->isPost()) {
                $chapter->chaptername = input('chaptername');
                $chapter->chapterorder = input('chapterorder');
                $result = $chapter->save();
                if ($result) {
                    $param = [
                        "articleid" => $chapter["articleid"],
                        "lastupdate" => time(),
                        'lastchapterid' => $id
                    ];
                    $result2 = ArticleArticle::update($param);
                    if ($result2) {
                        $this->success('修改成功');
                    } else {
                        $this->error('修改失败');
                    }
                } else {
                    $this->error('修改失败');
                }

            }
            View::assign([
                'chapter' => $chapter,
            ]);
            return view();
        } catch (DataNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (ModelNotFoundException $e) {
            abort(404, $e->getMessage());
        }
    }

    public function delete()
    {
        $id = input('chapterid');
        try {
            $chapter = ArticleChapter::findOrFail($id);
            $chapter->delete();
            return ['err' => 0, 'msg' => '删除成功'];
        } catch (DataNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (ModelNotFoundException $e) {
            abort(404, $e->getMessage());
        }

    }

    public function deleteAll()
    {
        $ids = input('ids');
        ArticleChapter::destroy($ids);
    }

}