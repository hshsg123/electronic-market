<?php
/*
 * 更新负责人信息
 */
require 'MushiController.php';

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET');
header('Access-Control-Allow-Headers:x-requested-with,content-type');

header("Content-Type: application/json");

$c = new MushiController();

$new_reference = $_POST['reference'];
$new_telephonenumber = $_POST['telephoneNumber'];

$filename = $c->get_filename();

$c->update_reference($new_reference,$new_telephonenumber,$filename);

echo true;