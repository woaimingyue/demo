<?php

$params = $_REQUEST;

if ($params['callback']) {
    $data = array('name'=>'zhangsan', 'redirect'=>'http://m.miniso.cn');
    echo $params['callback']."(".json_encode($data).")";die;
}