<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use \think\Route;

Route::rule('login','bugui/Index/login','POST',['https'=>true]);
Route::rule('first_time','bugui/Index/first_time','POST',['https'=>true]);
Route::rule('publish_good','bugui/Index/publish_good','POST',['https'=>true]);
Route::rule('good_photo','bugui/Index/good_photo','POST',['https'=>true]);
Route::rule('phone_code','bugui/Index/phone_code','POST',['https'=>true]);
Route::rule('show_class','bugui/Index/show_class','POST',['https'=>true]);
Route::rule('show_class_good','bugui/Index/show_class_good','POST',['https'=>true]);
Route::rule('recommend','bugui/Index/recommend','POST',['https'=>true]);
Route::rule('collect','bugui/Index/collect','POST',['https'=>true]);
Route::rule('search','bugui/Index/search','POST',['https'=>true]);
Route::rule('my_publish','bugui/Index/my_publish','POST',['https'=>true]);
Route::rule('delete_good','bugui/Index/delete_good','POST',['https'=>true]);
Route::rule('show_collect','bugui/Index/show_collect','POST',['https'=>true]);
Route::rule('delete_collect','bugui/Index/delete_collect','POST',['https'=>true]);
Route::rule('get_message','bugui/Index/get_message','POST',['https'=>true]);
Route::rule('confirm','bugui/Index/confirm','POST',['https'=>true]);
Route::rule('deliver','bugui/Index/deliver','POST',['https'=>true]);
Route::rule('take','bugui/Index/take','POST',['https'=>true]);
Route::rule('comment','bugui/Index/comment','POST',['https'=>true]);
Route::rule('my_out','bugui/Index/my_out','POST',['https'=>true]);
Route::rule('my_in','bugui/Index/my_in','POST',['https'=>true]);
Route::rule('test','bugui/Index/test','POST',['https'=>false]);
