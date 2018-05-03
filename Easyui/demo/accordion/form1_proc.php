<?php

	$name = $_POST['name'];
	$email = $_POST['email'];
	$phone = $_POST['phone'];
 	
 	echo json_encode($_POST);exit;
	echo "Your Name: $name <br/> Your Email: $email <br/> Your Phone: $phone";