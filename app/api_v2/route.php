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

Route::get('/',function(){
    return 'Hello,world!';
});

//Route::get('adnews/v1/:id','api/ad.v1.News/read');      //查询
//Route::post('adnews/v1','api/ad.v1.News/add');          //新增
//Route::put('adnews/v1/:id','api/ad.v1.News/update');    //修改
//Route::delete('adnews/v1/:id','api/ad.v1.News/delete'); //删除

//Route::get('activity_v1/:id','api/v1.Activity/index');      //查询
//Route::post('adnews/v1','api/ad.v1.News/add');          //新增
//Route::put('adnews/v1/:id','api/ad.v1.News/update');    //修改
//Route::delete('adnews/v1/:id','api/ad.v1.News/delete'); //删除
