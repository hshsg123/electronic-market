<?php

/*
 * 下载导出的志愿时名单表格
 */

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET');
header('Access-Control-Allow-Headers:x-requested-with,content-type');

require 'MushiController.php';

$c = new MushiController();
$file = $c->get_filename();


header ( "Content-Type: application/vnd.ms-excel" );
header ( "Content-Disposition: attachment; filename=".$file );

readfile($file);


?>
