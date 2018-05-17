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
Route::get('ucenter_v1/index','api/v1.UserCenter/index');
Route::get('ucenter_v1/consumerlist','api/v1.Order/consumerList');
Route::get('ucenter_v1/customerlist','api/v1.Order/customerList');
Route::get('ucenter_v1/customers','api/v1.UserCenter/customers');
Route::get('order_v1/statistics','api/v1.Order/statistics');
Route::get('ucenter_v1/visit','api/v1.UserCenter/visit');
Route::get('publics_v1/brand','api/v1.Common/brand');
Route::get('publics_v1/series','api/v1.Common/series');
Route::get('publics_v1/carlist','api/v1.Common/carList');
Route::post('publics_v1/upload','api/v1.Common/upload');
Route::get('publics_v1/gettoken','api/v1.Common/getToken');
Route::post('ucenter_v1/quotation','api/v1.UserCenter/quotation');
Route::get('ucenter_v1/quotationDetail','api/v1.UserCenter/quotationDetail');
Route::get('publics_v1/share','api/v1.Common/share');
Route::get('publics_v1/share1','api/v1.Common/share1');
Route::get('publics_v1/createImage','api/v1.Common/createImage');
Route::get('publics_v1/test','api/v1.Common/test');
Route::get('activity_v1.1/index','api/v1._1.Test/index');
Route::get('article_v1/index','api/v1.Article/index');
Route::post('article_v1/create','api/v1.Article/create');
Route::post('article_v1/edit','api/v1.Article/edit');
Route::get('article_v1/detail','api/v1.Article/detail');
Route::get('article_v1/remove','api/v1.Article/remove');
Route::get('ucenter_v1/consumerDetail','api/v1.Order/consumerDetail');
Route::get('backend_v1/stock','api/v1.Backend.StockCar/index');
Route::post('backend_v1/stock/edit','api/v1.Backend.StockCar/edit');
Route::get('backend_v1/stock/detail','api/v1.Backend.StockCar/detail');
Route::post('backend_v1/stock/export','api/v1.Backend.StockCar/export');
