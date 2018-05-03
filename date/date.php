<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/3
 * Time: 13:48
 */

$raw = '22.11.1968';
$start = DateTime::createFromFormat('d. m. Y', $raw);

echo $start->format('Y-m-d');