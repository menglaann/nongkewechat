<?php
include "wechat.class.php";
$options = array(
    'token'=>'weixin', //填写你设定的key
    'encodingaeskey'=>'jmQv0VM4MJbEEIVWeh1oB0xPAvBznqmuQDeUJqFmXG4' //填写加密用的EncodingAESKey，如接口为明文模式可忽略
);
$weObj = new Wechat($options);
//$weObj->valid();//明文或兼容模式可以在接口验证通过后注释此句，但加密模式一定不能注释，否则会验证失败
$type = $weObj->getRev()->getRevType();
$content = $weObj->getRev()->getRevContent();
switch($type) {
case Wechat::MSGTYPE_TEXT:
    $action = new gpAction();
    $responseStr = $action->process($content);
    $weObj->text("hello, ".$responseStr)->reply();
    exit;
    break;
case Wechat::MSGTYPE_EVENT:
    break;
case Wechat::MSGTYPE_IMAGE:
    break;
default:
    $weObj->text("help info")->reply();
}

class gpAction
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            //exit;
        }
    }

    public function process($content) {
        return $this->parseArg($content);
    }

    public function help()
    {
        $helpMsg = "输入一下命令:\n".
            "CXZW 作物名:查询作物信息\n".
            "CXZX :查询资讯\n".
            "CXZX 城市:查询天气\n";
        return $helpMsg;
    }

    public function queryCrop($name)
    {
        $command = "python2 crawler/963110.py ".$name;
        $msg = shell_exec($command);
        return $msg;
    }

    public function queryNews()
    {
        $command = "python2 crawler/963110.py ".$name;
        $msg = shell_exec($command);
        return $command;
    }

    public function queryWeather($name)
    {
        $command = "python2 crawler/weather.com.cn.py ".$name;
        $msg = shell_exec($command);
        return $msg;
    }

    public function parseArg($msg)
    {
        $arr = preg_split('/\s+/',$msg);
        $msg = '您好，';
        if(strtoupper($arr[0]) == 'CXZW')
        {
            //maximum length in wechat:2048
            $msg .= substr($this->queryCrop($arr[1]),0,1024);
        }else if(strtoupper($arr[0]) == 'CXZX')
        {
            $msg .= '最新资讯:';
        }else if (strtoupper($arr[0]) == 'CXTQ')
        {
            $msg .= '天气: ';
        }else
        {
            $msg .= $this->help();
        }
        return $msg;
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){

            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[%s]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                <FuncFlag>0</FuncFlag>
                </xml>";
            if(!empty( $keyword ))
            {
                $msgType = "text";
                $contentStr = $this->help();
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            }else{
                echo "Input something...";
            }

        }else {
            echo "";
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}

?>
