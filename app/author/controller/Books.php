<?php


namespace app\author\controller;

use app\model\ArticleArticle;
use app\model\ArticleChapter;
use app\model\Cate;
use Overtrue\Pinyin\Pinyin;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\HttpException;
use think\facade\App;
use think\facade\View;

class Books extends Base
{
    public function list()
    {
        return view();
    }

    public function getlist() {
        $page = intval(input('page'));
        $limit = intval(input('limit'));
        $author_id = session('xwx_author_id');
        $map[] = ['authorid', '=', $author_id];
        $map[] = ['display', '=', 1];
        $data = ArticleArticle::where($map)->order('articleid', 'desc');
        $count = $data->count();
        $books = $data->limit(($page - 1) * $limit, $limit)->select();
        foreach ($books as &$book) {
            $bigId = floor((double)($book['articleid'] / 1000));
            $book['cover'] = $this->server . sprintf('/files/article/image/%s/%s/%ss.jpg',
                    $bigId, $book['articleid'], $book['articleid']);
        }
        return json([
            'code' => 0,
            'msg' => '',
            'count' => $count,
            'data' => $books
        ]);
    }

    public function create() {
        if (request()->isPost()) {
            $book = new ArticleArticle();
            $data = $this->request->param();
            $book->authorid = $this->uid;
            $book->author = $this->author_name;
            $book->lastupdate = time();
            $book->display = 1;
            $str = $this->convert($data['articlename']); //生成标识
            $c = (int)ArticleArticle::where('backupname','=',$str)->count();
            if ($c > 0) {
                $data['backupname'] = md5(time() . mt_rand(1,1000000)); //如果已经存在相同标识，则生成一个新的随机标识
            } else {
                $data['backupname'] = $str;
            }
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
                return json(['err' =>0,'msg'=>'添加成功']);
            } else {
                return json(['err' =>1,'msg'=>'添加失败']);
            }
        }
        $cates = Cate::select();
        View::assign('cates', $cates);
        return view();
    }

    public function edit() {
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

    public function upload() {
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

    public function delete()
    {
        $id = input('articleid');
        try {
            $book = ArticleArticle::findOrFail($id);
            $chapters = ArticleChapter::where('articleid', '=', $id)->select(); //按漫画id查找所有章节
            foreach ($chapters as $chapter) {
                $chapter->delete(); //删除章节
            }
            $result = $book->delete();
            if ($result) {
                return json(['err' => 0, 'msg' => '删除成功']);
            } else {
                return json(['err' => 1, 'msg' => '删除失败']);
            }

        } catch (DataNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (ModelNotFoundException $e) {
            abort(404, $e->getMessage());
        }
    }

    protected function convert($str){
        $pinyin = new Pinyin();
        $name_format = config('seo.name_format');
        switch ($name_format) {
            case 'pure':
                $arr = $pinyin->convert($str);
                $str = implode('', $arr);
                break;
            case 'abbr':
                $str = $pinyin->abbr($str);break;
            default:
                $str = $pinyin->convert($str);break;
        }
        return $str;
    }
}