<?php

/**
 * Class Demo
 * @author Diven (702814242@qq.com)
 */

class Demo
{

    static public $certi = array(
        'certificate_id' => '1747557431',
        'token' => 'b394893a1a4cf4ee73ded2a4792ec3ed0aad84783e8c0f776fddbaeaa074a712',
        'valid' => '0',
    );

    static function request($url, $param = array(), $method = 'POST', $timeOut = 10)
    {

        //如果是数组参数，则根据请求方式做相应的编码
        if (is_array($param) || is_object($param)) {
            //正则替换掉[0],[1]中的数字
            $queryString = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', http_build_query($param));
        } else {
            $queryString = $param;
        }

        $ch = curl_init();

        //http数据提交方式头信息
        $httpHeader = array("Expect:");

        //兼容不同的请求方式
        switch (strtoupper($method)) {

            case 'GET' :

                //form表单方式提交数据
                //避免data数据过长问题
                curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
                curl_setopt($ch, CURLOPT_URL, $url .
                    (strpos($url, '?') !== false ? '&' : '?') .
                    ltrim(ltrim($queryString, '&'), '?'));
                break;

            //默认post
            default :
                //form表单方式提交数据
                //避免data数据过长问题
                curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $queryString);
        }

        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //设置远程服务响应超时关闭时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);

        //API追踪信息
        $apiTrace = array();
        $apiTrace['startTime'] = microtime(true);

        //发送请求
        $res = curl_exec($ch);
        $res = !is_null(json_decode($res)) ? json_decode($res, true) : $res;

        // 返回结果
        if ($res) {

            curl_close($ch);

            // 返回格式化结果集
            return $res;

        } else {

            $errno = curl_errno($ch);
            $error = curl_error($ch);

            curl_close($ch);

            throw new  Exception("Curl error,errno:{$errno},error:{$error},'url':{$url},'data':$queryString");
        }
    }

    static function gen_sign($params)
    {
        return strtoupper(md5(strtoupper(md5(self::assemble($params))) . self::token()));
    }

    static function assemble($params)
    {
        if (!is_array($params)) return null;
        ksort($params, SORT_STRING);
        $sign = '';
        foreach ($params AS $key => $val) {
            if (is_null($val)) continue;
            if (is_bool($val)) $val = ($val) ? 1 : 0;
            $sign .= $key . (is_array($val) ? self::assemble($val) : $val);
        }
        return $sign;
    }//End Function

    static function token()
    {
        return self::get('token');
    }

    static function get($code='certificate_id')
    {
        return self::$certi[$code];
    }
}

$data = array(
        "task"   => time(),
        "direct" => "true",
        "method" => "customapi.order.create",
        "app_id" => "1747557431",

        "uid" => '2',
        "area"  =>  "广东省广州市荔湾区",
        "addr"  =>  "康王路498号和业大厦906",
        "name"  =>  "邱先生",
        "mobile"=>  "13602865139",  
        "shipping_id" => "3",
        "products" => "goods_91_457_1|goods_91_458_1",
        'cpns_id' => 0,
        'payment_pay_app_id' => 'pointpay',
        'memo'  => '这个是我的订单备注',
        "time"  => '任意时间段',
        "score" => "10",
);

// $data = array(
//     "task"   => time(),
//     "direct" => "true",
//     "method" => "customapi.order.logistics",
//     "app_id" => "1747557431",
//     "order_id" => "2017171225183615201",
//     "uid"   => 2,
// );

// $data = array(
//     "task"   => time(),
//     "direct" => "true",
//     "method" => "customapi.order.logistics",
//     "app_id" => "1747557431",
//     "order_id" => "171227164285735",
//     "uid"   => 2,
// );

$data["sign"] = Demo::gen_sign($data);
$url = "http://api-ecstore.rrxueche.com/openapi/customapi/api";
$res = Demo::request($url, $data);

header("Content-type: text/html; charset=utf-8");
echo "<pre>"; print_r($res);

            // 0=>'goods_75_357_2',
            // 1=>'goods_96_464_1',
            // array(
            //     'goods_id'=>'101',
            //     'product_id'=> '562',
            //     'products_num'=>1
            // ),
            //             array(
            //     'goods_id'=>'101',
            //     'product_id'=> '562',