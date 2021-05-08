<?php


namespace app\model;


use think\Model;

class SystemUsers extends Model
{
    protected $pk = 'uid';

    public function setUnameAttr($value)
    {
        return trim($value);
    }

    public function setSaltAttr($value)
    {
        return trim($value);
    }

    public function setEmailAttr($value)
    {
        return trim($value);
    }

    public function delFavors($uid, $ids)
    {
        $where[] = ['uid', '=', $uid];
        $where[] = ['articleid', 'in', $ids];
        $favors = UserFavor::where($where)->selectOrFail();
        foreach ($favors as $favor) {
            $favor->delete();
        }
    }
}