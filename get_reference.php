<?php

/*
 * 获取负责人信息
 */

require 'MushiController.php';

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET');
header('Access-Control-Allow-Headers:x-requested-with,content-type');

header("Content-Type: application/json");

$c = new MushiController();

$reference = $c->get_reference();
$telephoneNumber = $c->get_telephoneNumber();

$response = array(
    "reference"=>$reference,
    "telephoneNumber"=>$telephoneNumber,
);

echo json_encode($response);