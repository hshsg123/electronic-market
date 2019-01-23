
<?php
/*
 * 验证码获取页面
 */
require 'MushiController.php';

header('Access-Control-Allow-Origin:*');
header('Access-Control-Allow-Methods:POST,GET');
header('Access-Control-Allow-Headers:x-requested-with,content-type');

header("Content-Type: Text/plain;charset=utf-8");

$c = new MushiController();
$captchaCode = $c->get_captcha();
echo $captchaCode;

?>

