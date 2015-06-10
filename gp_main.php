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
        $command = "python2 crawler/963110.py crop ".$name;
        $msg = shell_exec($command);
        return $msg;
    }

    public function queryNews()
    {
	$command = "python2 crawler/963110.py news";
        $msg = shell_exec($command);
        return '[{"url": "http://www.963110.com.cn/wcm/index.php?m=content&c=index&a=show&catid=7&id=6463", "text": "\u6d77\u53e3\u5b89\u63922600\u4e07\u5143\u63a8\u8fdb\u201c\u83dc\u7bee\u5b50\u201d\u6d41\u901a\u4f18\u5316\u5efa\u8bbe\u5de5\u7a0b"}, {"url": "http://www.963110.com.cn/wcm/index.php?m=content&c=index&a=show&catid=7&id=6462", "text": "\u6b63\u5b97\u5b9a\u5b89\u7cbd\u5b50\u6709\u201c\u62a4\u8eab\u7b26\u201d\u4e86"}, {"url": "http://www.963110.com.cn/wcm/index.php?m=content&c=index&a=show&catid=7&id=6457", "text": "\u6d77\u5357\u7cbd\u8ba9\u6e38\u5ba2\u54c1\u5c1d\u7279\u8272\u201c\u7aef\u5348\u5473\u201d"}, {"url": "http://www.963110.com.cn/wcm/index.php?m=content&c=index&a=show&catid=7&id=6456", "text": "\u6d77\u5357\u98df\u54c1\u9700\u6811\u597d\u4ea7\u54c1\u5f62\u8c61"}, {"url": "http://www.963110.com.cn/wcm/index.php?m=content&c=index&a=show&catid=7&id=6455", "text": "\u4e09\u4e9a\u6253\u51fb\u519c\u6751\u8fdd\u5efa\u201c\u91cd\u707e\u533a\u201d"}, {"url": "http://www.963110.com.cn/wcm/index.php?m=content&c=index&a=show&catid=7&id=6454", "text": "\u510b\u5dde\uff1a\u98ce\u60c5\u4e07\u201c\u7cbd\u201d\u9999\u715e\u4eba"}, {"url": "http://www.963110.com.cn/wcm/index.php?m=content&c=index&a=show&catid=7&id=6453", "text": "\u5927\u4e2d\u578b\u6e14\u8239\u6d77\u4e0a\u53ef\u770b58\u5957\u7535\u89c6\u8282\u76ee"}]';
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
            $jfo = json_decode($msgs);
            $arrs = array();
            $i = 0;
            foreach($jfo as $j)
            {
            //$this->weObj->text($j->url)->reply();
                $arr = array(
                    'Title'=>$j->text,
                    'Description'=>utf8_encode($j->text),
                    //'PicUrl'=>"http://www.963110.com.cn/",
                    'Url'=>$j->url,
                );
                $i_str = (string)$i;
                array_push($arrs,$arr);
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
