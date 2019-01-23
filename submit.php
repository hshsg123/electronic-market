<?php
/*
 * 登录并录入页面
 */
require 'MushiController.php';
require 'vendor/autoload.php';

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET');
header('Access-Control-Allow-Headers:x-requested-with,content-type');


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use \PhpOffice\PhpSpreadsheet\Reader\DefaultReadFilter;
use \PhpOffice\PhpSpreadsheet\Writer\IWriter;

$activityId = $_POST['activityId'];
$date = $_POST['date'];
$filename = $_FILES['filename']['tmp_name'];

$c = new MushiController();
$c->setActId($activityId);

//    上传材料

$c->uploadPicture();
$c->uploadDocument();

$c->getList();

$c->get_reference();
$c->get_telephoneNumber();

$msg = $c->submit();
echo $msg;