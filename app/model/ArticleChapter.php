<?php


namespace app\model;


use think\Model;

class ArticleChapter extends Model
{
    protected $pk = 'chapterid';

    public function book()
    {
        return $this->belongsTo(ArticleArticle::class, 'articleid', 'articleid');
    }

    public function setChapternameAttr($value)
    {
        return trim($value);
    }

    function getChapters($order, $where, $num)
    {
        if ($num == 0) {
            $chapters = ArticleChapter::where($where)
                ->order($order)->select();
        } else {
            if (strlen($num) == 3) {
                $arr = explode(',',$num);
                $chapters = ArticleChapter::where($where)
                    ->limit($arr[0],$arr[1])->order($order)->select();
            } else {
                $chapters = ArticleChapter::where($where)->limit($num)->order($order)->select();
            }

        }
        return $chapters;
    }

    public function getPagedChapters($order, $where, $pagesize)
    {
        $data = ArticleChapter::where($where)->order($order)
            ->paginate([
                'list_rows' => $pagesize,
                'query' => request()->param(),
            ]);
        $arr = $data->toArray();
        $paged = array();
        $paged['chapters'] = $arr['data'];
        $paged['page'] = [
            'total' => $arr['total'],
            'per_page' => $arr['per_page'],
            'current_page' => $arr['current_page'],
            'last_page' => $arr['last_page'],
            'query' => request()->param()
        ];
        return $paged;
    }
}