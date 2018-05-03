<?php

echo '<pre>';

$arr = array(array('one', 'two'), array('three', 'four', 'five'), array('six', 'seiven'));

$temp = array();
$k = 0;

foreach ($arr as $key => $value) {
	if($key > $k) {
		$temp = array_merge($temp, $value);
	} else {
		$temp = $value;
	}

	$k = $key;
}
//var_dump($temp);

#---------------------------------------------------------------------------------------------

$a = [
    'id',
    'name',
    'identityId',
    'phone',
    'email',
    'schoolId'
];

$b = [
    'id' => '唯一标识',
    'identityId' => '身份证',
    'phone' => '手机号',
    'email' => '邮箱',
    'name' => '姓名',
    'schoolId' => '学校'
];

var_dump(array_merge(array_flip($a), $b));