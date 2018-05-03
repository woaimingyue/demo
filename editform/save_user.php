<?php

$firstname = $_REQUEST['firstname'];
$lastname = $_REQUEST['lastname'];
$phone = $_REQUEST['phone'];
$email = $_REQUEST['email'];

include 'conn.php';

$sql = "insert into users(firstname,lastname,phone,email) values('$firstname','$lastname','$phone','$email')";
@mysql_query($sql);
echo json_encode(array(
	'id' => mysql_insert_id(),
	'firstname' => $firstname,
	'lastname' => $lastname,
	'phone' => $phone,
	'email' => $email
));

?>