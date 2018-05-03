<?php

 	$link = mysql_connect('127.0.0.1', 'unamecom_qlzw','qianlizhiwai@11');
	
	if (!$link) {
	    die('Could not connect: ' . mysql_error());
	}

	mysql_select_db('unamecom_qianlizhiwai', $link) or die ('Can\'t use foo : ' . mysql_error());
	//mysql_query('use unamecom_qianlizhiwai');
	$result = mysql_query('select * from rb_admin_users');

	//$result = mysql_query('show databases');

	$data = [];
	while ($row = mysql_fetch_array($result)) {
	    $data[] = $row;
	}

	echo '<pre>';

	//var_dump($data);
	echo '----------------------';
	var_dump($result);
	//echo 'Connected successfully';
	mysql_close($link);
