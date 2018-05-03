<?php

$fruits = array("3"=>"lemon", "1"=>"orange", "4"=>"banana", "2"=>"apple");
//krsort($fruits);

asort($fruits,SORT_NUMERIC);

var_dump($fruits);