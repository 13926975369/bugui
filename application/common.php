<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

function curl_get($url, $httpCode = 0) {
//    初始化
    $ch = curl_init();
//    爬取url地址
    curl_setopt($ch, CURLOPT_URL, $url);
//    不将爬取内容直接输出而保存到变量中
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    //部署在Linux环境下改为true
//    模拟一个浏览器访问https网站
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//    设定连接时间
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

    //执行获取内容
    $file_contents = curl_exec($ch);
    $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $file_contents;
}

function getRandChars($length){
    //不能把位数写死了，根据length来确定多少位数随机字符串
    $str = null;
    $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
    $max = strlen($strPol) - 1;

    //从中间抽出字符串加length次
    for ($i = 0; $i < $length; $i++){
        $str .= $strPol[rand(0, $max)];
    }

    return $str;
}

