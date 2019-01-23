<?php

require 'config.php';

class MushiController
{
    private $cookie = 'cookie.txt'; //cookie存放目录
    private $username = 'scutdxxywlwm';
    private $password = '25d55ad283aa400af464c76d713c07ad';
    private $static = 0; //登录状态
    private $activityId;
    private $picture_tmp_path;
    private $document_tmp_path;
    private $missionServiceLogs = '';
    private $reference;//负责人姓名
    private $telephoneNumber;//负责人电话

    //登录，仅为了保存cookie
    public function login($captchaCode)
    {
        $idCode = $captchaCode;
        $loginType = 2;
        $postfield = array(
            'loginType' => $loginType,
            'userName' => $this->username,
            'password' => $this->password,
            'captchaCode' => $idCode
        );
        $content = $this->curl(LOGIN_WEB,$postfield);
        $this->static = preg_match('/success/',$content);
        if ($this->static == 0)
        {

            echo '<script>alert("登录失败或服务器连接失败！")</script>';

            exit();
        }
    }

    //设置活动Id
    public function setActId($activityId)
    {
        $this->activityId = $activityId;
    }

    //录入，返回录入信息与录入状态
    public function  input($name,$idCardCode,$tel)
    {
        //查询人物
        $idCardCode = strtoupper($idCardCode);
        $idCard_search_url = 'https://www.gdzyz.cn/api/mission/manage/inviteUserList.do?missionId='.$this->activityId.'&zzName=&districtId=&subDistrict=true&userName=&idcardCode='.$idCardCode.'&mobile=&gender=&pageIndex=1&pageSize=10';
        $tel_search_url = 'https://www.gdzyz.cn/api/mission/manage/inviteUserList.do?missionId='.$this->activityId.'&zzName=&districtId=&subDistrict=true&userName=&idcardCode=&mobile='.$tel.'&gender=&pageIndex=1&pageSize=10';

        $m = array();
        $patten = '/\"userId\":\"(.*)\"/U';
        $error_patten = '/\"msg\":\"(.*)\"/U';

        $content = $this->curl($idCard_search_url);
        $idCard_judge = preg_match($patten,$content,$m);

        $message = array(
            'userId' => '',
            'name' => $name,
            'idCardCode' => $idCardCode,
            'status' => ''
        );

        if ($idCard_judge == 0 || empty($idCardCode) )
        {
            $content = $this->curl($tel_search_url);
            $tel_judge = preg_match($patten,$content,$m);

            if ($tel_judge == 0 || empty($tel) )
            {
                /*
                 * to do
                 * 查询失败时，记录当前失败的身份证号、名字
                 */
                $message['status'] = '查询失败或已录入';
                return $message;
            }
        }

        $message['userId'] = $m[1];

        //录用人物
        $input_url = 'https://www.gdzyz.cn/api/mission/manage/enlistUser4His.do?missionId='.$this->activityId.'&userId='.$message['userId'];
        $content = $this->curl($input_url);
        $res = preg_match('/录用成功/',$content);
        if ($res)
        {
            $message['status'] = '录入成功';
            return $message;
        }
        else
        {
            /*
             * to do
             * 录用失败时，记录当前失败的身份证号、名字
             */
            preg_match($error_patten,$content,$error_msg);
            $message['status'] = $error_msg[1];
            return $message;
        }
    }

    //考勤
    public function check($userId,$name,$idCardCode,$tel,$date,$hour,$minute)
    {
        $postField = array(
            'missionId' => $this->activityId,
            'userIds' => $userId,
            'askoff' => 1,
            'checkOnDate' => $date,
            'hour' => $hour,
            'minute' =>$minute,
            'leaveEarlyMinute' => 0,
            'arriveLateMinute' => 0
        );

        $content = $this->curl(CHECK_WEB,$postField);
        $patten = '/\"msg\":\"(.*)\"/U';
        preg_match($patten,$content,$missionId);

        $old_Log = $this->missionServiceLogs;
        $this->missionServiceLogs = $old_Log.$missionId[1];
    }
    
    //获取考勤名单（即录入成功的名单）
    public function getList()
    {
        $page = 1;
        $url = DOWNLOAD_WEB.'?missionId='.$this->activityId.'&userName=&checkOnDate=&isUpdated=&pageIndex='.$page.'&pageSize=100';
        $content = $this->curl($url);
        $content = json_decode($content);

        while(!empty($content->records))
        {
            foreach ($content->records as $member)
            {
                $old_Log = $this->missionServiceLogs;
                $this->missionServiceLogs = $old_Log.$member->missionServiceLogId.',';
            }

            $page++;
            $url = DOWNLOAD_WEB.'?missionId='.$this->activityId.'&userName=&checkOnDate=&isUpdated=&pageIndex='.$page.'&pageSize=100';
            $content = json_decode($this->curl($url));
        }
    }

    //上传照片
    public function uploadPicture()
    {
        $file = new \CURLFile(realpath("E光.jpeg"));
        $file->setMimeType("image/jpeg");
        $file->setPostFilename('E光.jpeg');
        $post_picture = array(
            "uid"=>"123",
            "id"=>"WU_FILE_0",
            "name"=>"E光.jpeg",
            "type"=>"image/jpeg",
            "lastModifiedDate"=>"Fri Apr 06 2018 11:23:11 GMT+0800 (中国标准时间)",
            "size"=>"49409",
            "file"=>$file,
        );
        $contents = $this->curl(UPLOAD_PICTURE,$post_picture);
        $patten = '/\"url\":\"(.*)\"/U';
        preg_match($patten,$contents,$tmp_path);
        $this->picture_tmp_path = $tmp_path[1];
    }

    //上传证书
    public function uploadDocument()
    {
        $file = new \CURLFile(realpath("E光.jpeg.zip"));
        $file->setMimeType('application/text');
        $file->setPostFilename('E光.jpeg.zip');
        $post_document = array(
            'filename'=>'C:\fakepath\E光.jpeg.zip',
            'papers'=>$file,
        );
        $contents = $this->curl(UPLOAD_DOCUMENT,$post_document);
        $patten = '/\"relative_path\":\"(.*)\"/U';
        preg_match($patten,$contents,$tmp_path);
        $this->document_tmp_path = $tmp_path[1];
    }

    //确认录入
    public function submit()
    {
        $post_data = array(
            "missionId"=>$this->activityId,
            "selectType"=>1,
            "missionServiceLogs"=>$this->missionServiceLogs,
            "reference"=>$this->reference,
            "telephoneNumber"=>$this->telephoneNumber,
            "imgUrl"=>$this->picture_tmp_path,
            "papers"=>"C:\fakepath\E光.jpeg.zip",
            "timeProveDocument"=>$this->document_tmp_path,
        );
        $content = $this->curl(SUBMIT_WEB,$post_data);
        $patten = '/\"msg\":\"(.*)\"/U';
        preg_match($patten,$content,$msg);
        return $msg[1];
    }

    public function curl($url, $curlPost = NULL)
    {
        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_SAFE_UPLOAD,true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
        if ($curlPost != NULL)
            curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $contents = curl_exec($ch);

        $code = curl_getinfo($ch,CURLINFO_HEADER_OUT);
        echo $code;

        curl_close($ch);

        return $contents;
    }

    //获取当前负责人姓名
    public function get_reference()
    {
        $handle = fopen('information','r');

        $reference = fgets($handle);
        $patten1 = '/\"reference\":\"(.*)\"/';
        preg_match($patten1,$reference,$refer);
        $this->reference = $refer[1];

        fclose($handle);

        return $refer[1];
    }

    //负责当前负责人电话
    public function get_telephoneNumber()
    {
        $handle = fopen('information','r');

        fgets($handle);
        $telephoneNumber = fgets($handle);
        $patten2 = '/\"telephoneNumber\":\"(.*)\"/';
        preg_match($patten2,$telephoneNumber,$tel);
        $this->telephoneNumber = $tel[1];
        fclose($handle);

        return $tel[1];
    }

    //获取上一次提交录入的名单文件名
    public function get_filename()
    {
        $handle = fopen('information','r');

        fgets($handle);
        fgets($handle);
        $filename = fgets($handle);
        $patten3 = '/\"filename\":\"(.*)\"/';
        preg_match($patten3,$filename,$file);
        fclose($handle);
        return $file[1];
    }

    //更新负责人信息
    public function update_reference($new_reference,$new_telephoneNumber,$new_filename)
    {
        $handle = fopen('information','w');

        fwrite($handle,'"reference":'.'"'.$new_reference.'"'."\n");
        fwrite($handle,'"telephoneNumber":'.'"'.$new_telephoneNumber.'"'."\n");
        fwrite($handle,'"filename":'.'"'.$new_filename.'"');

        fclose($handle);
    }

    public function get_captcha()
    {
        /*
         * 访问验证码页面，并保存cookie
         */
        $ch = curl_init(IDCODE_WEB); //初始化
        curl_setopt($ch, CURLOPT_HEADER, 0); //不返回header部分
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返回字符串，而非直接输出
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie);
        curl_setopt($ch, CURLOPT_COOKIEJAR,  $this->cookie); //存储cookies
        $imagedata = curl_exec($ch);
        curl_close($ch);

        $img = base64_encode($imagedata);
        return $img;
    }
}
