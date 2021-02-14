<?php


namespace app\app\controller;


use app\model\ArticleChapter;
use Firebase\JWT\JWT;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;

class Chapters extends Base
{
    public function getList()
    {
        $articleid = input('articleid');
        $chapters = cache('mulu:' . $articleid);
        if (!$chapters) {
            $chapters = ArticleChapter::where('articleid', '=', $articleid)->select();
            cache('chapters:' . $articleid, $chapters, null, 'redis');
        }

        $result = [
            'success' => 1,
            'chapters' => $chapters
        ];
        return json($result);
    }

    public function detail()
    {
        $id = input('chapterid');
        $utoken = input('utoken');
        if (isset($utoken)) {
            $key = config('site.api_key');
            $info = JWT::decode($utoken, $key, array('HS256', 'HS384', 'HS512', 'RS256' ));
            $arr = (array)$info;
            $this->uid = $arr['uid'];
        }
        try {
            $chapter = cache('mulu:' . $id);
            if (!$chapter) {
                $chapter = ArticleChapter::with('book')->findOrFail($id);
				cache('mulu:' . $id, $chapter, null, 'redis');
            }

            $articleid = $chapter->articleid;
            $prev = cache('chapterPrev:' . $id);
            if (!$prev) {
                $prev = Db::query(
                    'select * from ' . $this->prefix . 'article_chapter where articleid=' . $articleid . ' 
                and chapterorder<' . $chapter->chapterorder . ' and chaptertype=0 order by chapterorder desc limit 1');
                cache('chapterPrev:' . $id, $prev, null, 'redis');
            }
            $next = cache('chapterNext:' . $id);
            if (!$next) {
                $next = Db::query(
                    'select * from ' . $this->prefix . 'article_chapter where articleid=' . $articleid . ' 
                and chapterorder>' . $chapter->chapterorder . ' and chaptertype=0 order by chapterorder limit 1');
                cache('chapterNext:' . $id, $next, null, 'redis');
            }

            $chapter['prev'] = count($prev) > 0 ? $prev[0]['id'] : null;
            $chapter['next'] = count($next) > 0 ? $next[0]['id'] : null;

            $result = [
                'success' => 1,
                'chapter' => $chapter,
            ];

            return json($result);
        } catch (ModelNotFoundException $e) {
            return json(['success' => 0, 'msg' => '章节id错误']);
        }
    }
}