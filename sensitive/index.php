<?php

	$mysqli = new mysqli("192.168.22.209", "root", "123456", "ecstore_dev");

	if ($mysqli->connect_errno) {
	    printf("1:Connect failed: %s\n </br>", $mysqli->connect_error);
	    exit();
	}else{
		printf("1:Connect success: %s\n </br>", '');
	}

	/* change character set to utf8 */
	if (!$mysqli->set_charset("gbk")) {
	    printf("2:Error loading character set gbk: %s\n </br>", $mysqli->error);
	} else {
	    printf("2:Current character set: %s\n </br>", $mysqli->character_set_name());
	}

	$explode_content = get_file_content('./keys.txt');
	$value_s = array_map('to_values', $explode_content);
	$value_s = array_unique($value_s);
	$explode_value = implode(',', $value_s);
	$str = "INSERT into sdb_b2c_sensitive(words) values{$explode_value};";

	//$content = "('rwer'),(''),('')";
	$str = preg_replace("/\,\(\'\'\)/", '', $str); //去除空value值

	// echo $str;die;
	$handel = fopen('./key.sql', 'w');
	$res = fwrite($handel, $str);
	if($res){
		printf("3:Write file success: %s\n </br>",'');
	}else{
		printf("3:Write file error: %s\n </br>",'');
	}
	fclose($handel);

/*	$new_values = array_chunk($value_s, 1000);
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

	foreach ($new_values as $key => $value) {
		$explode_value = implode(',', $value);
		$res = $mysqli->query("INSERT into sdb_b2c_sensitive(words) values{$explode_value};");
		var_dump($res);
		echo "INSERT into sdb_b2c_sensitive(words) values{$explode_value};";

		$mysqli->commit();
	}

	$mysqli->close();*/

	function to_values($param = '')
	{
		$param = mb_convert_encoding($param, 'utf8', 'gbk');
		return "('".$param."')";
	}

	function get_file_content($file = '')
	{
		$file = './keys.txt';
		if(!file_exists($file)){
			die('file is not exists');
		}

		$file_content = file_get_contents($file);
		$explode_content = explode('|', $file_content);
		
		return $explode_content;
	}  