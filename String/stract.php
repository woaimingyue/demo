<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/1
 * Time: 13:53
 */

header("Content-type:text/html; charset=utf-8");
echo '<pre>';

$str = 'Keepalived原理 简介 Keepalived:高可用或热备软件,用来防止单点故障(单点故障是指一旦某一)';

$search_arr = ['Keepalived', '简介'];
$replace_arr = ['Mysql', '介绍'];
$result[0] = str_replace('Keepalived', 'Mysql', $str);

$result[1] = str_ireplace($search_arr, $replace_arr, $str);

$result[2] = substr_replace($str, '****', 0, 10);

/**************************************************************************************************/

$str1 = '中国a汉1族';

echo strlen($str1);
echo '<br>';
echo mb_strlen($str1,'UTF8');


$str2 = '    
    sdfasd
asdfasdfasdf';

echo $str2;