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

class Postbot extends BaseController
{
    protected $chapterService;

    public function initialize()
    {
        $this->chapterService = new \app\service\ChapterService();
    }

    public function save()
    {
        $data = request()->param();
        if (!isset($data['api_key']) || $data['api_key'] != config('site.api_key'))
            return json(['code' => 1, 'message' => 'Api密钥为空/密钥错误']);
        try {
            $book = ArticleArticle::where(
                array(
                    'articlename' => $data['book_name'],
                    'author' => $data['unique_id']
                )
            )->findOrFail();
            return json(['code' => 0, 'message' => '小说已存在']);
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
                if ($this->jieqi_ver >= 2.4) {
                    $author->pass = md5(md5($data['password']) . $salt);
                } else {
                    $author->pass = md5(trim($data['password']) . $salt);
                }
                $author->email = trim($data['email']);
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
            $book->sortid = Cate::where('cate_name', '=', trim($data['cate']))->column('sortid');
            $book->postdate = time();
            $book->infoupdate = time();
            $book->lastupdate = time();
            $book->words = 0;
            $book->rgroup = 0;
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
            $book->lastchapter = '';
            $book->save();
            $bigId = floor((double)($book['articleid'] / 1000));
            $file = App::getRootPath() . sprintf('/files/article/image/%s/%s/%ss.jpg',
                    $bigId, $book['articleid'], $book['articleid']);
            file_put_contents($file, $data['cover']);

            try {
                $chapter = ArticleChapter::where([
                    'chaptername' => $data['chaptername'],
                    'articleid' => $data['articleid']
                ])->findOrFail();
                return json(['code' => 0, 'message' => '章节已存在']);
            } catch (ModelNotFoundException $e) {
                $chapter = new ArticleChapter();
                $chapter->chaptername = trim($data['chaptername']);
                $chapter->articleid = $data['articleid'];
                $chapter->chapterorder = $data['chapterorder'];
                $chapter->lastupdate = time();
                $chapter->Words = strlen($data['content']);
                $chapter->preface = '';
                $chapter->notice = '';
                $chapter->summary = strlen($data['content']) >= 99 ? substr($data['content'], 0, 99) : $data['content'];
                $chapter->foreword = '';
                $chapter->save();
                $book->lastupdate = time();
                $book->save();

                $bigId = floor((double)($chapter['articleid'] / 1000));
                $file = sprintf('/files/article/txt/%s/%s/%s.txt',
                    $bigId, $chapter['articleid'], $chapter->id);
                file_put_contents($file, $data['content']);
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
