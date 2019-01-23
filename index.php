<?php

    if (!isset($_COOKIE['user']) || $_COOKIE['user'] != 'I love Elight volunteers family')
    {
        header('location: login.php');
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="E光.jpeg" type="image/x-icon"/>
    <title>E光志愿时录入系统</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<style type="text/css">
    #homepage {
        left: 50%;
        top: 50%;
        width: 60%;
        height: 200px;
        line-height: 40px;
        margin: 70px auto;
        float: left;
    }
    #introduction{
        float: left;
        width: 40%;
        margin: 50px 0px;
        min-height: 200px;

    }
    p{
        border: 0.5px solid black;
        width: 300px;
    }
    #introduce{
        border: 0.5px solid black;
        width: 300px;
        padding: 20px;
        margin-left: 0px;
    }

    table {
        /*设置相邻单元格的边框间的距离*/
        border-spacing: 0;
        /*表格设置合并边框模型*/
        border-collapse: collapse;
        text-align: center;
    }
    /*关键设置 tbody出现滚动条*/
    table tbody {
        display: block;
        height: 500px;
        overflow-y: scroll;
    }

    table thead,
    tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }
    /*关键设置：滚动条默认宽度是16px 将thead的宽度减16px*/
    table thead {
        width: calc( 100% - 1em)
    }


    table thead th {
        background: #ccc;
    }

    table tr td:first-child{
        width:20%;
    }
</style>

<body onload="start()">



<div id="homepage" align="center" >
    广东志愿者网志愿时自动补录系统
    <p>
    <form id="form1" action="##" method="post" enctype="multipart/form-data">


        活动ID: <input style="width: 135px;" type="text" name="activityId" id="activityId"/> <br>

        志愿时名单: <input style="width: 175px;" type="file" accept=".xlsx,.xls" name="filename" id="filename"/>
        <br>


        考勤日期: <input type="date" name="date" id="date"> <br>


        <input name="submit" type="button" onclick="checkInput()" value="录入并考勤"/>

        &nbsp;&nbsp;&nbsp;

        <input name="check" type="button" onclick="checkSubmit()" value="提交证明材料">

    </form>

    </p>

    <p style="border: 1px solid blue;font-size: small" >
        负责人：<input type="text" name="reference" id="reference" value="无" disabled="disabled"/><br>
        电话号码：<input type="text" name="telephoneNumber" id="telephoneNumber" value="无" disabled="disabled"/><br>
        <input type="button" id="change_reference" value="修改" onclick="change_reference()">
        <input style="margin-left: 50px" name="download" type="button" value="下载表格" onclick="download()"/>
    </p>
</div>

<div id="introduction" >
   <div id="introduce" align="center">
       说明：<br>
       <div style="font-size: small; color: red" align="left">
           1、excel中需包含电话、身份证和时长三个关键词<br><br>
           2、志愿时长最大为10，超过10请分多次录入！<br><br>
           3、考勤日期要在活动时间内<br><br>
           4、必须要有手机号或身份证号的其中一个才能使用本系统，只有名字的人请手动录入<br><br>
           5、录入与考勤需要填满所有的空；提交证明材料只需要填写“活动ID”一栏即可
       </div>
   </div>

    <table width="80%" border="1"  hidden="hidden" id="list">
        <thead>
        <tr>
            <th width="20%">姓名</th>
            <th>身份证号</th>
            <th>录入状态</th>
        </tr>
        </thead>

        <tbody id="i_list">
<!--        <tr>-->
<!--            <td></td>-->
<!--            <td></td>-->
<!--            <td></td>-->
<!--        </tr>-->
<!--        <tr>-->
<!--            <td></td>-->
<!--            <td></td>-->
<!--            <td></td>-->
<!--        </tr>-->
        </tbody>
    </table>
</div>

<!--引入ajax的jQuery-->
<script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>

<script>

    function start()
    {
        show_reference.call();
    }

    function show_reference()
    {
        var request = new XMLHttpRequest();
        request.open("GET","get_reference.php");
        request.send();

        request.onreadystatechange = function () {
            var json = eval("("+request.responseText+")");
            document.getElementById('reference').value =  json['reference'];
            document.getElementById('telephoneNumber').value = json['telephoneNumber'];
        }
    }

    function change_reference()
    {
        if (document.getElementById('reference').disabled == true && document.getElementById('reference').disabled == true)
        {
            document.getElementById('reference').disabled = false;
            document.getElementById('telephoneNumber').disabled = false;
            document.getElementById('change_reference').value = '确定';
        }
        else
        {
            var new_reference = document.getElementById('reference').value;
            var new_telephoneNumber = document.getElementById('telephoneNumber').value;
            var request = new XMLHttpRequest();
            request.open("POST","update_reference.php");
            request.setRequestHeader("Content-type","application/x-www-form-urlencoded");
            request.send('reference='+new_reference+'&'+'telephoneNumber='+new_telephoneNumber);

            request.onreadystatechange = function ()
            {
                show_reference.call();
                document.getElementById('reference').disabled = true;
                document.getElementById('telephoneNumber').disabled = true;
                document.getElementById('change_reference').value = '修改';
            }
        }
    }
    
    function download()
    {
        window.open('download.php');
    }
    
    function checkSubmit() 
    {
        var captchaCode = document.getElementById('code');
        var activityId = document.getElementById('activityId');
        if (activityId.value == "")
        {
            alert('活动ID不能为空！');
            return false;
        }

        $.ajax({
            type: "POST",//方法类型
            dataType: "text",//预期服务器返回的数据类型
            url: "submit.php" ,//url
            data: $('#form1').serialize(),
            success: function (result) {
                alert(result);
            },
            error : function() {
                alert("异常！");
            }
        });
    }

    function checkInput()
    {
        var activityId = document.getElementById('activityId');
        var filename = document.getElementById('filename');
        var date = document.getElementById('date');

        var form = new FormData(document.getElementById("form1"));

        if (activityId.value == "")
        {
            alert('活动ID不能为空！');
            return false;
        }
        else if (filename.value == "")
        {
            alert('未上传志愿时名单！');
            return false;
        }
        else if (date.value == "")
        {
            alert('尚未选择考勤日期！');
            return false;
        }

        alert('录入中，请稍后');

        $.ajax({
            type: "POST",//方法类型
            dataType: "json",//预期服务器返回的数据类型
            url: "main.php" ,//url
            data: form,
            processData:false,
            contentType:false,
            success: function (result) {
                var str = '';
                for(var i in result)
                {
                    str = str + "<tr width=\"20%\"><td>"+result[i].name+"</td><td>"+result[i].idCardCode+"</td>"+"<td";
                    if (result[i].status != '录入成功')
                        str = str + " style=\"color: red\"";

                    str = str + ">"+result[i]['status']+"</td></tr>";

                }

                document.getElementById('introduce').hidden = true;
                document.getElementById('list').hidden = false;

                $("#i_list").html(str);

            },
            error : function(XMLHttpRequest, textStatus, errorThrown) {
                alert("异常！");
            }
        });
    }

</script>

</body>
</html>

