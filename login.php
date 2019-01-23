<?php

require 'MushiController.php';

if (isset($_COOKIE['user']) && $_COOKIE['user'] == 'I love Elight volunteers family')
{
    header('location: index.php');
}

if (isset($_POST['password']))
{
    if ($_POST['password'] ==  'elight2016')
    {
        $captchaCode = $_POST['captchaCode'];

        $c = new MushiController();
        $c->login($captchaCode);

        echo '<script>alert("登录成功！");</script>';
        setcookie('user','I love Elight volunteers family',time()+7200);

        header('location: index.php');
    }
    else echo '<script>alert("密码错误！");</script>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="E光.jpeg" type="image/x-icon"/>
    <title>录入系统登录</title>
</head>

<body align="center" onload="fin()">
<h1>广东志愿者网志愿时自动补录系统</h1>
<form action="" method="POST" id="login">

    请输入验证码: <input type="text" name="captchaCode" id="code"/>
    <img src="" height="20" width="50" onclick="fin()" id="captchaCode">
    <br><br>
    请输入密码: <input style="width: 167px;" type="password" name="password"> <br><br>

    <button type="submit">提交</button>

</body>

<script>

    function fin()
    {
        var request = new XMLHttpRequest();
        request.open("GET","main2.php");
        request.send();

        request.onreadystatechange = function(){
            document.getElementById('captchaCode').src = "data:image/png;base64,"+request.responseText;
        };

    }

</script>

</html>