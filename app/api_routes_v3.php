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
Route::post('shop_v3/loan/apply','api/v3.Shop.Loan/apply');
Route::get('shop_v3/loan/detail','api/v3.Shop.Loan/detail');
Route::get('shop_v3/loan/cancel','api/v3.Shop.Loan/cancel');
Route::get('shop_v3/loan/carcolor','api/v3.Shop.Loan/carcolor');
Route::get('shop_v3/user/shopInfo','api/v3.Shop.User/shopInfo');
Route::get('shop_v3/user/loanInfo','api/v3.Shop.User/loanInfo');
Route::get('shop_v3/index/updatefee','api/v3.Shop.Index/updatefee');
Route::get('task_v3/updatefee','api/v3.Task/updatefee');

Route::post('shop_v3/loan/send','api/v3.Shop.Loan/send');

//小程序端垫资接口
Route::get('frontend_v3/loan/index','api/v3.Frontend.Loan/index');
Route::get('frontend_v3/user/profile','api/v3.Frontend.User/profile');
Route::post('frontend_v3/Index/verify','api/v3.Frontend.Index/verify');
Route::post('frontend_v3/loan/create','api/v3.Frontend.Loan/create');
Route::post('frontend_v3/loan/apply','api/v3.Frontend.Loan/apply');
Route::get('frontend_v3/loan/detail','api/v3.Frontend.Loan/detail');
Route::get('frontend_v3/loan/cancel','api/v3.Frontend.Loan/cancel');

//后台接口
//垫资资格认证列表
Route::get('backend_v3/shoploanverify/index','api/v3.Backend.ShopLoanVerify/index');
Route::get('backend_v3/shoploanverify/detail','api/v3.Backend.ShopLoanVerify/detail');
Route::post('backend_v3/shoploanverify/verify','api/v3.Backend.ShopLoanVerify/verify');

//后台垫资管理
Route::get('backend_v3/shoploan/index','api/v3.Backend.ShopLoan/index');
Route::post('backend_v3/shoploan/create','api/v3.Backend.ShopLoan/create');
Route::post('backend_v3/shoploan/setrate','api/v3.Backend.ShopLoan/setRate');
Route::get('backend_v3/shoploan/detail','api/v3.Backend.ShopLoan/detail');
Route::post('backend_v3/shoploan/verify','api/v3.Backend.ShopLoan/verify');
Route::post('backend_v3/shoploan/loanVoucher','api/v3.Backend.ShopLoan/loanVoucher');
Route::post('backend_v3/shoploan/payVoucher','api/v3.Backend.ShopLoan/payVoucher');
Route::get('backend_v3/shoploan/unpayList','api/v3.Backend.ShopLoan/unpayList');
Route::get('backend_v3/shoploan/overdue','api/v3.Backend.ShopLoan/overdue');
Route::get('backend_v3/shoploan/overdueDetail','api/v3.Backend.ShopLoan/overdueDetail');
Route::get('backend_v3/shoploan/payRecord','api/v3.Backend.ShopLoan/payRecord');

//后台店铺认证管理
Route::get('backend_v3/shop/index','api/v3.Backend.Shop/index');
Route::get('backend_v3/shop/detail','api/v3.Backend.Shop/detail');
Route::post('backend_v3/shop/verify','api/v3.Backend.Shop/verify');