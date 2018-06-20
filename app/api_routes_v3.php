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


/**
 * v3版本接口路由
 * */

//前台店铺垫资接口
Route::get('shop_v3/loan/index','api/v3.Shop.Loan/index');
Route::get('shop_v3/user/profile','api/v3.Shop.User/profile');
Route::post('shop_v3/Index/verify','api/v3.Shop.Index/verify');
Route::post('shop_v3/loan/create','api/v3.Shop.Loan/create');
Route::get('shop_v3/loan/detail','api/v3.Shop.Loan/detail');

//后台接口
//后台垫资管理
Route::get('backend_v3/shoploan/index','api/v3.Backend.ShopLoan/index');
Route::post('backend_v3/shoploan/create','api/v3.Backend.ShopLoan/create');
Route::post('backend_v3/shoploan/setrate','api/v3.Backend.ShopLoan/setRate');
Route::post('backend_v3/shoploan/detail','api/v3.Backend.ShopLoan/detail');
Route::post('backend_v3/shoploan/verify','api/v3.Backend.ShopLoan/verify');