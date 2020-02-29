<?php

namespace app\common;


class Pay
{
    public function submit($order_id, $money, $pay_type)
    {
        $posturl = trim(config('payment.pay.url'));//这里改成支付URL
        $pid = trim(config('payment.pay.appid'));//这里改成支付ID
        $apikey = trim(config('payment.pay.appkey')); //这是您的通讯密钥
        //构造需要传递的参数
        $data = array(
            "pid" => $pid,//你的支付ID
            "out_trade_no" => $order_id, //唯一标识 可以是用户ID,用户名,session_id(),订单ID,ip 付款后返回
            "type" => (string)$pay_type,//支付方式
            "money" => $money,//金额
            "name" => "余额充值",//金额
            "sitename" => "漫画站",
            "notify_url" => config('site.schema').config('site.api_domain') . '/paynotify',//通知地址
            "return_url" => config('site.schema').config('site.domain') . '/feedback',//跳转地址
        );
        //将数组参数转码
        $query = $this->buildRequestPara($data);
        //将转码数据进行&和=组合，生成传递值
        $request_data = $this->createLinkstringUrlencode($query);
        //生成支付提交链接和参数
        $url = $posturl . $request_data; //支付页面

        header("Location:{$url}"); //跳转到支付页面
    }

    public function buildRequestPara($para_temp)
    {
        //除去待签名参数数组中的空值和签名参数
        $para_filter = $this->paraFilter($para_temp);
        //对待签名参数数组排序
        $para_sort = $this->argSort($para_filter);
        //生成签名结果
        $mysign = $this->buildRequestMysign($para_sort);
        //签名结果与签名方式加入请求提交参数组中
        $para_sort['sign'] = $mysign;
        $para_sort['sign_type'] = strtoupper(trim(strtoupper('MD5')));
        return $para_sort;
    }

    public function buildRequestMysign($para_sort)
    {
        //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $this->createLinkstring($para_sort);

        $mysign = $this->md5Sign($prestr, trim(config('payment.epay.appkey')));

        return $mysign;
    }

    public function md5Sign($prestr, $key)
    {
        $prestr = $prestr . $key;
        return md5($prestr);
    }

    public function createLinkstring($para)
    {
        $arg = "";
        foreach ($para as $key => $val) {
            $arg .= $key . "=" . $val . "&";
        }

        //去掉最后一个&字符

        $arg = substr($arg, 0, strlen($arg) - 1);

        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }

    public function paraFilter($para)
    {
        $para_filter = array();
        foreach ($para as $key => $val) {
            if ($key == "sign" || $key == "sign_type" || $val == "") continue;
            else    $para_filter[$key] = $para[$key];
        }
        return $para_filter;
    }

    public function argSort($para)
    {
        ksort($para);
        reset($para);
        return $para;
    }

    public function createLinkstringUrlencode($para)
    {
        $arg = "";
        foreach ($para as $key => $val) {
            $arg .= $key . "=" . urlencode($val) . "&";
        }

        //去掉最后一个&字符
        $arg = substr($arg, 0, strlen($arg) - 1);


        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $arg = stripslashes($arg);
        }

        return $arg;
    }
}