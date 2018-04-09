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

Route::rule('login','bugui/Index/login','POST',['https'=>false]);
Route::rule('first_time','bugui/Index/first_time','POST',['https'=>false]);
Route::rule('publish_good','bugui/Index/publish_good','POST',['https'=>false]);
Route::rule('show_class','bugui/Index/show_class','POST',['https'=>false]);
Route::rule('show_class_good','bugui/Index/show_class_good','POST',['https'=>false]);
Route::rule('collect','bugui/Index/collect','POST',['https'=>false]);
Route::rule('search','bugui/Index/search','POST',['https'=>false]);
Route::rule('change_info','bugui/Index/change_info','POST',['https'=>false]);
