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

function sendCode($phone,$uid){//发送验证码
    $charset = '123456789';//随机因子
    $_len = strlen($charset)-1;//四个啊
    $code ='';
    //遍历验证码长度次数，每一次在随机因子里面取出一个字符
    for ($i=0;$i<4;$i++) {
        $code .= $charset[mt_rand(0,$_len)];
    }
    $url = sprintf(config('setting.url'),config('setting.apiid'),config('setting.apikey'),$phone,$code);
    $result = curl_get($url);
    $set['uid'] = $uid;
    $set['phone'] = $phone;
    cache($code, $set, config('setting.code_time'));
    return $code;
}

function doCurl($url, $type=0, $data=[]) {
    $ch = curl_init(); //初始化
//    设置选项
    //爬取网页url
    curl_setopt($ch, CURLOPT_URL, $url);
    //这个选项是判定是否把内容直接输出，true的话是保存在一个变量中
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //header头
    curl_setopt($ch, CURLOPT_HEADER, $url);

    if ($type == 1){
        // post
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    //执行获取内容
    $output = curl_exec($ch);
    // 释放curl句柄
    curl_close($ch);
    return $output;
}

