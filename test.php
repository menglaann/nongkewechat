<?php

// copy file content
//$json_file = file_get_contents('sample/news.json');

// convert string to json
//$jfo = json_decode($json_file);
$command = "python2 crawler/963110.py news";
$msg = shell_exec($command);
$jfo = json_decode($msg);

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

$arrs = array(
            "0"=>array(
            'Title'=>'msg title',
            'Description'=>'summary text',
            'PicUrl'=>'http://www.domain.com/1.jpg',
            'Url'=>'http://www.domain.com/1.html'
            ),
"1"=>array(
            'Title'=>'msg title',
            'Description'=>'summary text',
            'PicUrl'=>'http://www.domain.com/1.jpg',
            'Url'=>'http://www.domain.com/1.html'
            )
            );

var_dump($arrs);


?>
