<?php


namespace app\service;


use app\model\BookLogs;

class BookLogService
{
    public function getPageAdmin($where = '1=1') {
        $data = BookLogs::where($where);
        $page = config('page.back_end_page');
        $logs = $data->order('id', 'desc')
            ->paginate(
                [
                    'list_rows'=> $page,
                    'query' => request()->param(),
                    'var_page' => 'page',
                ]);
        return [
            'logs' => $logs,
            'count' => $data->count()
        ];
    }
}