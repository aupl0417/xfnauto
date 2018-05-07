<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

//Route::get('/',function(){
//    return 'Hello,world!';
//});
//Route::domain('work.' . config('url_domain_root'), 'admin');
/*
 * 接口路由文件，接口专用
 * */

//V1版本接口路由
//屏幕活动
Route::get('activity_v1/index','api/v1.Screen.Activity/read');
Route::post('ucenter_v1/index','api/v1.UserCenter/index');
Route::get('ucenter_v1/consumerlist','api/v1.Order/consumerList');
Route::get('ucenter_v1/customerlist','api/v1.Order/customerList');
