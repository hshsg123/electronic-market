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

    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filename);
    $patten1 = '/身份证/';
    $patten2 = '/姓名/';
    $patten3 = '/手机|电话/';
    $patten4 = '/时长/';

//抓取excel表数据
    for ($i = 1; $i<=3; $i++)
        for ($j = 'A'; $j<='J'; $j++)
        {
            $value = $spreadsheet->getActiveSheet()->getCell($j.$i)->getValue();
            $idCard_judge = preg_match($patten1,$value);
            $name_judge = preg_match($patten2,$value);
            $tel_judge = preg_match($patten3,$value);
            $time_judge = preg_match($patten4,$value);

            if ($idCard_judge)
            {
                $idCard_column = $j;
                $row = $i+1;
            }

            if ($name_judge)
            {
                $name_column = $j;
            }

            if ($tel_judge)
            {
                $tel_column = $j;
            }

            if ($time_judge)
            {
                $time_column = $j;
            }
        }
    if (!isset($idCard_column)|!isset($name_column)|!isset($tel_column)|!isset($time_column))
    {

        echo '<script>alert("文件读取失败！")</script>';

        exit();
    }

    $name = $spreadsheet->getActiveSheet()->getCell($name_column.$row)->getValue();
    $idCardCode = $spreadsheet->getActiveSheet()->getCell($idCard_column.$row)->getValue();
    $tel = $spreadsheet->getActiveSheet()->getCell($tel_column.$row)->getValue();

    if (isset($time_column))
    {
        $time = $spreadsheet->getActiveSheet()->getCell($time_column.$row)->getValue();
        $hour = floor($time);
        $minute = 60*($time - $hour);
    }

    $list = array();    //录入人员清单

//录入并考勤
    while (!empty($name) && (!empty($idCardCode) || !empty($tel)))
    {
        $message = $c->input($name,$idCardCode,$tel);
        $userId = $message['userId'];

        array_push($list,$message);

        if (isset($time_column) && !empty($userId))
        {
            $c->check($userId,$name,$idCardCode,$tel,$date,$hour,$minute);
        }

        $row++;

        $name = $spreadsheet->getActiveSheet()->getCell($name_column.$row)->getValue();
        $idCardCode = $spreadsheet->getActiveSheet()->getCell($idCard_column.$row)->getValue();
        $tel = $spreadsheet->getActiveSheet()->getCell($tel_column.$row)->getValue();

        if (isset($time_column))
        {
            $time = $spreadsheet->getActiveSheet()->getCell($time_column.$row)->getValue();
            $hour = floor($time);
            $minute = 60*($time - $hour);
        }
    }
    echo json_encode($list);




