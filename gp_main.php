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
    $action = new gpAction($weObj);
    $responseStr = $action->process($content);
    //$weObj->news($responseStr)->reply();
    exit;
    break;
case Wechat::MSGTYPE_EVENT:
    break;
case Wechat::MSGTYPE_IMAGE:
    break;
default:
    $weObj->text("welcome")->reply();
}

class gpAction
{
    public function __construct($weObj)
    {
        $this->weObj = $weObj;
    }

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
            "CXTQ 城市:查询天气\n";
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
        //return "<a href=\"http://weibo.com/\">weibo</a";
        return $msg;
    }

    public function queryWeather($name)
    {
        $command = "python2 crawler/weather.com.cn.py ".$name." 2>&1";
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
            if(sizeof($arr) >= 2)
            {
                $msg .= substr($this->queryCrop($arr[1]),0,1024);
            }
            else
            {
                $msg .= 'Please input the crop name!';
            }
            $this->weObj->text($msg)->reply();
        }else if(strtoupper($arr[0]) == 'CXZX')
        {
            $msgs = $this->queryNews();
            $news = json_decode($msgs);
            $arrs = array();
            $i = 0;
            foreach($news as $j)
            {
                $arr = array(
                    'Title'=>'News',
                    'Description'=>$j->text,
                    'PicUrl'=>"http://www.963110.com.cn/",
                    'Url'=>$j->url,
                );
                $i_str = (string)$i;
                array_push($arrs,array($i_str=>$arr));
                $i = $i + 1;
            }
            $this->weObj->news($arrs)->reply();
            //$this->weObj->news(array(
            //"0"=>array(
            //'Title'=>'msg title',
            //'Description'=>'summary text',
            //'PicUrl'=>'http://www.domain.com/1.jpg',
            //'Url'=>'http://www.domain.com/1.html'
            //)
            //))->reply();
        }else if (strtoupper($arr[0]) == 'CXTQ')
        {
            if (sizeof($arr) >=2)
            {
                $msg .= $arr[1].'天气: ';
                $msg .=
                    $this->queryWeather($arr[1]);
            }else{
                $msg ="请输入要查询天气的城市";
            }
            $this->weObj->text($msg)->reply();
        }else
            {
                $msg .= $this->help();
                $this->weObj->text($msg)->reply();
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
