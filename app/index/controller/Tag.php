<?php


namespace app\index\controller;


use app\model\Author;
use app\model\Cate;
use app\model\Tags;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\facade\View;

class Tag extends Base
{
    protected $bookService;
    protected function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->bookService = app('bookService');
    }

    public function Index($id)
    {
        try {
            $tag = cache('tag:'.$id);
            if (!$tag) {
                $param = config('seo.tag_end_point');
                if ($param == 'id') {
                    $tag = Tags::findOrFail($id);
                } else if ($param == 'pinyin') {
                    $tag = Tags::where('pinyin','=',$id)->findOrFail();
                } else {
                    $tag = Tags::where('jianpin','=',$id)->findOrFail();
                }
                cache('tag:'.$id, $tag, null, 'redis');
            }

            $tags = cache('tags:'.$id);
            if (!$tags) {
                //$tags = Tags::where('id', 'in', $tag->similar)->select();
                $tags = Db::query(
                    "select * from " . $this->prefix . "tags where match(tag_name) 
            against ('" . $tag->tag_name . "') LIMIT 10");
                foreach ($tags as &$t) {
                    if ($param == 'id') {
                        $t['param'] = $t['id'];
                    } else if ($param == 'pinyin') {
                        $t['param'] = $t['pinyin'];
                    } else {
                        $t['param'] = $t['jianpin'];
                    }
                }
                cache('tags:'.$id, $tags, null, 'redis');
            }

            $books = cache('tag:books:'.$id);
            if (!$books) {
                $books = $this->bookService->search($tag->tag_name, $this->prefix);
                foreach ($books as &$book) {
                    try {
                        $author = Author::findOrFail($book['author_id']);
                        $cate = Cate::findOrFail($book['cate_id']);
                        $book['author'] = $author;
                        $book['cate'] = $cate;
                        if ($this->end_point == 'id') {
                            $book['param'] = $book['id'];
                        } else {
                            $book['param'] = $book['unique_id'];
                        }
                    } catch (DataNotFoundException $e) {
                        abort(404, $e->getMessage());
                    } catch (ModelNotFoundException $e) {
                        abort(404, $e->getMessage());
                    }
                }
                cache('tag:books:'.$id, $books, null, 'redis');
            }

            View::assign([
                'books' => $books,
                'tag' => $tag,
                'tags' => $tags
            ]);
            return view($this->tpl);
        } catch (DataNotFoundException $e) {
            abort(404, $e->getMessage());
        } catch (ModelNotFoundException $e) {
            abort(404, $e->getMessage());
        }
    }
}