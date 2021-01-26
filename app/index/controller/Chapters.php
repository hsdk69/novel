<?php


namespace app\index\controller;


use app\model\ArticleChapter;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\facade\App;
use think\facade\Db;
use think\facade\View;

class Chapters extends Base
{
    public function index($id)
    {
        try {
            $chapter = cache('chapter:' . $id);
            if ($chapter == false) {
                $chapter = ArticleChapter::with('book.cate')->where('chapterid','=',$id)->findOrFail();
                $bigId = floor((double)($chapter['articleid'] / 1000));
                $chapter['book']['cover'] = sprintf('/files/article/image/%s/%s/%ss.jpg',
                    $bigId, $chapter['articleid'], $chapter['articleid']);
                cache('chapter:' . $id, $chapter, null, 'redis');
            }
        } catch (DataNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (ModelNotFoundException $e) {
            abort(404, $e->getMessage());
        }
        if ($this->end_point == 'id') {
            $chapter->book['param'] = $chapter->book['articleid'];
        } else {
            $chapter->book['param'] = $chapter->book['backupname'];
        }
        $bigId = floor((double)($chapter['articleid'] / 1000));
        $file = sprintf('/files/article/txt/%s/%s/%s.txt',
            $bigId, $chapter['articleid'], $id);
        $content = $this->getTxtcontent($this->server . $file);
        $articleid = $chapter->articleid;
        $chapters = cache('mulu:' . $articleid);
        if (!$chapters) {
            $chapters = ArticleChapter::where('articleid', '=', $articleid)->select();
            cache('mulu:' . $articleid, $chapters, null, 'redis');
        }

        $prev = cache('chapterPrev:' . $id);
        if (!$prev) {
            $prev = Db::query(
                'select * from ' . $this->prefix . 'article_chapter where articleid=' . $articleid . ' 
                and chapterorder<' . $chapter->chapterorder . ' order by chapterorder desc limit 1');
            cache('chapterPrev:' . $id, $prev, null, 'redis');
        }
        if (count($prev) > 0) {
            View::assign('prev', $prev[0]);
        } else {
            View::assign('prev', 'null');
        }

        $next = cache('chapterNext:' . $id);
        if (!$next) {
            $next = Db::query(
                'select * from ' . $this->prefix . 'article_chapter where articleid=' . $articleid . ' 
                and chapterorder>' . $chapter->chapterorder . ' order by chapterorder limit 1');
            cache('chapterNext:' . $id, $next, null, 'redis');
        }
        if (count($next) > 0) {
            View::assign('next', $next[0]);
        } else {
            View::assign('next', 'null');
        }
        View::assign([
            'chapter' => $chapter,
            'chapters' => $chapters,
            'chapter_count' => count($chapters),
            'content' => $content,
            'words' => mb_strlen($content),
        ]);
        return view($this->tpl);
    }

    private function getTxtcontent($txtfile)
    {
        //$file = fopen($txtfile, 'r');
        $file = file_get_contents($txtfile);
        $arr = explode("\n",$file);
//        $i = 0;
//        if ($file) {
//            while (!feof($file)) {
//                $arr[$i] = fgets($file);
//                $i++;
//            }
//            fclose($file);
//        } else {
//            // error opening the file.
//        }
        $arr = array_filter($arr); //数组去空
        $content = '<p>' . implode('</p><p>', $arr) . '</p>';
        return mb_convert_encoding($content, 'HTML-ENTITIES', "UTF-8");
    }
}