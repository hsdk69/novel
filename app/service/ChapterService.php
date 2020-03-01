<?php


namespace app\service;

use app\model\Chapter;
use think\facade\App;

class ChapterService
{
    public function getChapters($where)
    {
        $page = config('page.back_end_page');
        $chapters = Chapter::where($where);
        foreach ($chapters as $chapter) {
            if (substr($chapter->content_url, 0, 4 ) !== "http") {
                $chapter->content_url = App::getRootPath() . 'public/' . $chapter->content_url;
            }
        }
        $pages = $chapters->order('id', 'desc')->paginate([
            'list_rows'=> $page,
            'query' => request()->param(),
            'var_page' => 'page',
        ]);
        return [
            'chapters' => $pages,
            'count' => $chapters->count(),
        ];
    }

    public function getLastChapter($book_id)
    {
        return Chapter::where('book_id', '=', $book_id)->order('chapter_order', 'desc')->limit(1)->find();
    }
}