<?php
namespace app\api\controller;

use app\BaseController;
use app\model\ArticleArticle;
use app\model\ArticleChapter;
use app\model\Cate;
use app\model\SystemUsers;
use Overtrue\Pinyin\Pinyin;
use think\db\exception\ModelNotFoundException;
use think\facade\App;
use think\Request;

class Postbot extends Base
{
    public function save()
    {
        $data = request()->param();
        try {
            $book = ArticleArticle::where(
                array(
                    'articlename' => $data['book_name'],
                    'author' => $data['author']
                )
            )->findOrFail();
            try {
                $chapter = ArticleChapter::where([
                    'chaptername' => $data['chaptername'],
                    'articleid' => $book['articleid']
                ])->findOrFail();
                return json(['code' => 0, 'message' => '章节已存在']);
            } catch (ModelNotFoundException $e) {
                $chapter = new ArticleChapter();
                $chapter->articlename = $data['book_name'];
                $chapter->chaptername = trim($data['chaptername']);
                $chapter->articleid = $book['articleid'];
                $chapter->chapterorder = $data['chapterorder'];
                $chapter->lastupdate = time();
                $chapter->words = strlen($data['content']);
                $chapter->intro = strlen($data['content']) >= 99 ? substr($data['content'], 0, 99) : $data['content'];
                $chapter->preface = '';
                $chapter->notice = '';
                $chapter->foreword = '';
                $chapter->save();
                $book->words = $book->words+strlen($data['content']); 
                $book->lastchapterid = $chapter->chapterid; 
                $book->lastchapter = trim($data['chaptername']);
                $book->lastupdate = time();
                $book->save();
                $bigId = floor((double)($chapter['articleid'] / 1000));
                $dir = App::getRootPath() .  sprintf('/public/files/article/txt/%s/%s', $bigId,
                        $chapter['articleid']);
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $file = App::getRootPath() .  sprintf('/public/files/article/txt/%s/%s/%s.txt',
                        $bigId, $chapter['articleid'], $chapter->chapterid);
                file_put_contents($file, $data['content']);
                $book->lastchapterid = ArticleChapter::where(['chapterid']);
                $book->lastchapter = $data['chaptername'];
                return json(['code' => 0, 'message' => '发布成功', 'info' => ['book' => $book, 'chapter' => $chapter]]);
            }
        } catch (ModelNotFoundException $e) {
            try {
                $author = SystemUsers::where(
                    array(
                        'name' => trim($data['author']),
                        'groupid' => 3
                    ))->findOrFail();
            } catch (ModelNotFoundException $e) {
                $author = new SystemUsers();
                $author->uname = gen_uid(12);
                $author->name = $data['author'] ?: '侠名';
                $key_str = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
                $salt = substr(str_shuffle($key_str), mt_rand(0, strlen($key_str) - 11), 5);
                $author->salt = $salt;
                $jieqi_ver = config('site.jieqi_ver');
                if ($jieqi_ver >= 2.4) {
                    $author->pass = md5(md5('123456') . $salt);
                } else {
                    $author->pass = md5(trim('123456') . $salt);
                }
                $author->email = trim('123456@qq.com');
                $author->siteid = 0;
                $author->groupid = 3;
                $author->regdate = time();
                $author->sex = 1;
                $author->workid = 0;
                $author->lastlogin = 0;
                $author->save();
            }

            $book = new ArticleArticle();
            $book->author_id = $author->id;
            $book->author = $data['author'] ?: '侠名';
            $book->articlename = trim($data['articlename']);
            if (isset($data['backupname'])) {
                $book->backupname = trim($data['backupname']);
            }
            $book->initial = strtoupper(substr($this->convert(trim($data['articlename'])), 0, 1));
            if (isset($data['keywords'])) {
                $book->keywords = $data['keywords'];
            }
            if (isset($data['roles'])) {
                $book->roles = $data['roles'];
            }
            try {
                $cate = Cate::where('cate_name', '=', trim($data['cate']))->findOrFail();
                $book->sortid = $cate->sortid;
            } catch (ModelNotFoundException $exception) {
                $book->sortid = 0;
            }
            $book->postdate = time();
            $book->infoupdate = time();
            $book->lastupdate = time();
            $book->intro = $data['intro'];
            $book->words = 0;
            $book->rgroup = 0;
            $book->articlecode = $data['backupname'];
            $book->fullflag = $data['fullflag'];
            $book->imgflag = '';
            $book->freetime = time();
            $book->poster = 'admin';
            $book->agent = '';
            $book->reviewer = '';
            $book->lastvolume = '';
            $book->pubhouse = '';
            $book->pubisbn = '';
            $book->buysite = '';
            $book->buyurl = '';
            $book->vipvolume = '';
            $book->vipchapter = '';
            $book->lastchapter = $data['chaptername'];
            $book->save();
            $bigId = floor((double)($book['articleid'] / 1000));
            $dir = App::getRootPath() . sprintf('/public/files/article/image/%s/%s', $bigId, $book['articleid']);
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $file = App::getRootPath() . sprintf('/public/files/article/image/%s/%s/%ss.jpg',
                    $bigId, $book['articleid'], $book['articleid']);
            $content = file_get_contents($data['cover']);
            file_put_contents($file, $content);

            try {
                $chapter = ArticleChapter::where([
                    'chaptername' => $data['chaptername'],
                    'articleid' => $book['articleid']
                ])->findOrFail();
                return json(['code' => 0, 'message' => '章节已存在']);
            } catch (ModelNotFoundException $e) {
                $chapter = new ArticleChapter();
                $chapter->articlename = $data['book_name'];
                $chapter->chaptername = trim($data['chaptername']);
                $chapter->articleid = $book['articleid'];
                $chapter->chapterorder = $data['chapterorder'];
                $chapter->lastupdate = time();
                $chapter->words = strlen($data['content']);
                $chapter->preface = '';
                $chapter->notice = '';
                $chapter->summary = strlen($data['content']) >= 99 ? substr($data['content'], 0, 99) : $data['content'];
                $chapter->foreword = '';
                $chapter->save();
                $book->lastupdate = time();
                $book->save();
                $bigId = floor((double)($chapter['articleid'] / 1000));
                $dir = App::getRootPath() .  sprintf('/public/files/article/txt/%s/%s', $bigId,
                        $chapter['articleid']);
                if (!file_exists($dir)) {
                    mkdir($dir, 0777, true);
                }
                $file = App::getRootPath() .  sprintf('/public/files/article/txt/%s/%s/%s.txt',
                        $bigId, $chapter['articleid'], $chapter->chapterid);
                file_put_contents($file, $data['content']);
                $book->lastchapterid = $chapter->chapterid;
                $book->lastchapter = $data['chaptername'];
                return json(['code' => 0, 'message' => '发布成功', 'info' => ['book' => $book, 'chapter' => $chapter]]);
            }
        }
    }

    public function getLastChapter($articleid)
    {
        return ArticleChapter::where('articleid', '=', $articleid)
            ->order('chapterorder', 'desc')->limit(1)->find();
    }

    protected function convert($str)
    {
        $pinyin = new Pinyin();
        $str = $pinyin->abbr($str);
        return $str;
    }
}