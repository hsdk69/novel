<?php


namespace app\admin\controller;

use AipNlp;
class Gentag extends Base
{
    public function index() {
        $appid = '19558055';
        $appkey = 'LLWRCMRkMXZy2eUw7MvSdrbO';
        $secret = 'Z0GPwbjwySnjQDYwwoSG6g4AKZMSbzDO';

        $client = new AipNlp($appid, $appkey, $secret);
        $text = "蚀骨情深：顾少追妻套路深";

        // 调用词法分析
        $result = $client->lexer($text);
        if ($result) {
            foreach ($result['items'] as $item) {
                dump($item['item']);
            }
//            if (count($result['items']) > 1) { //分词成功
//                foreach ($result['items'] as $item) {
//                    dump($item['item']);
//                }
//            } else {
//                foreach ($result['items'][0]['basic_words'] as $item) { //从基词中取
//                    dump($item);
//                }
//            }
        }
    }
}