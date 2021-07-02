<?php


namespace app\admin\controller;


use app\model\ArticleArticle;
use app\model\BookLogs;
use app\model\Cate;
use app\model\ArticleChapter;
use app\model\ChapterLogs;
use Overtrue\Pinyin\Pinyin;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\facade\App;
use think\facade\View;

class Books extends Base
{
    public function index()
    {
        return view();
    }

    public function list()
    {
        $where = [
            ['display', '=', intval(input('display'))]
        ];
        $page = intval(input('page'));
        $limit = intval(input('limit'));
        $data = ArticleArticle::where($where)->order('articleid', 'desc');
        $count = $data->count();
        $books = $data->limit(($page - 1) * $limit, $limit)->select();

        foreach ($books as &$book) {
            $bigId = floor((double)($book['articleid'] / 1000));
            $book['cover'] = $this->server . sprintf('/files/article/image/%s/%s/%ss.jpg',
                $bigId, $book['articleid'], $book['articleid']);
            $book['cate_name'] = Cate::where('sortid', '=', $book['sortid'])->column('cate_name');
        }
        return json([
            'code' => 0,
            'msg' => '',
            'count' => $count,
            'data' => $books
        ]);
    }

    public function edit()
    {
        $data = request()->param();
        try {
            $book = ArticleArticle::findOrFail($data['articleid']);
            if (request()->isPost()) {
                $book->lastupdate = time();
                $result = $book->save($data);
                if ($result) {
                    $cover = App::getRootPath() .'/public/files/'. $data['cover'];
                    $bigId = floor((double)($book['articleid'] / 1000));
                    $dir = App::getRootPath().'/public/files/' . sprintf('/article/image/%s/%s/',$bigId, $book['articleid']);
                    if (!is_dir($dir))
                    {
                        mkdir($dir, 0777, true);
                    }
                    $filename = App::getRootPath().'/public/files/' . sprintf('/article/image/%s/%s/%ss.jpg',
                            $bigId, $book['articleid'], $book['articleid']);
                    copy($cover, $filename);
                    unlink($cover);
                    return json(['err' =>0,'msg'=>'修改成功']);
                } else {
                    return json(['err' =>1,'msg'=>'修改失败']);
                }
            } else {
                $cates = Cate::select();
                View::assign('cates', $cates);
                return view();
            }
        } catch (ModelNotFoundException $e) {
            abort(404);
        }
    }

    public function upload()
    {
        if (is_null(request()->file())) {
            return json([
                'code' => 1
            ]);
        } else {
            $cover = request()->file('file');
            $dir = 'article/tmp';
            $savename =str_replace ( '\\', '/',
                \think\facade\Filesystem::disk('public')->putFile($dir, $cover));
            return json([
                'code' => 0,
                'msg' => '',
                'img' => $savename
            ]);
        }
    }

    public function search()
    {
        $name = input('articlename');
        $where = [
            ['articlename', 'like', '%' . $name . '%'],
            ['display', '=', 0]
        ];
        $page = intval(input('page'));
        $limit = intval(input('limit'));
        $data = ArticleArticle::where($where)->order('articleid', 'desc');
        $count = $data->count();
        $books = $data->limit(($page - 1) * $limit, $limit)->select();

        foreach ($books as &$book) {
            $bigId = floor((double)($book['articleid'] / 1000));
            $book['cover'] = sprintf('/files/article/image/%s/%s/%ss.jpg',
                $bigId, $book['articleid'], $book['articleid']);
        }
        return json([
            'code' => 0,
            'msg' => '',
            'count' => $count,
            'data' => $books
        ]);
    }

    public function disable()
    {
        $articleid = input('articleid');
        if (isset($articleid)) {
            try {
                $book = ArticleArticle::findOrFail($articleid);
                $book->display = 1;
                $result = $book->save();
                if ($result) {
                    return json(['err' => 0, 'msg' => '下架成功']);
                }
            } catch (ModelNotFoundException $e) {
                return json(['err' => 0, 'msg' => $e->getMessage()]);
            }
        }
        return json(['err' => 0, 'msg' => '找不到该小说']);
    }

    public function enable()
    {
        $articleid = input('articleid');
        if (isset($articleid)) {
            try {
                $book = ArticleArticle::findOrFail($articleid);
                $book->display = 0;
                $result = $book->save();
                if ($result) {
                    return json(['err' => 0, 'msg' => '上架成功']);
                }
            } catch (ModelNotFoundException $e) {
                return json(['err' => 0, 'msg' => $e->getMessage()]);
            }
        }
        return json(['err' => 0, 'msg' => '找不到该小说']);
    }

    public function disabled()
    {
        return view();
    }

    public function delete()
    {
        $id = input('articleid');
        try {
            $book = ArticleArticle::findOrFail($id);
            $chapters = ArticleChapter::where('articleid', '=', $id)->select(); //按漫画id查找所有章节
            foreach ($chapters as $chapter) {
                $chapter->delete(); //删除章节
                $chapterLog = ChapterLogs::where('chapter_id','=',$chapter->chapterid)->find();
                if ($chapterLog) {
                    $chapterLog->delete();
                }
            }
            $book->delete();
            $bookLog = BookLogs::where('book_id','=',$book->articleid)->find();
            if ($bookLog) {
                $bookLog->delete();
            }
            return json(['err' => 0, 'msg' => '删除成功']);

        } catch (ModelNotFoundException $e) {
            abort(404, $e->getMessage());
            return json(['err' => 1, 'msg' => $e->getMessage()]);
        }
    }

    protected function convert($str)
    {
        $pinyin = new Pinyin();
        return $pinyin->permalink($str, '');
    }

}