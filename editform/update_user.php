<?php

$id = intval($_REQUEST['id']);
$firstname = $_REQUEST['firstname'];
$lastname = $_REQUEST['lastname'];
$phone = $_REQUEST['phone'];
$email = $_REQUEST['email'];

include 'conn.php';

$sql = "update users set firstname='$firstname',lastname='$lastname',phone='$phone',email='$email' where id=$id";
@mysql_query($sql);
echo json_encode(array(
	'id' => $id,
	'firstname' => $firstname,
	'lastname' => $lastname,
	'phone' => $phone,
	'email' => $email
));
?>