<?php
/*$array = ['nofile.php', 'demo.php'];
foreach ($array as $v) {
    echo '$v:'. $v. BS;
    switch (true) {
        case false === $v = realpath(filter_var($v)):
        case !is_file($v):
        case !is_readable($v):
            echo $v. ' in case'. BS;
            continue; // or return false, throw new InvalidArgumentException
        default:
            echo 'break'. BS;
            break;
    }
    //...
    echo $v. ' in foreach'. BS;
}*/

$arr = array(1,2,3,4,5,6,7);
foreach($arr as $item) {
    switch ($item) {
        case '1':
            $item = 'a';
            break;
        case '2':
            break 2;
            $item = 'b';
            break;
        case '3':
            $item = 'c';
            break;
        case '4':
            $item = 'd';
            break;
        default:
            break;
    }
    echo $item.'<br>';
}