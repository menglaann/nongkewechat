<?php

//include"gp_main.php";
//$gp_action = new gpAction();
//echo $gp_action->queryNews();
//$msgs = $gp_action->queryNews();
//$news = json_decode($msgs);
//$arrs = array();
//foreach($news as $a_news)
//{
//$arr = array(
//'Title'=>'News',
//'Description'=>$a_news->text,
//'PicUrl'=>"http://www.963110.com.cn/",
//'Url'=>$a_news->url,
//);
//array_push($arrs,$arr);
//}
//echo var_dump($arrs);

// copy file content
$json_file = file_get_contents('sample/news.json');

// convert string to json
$jfo = json_decode($json_file);

// read the value
foreach ($jfo as $i) {
    //echo $i->url."\t".$i->text."\n";
}

$arrs = array();
$i = 0;
foreach($jfo as $j)
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

var_dump($arrs);


?>
