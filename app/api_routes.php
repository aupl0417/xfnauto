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
Route::get('ucenter_v1/stockout','api/v1.Order/stockOut');
Route::get('publics_v1/brand','api/v1.Common/brand');
Route::get('publics_v1/series','api/v1.Common/series');
Route::get('publics_v1/carlist','api/v1.Common/carList');
Route::post('publics_v1/upload','api/v1.Common/upload');
Route::get('publics_v1/gettoken','api/v1.Common/getToken');
Route::post('ucenter_v1/quotation','api/v1.UserCenter/quotation');
Route::get('ucenter_v1/quotationDetail','api/v1.Common/quotationDetail');
Route::get('publics_v1/share','api/v1.Common/share');
Route::get('publics_v1/contract','api/v1.Common/contract');
Route::get('publics_v1/createImage','api/v1.Common/createImage');
Route::get('ucenter_v1/consumerDetail','api/v1.Common/consumerDetail');
Route::get('ucenter_v1/stocklist','api/v1.Order/stockList');
Route::get('ucenter_v1/organizationlist','api/v1.Organization/index');
Route::get('ucenter_v1/logistics','api/v1.Logistics/index');
Route::get('ucenter_v1/logistics/consignment','api/v1.Logistics/consignment');
Route::get('ucenter_v1/shop','api/v1.Shop/index');
Route::get('ucenter_v1/shop/activity','api/v1.Shop/activity');
Route::get('ucenter_v1/userlist','api/v1.UserCenter/userList');
//Route::get('publics_v1/test','api/v1.Common/test');
//Route::get('activity_v1.1/index','api/v1._1.Test/index');
//前台文章列表及详情
Route::get('article_v1/index','api/v1.Article/index');
Route::get('article_v1/detail','api/v1.Article/detail');

//后台接口
//后台文章管理
Route::get('backend_v1/article/index','api/v1.Backend.Article/index');
Route::post('backend_v1/article/create','api/v1.Backend.Article/create');
Route::post('backend_v1/article/edit','api/v1.Backend.Article/edit');
Route::get('backend_v1/article/detail','api/v1.Backend.Article/detail');
Route::get('backend_v1/article/remove','api/v1.Backend.Article/remove');
Route::get('backend_v1/article/publish','api/v1.Backend.Article/publish');

//后台库存管理
Route::get('backend_v1/stock','api/v1.Backend.StockCar/index');
Route::post('backend_v1/stock/edit','api/v1.Backend.StockCar/edit');
Route::get('backend_v1/stock/detail','api/v1.Backend.StockCar/detail');
Route::get('backend_v1/stock/export','api/v1.Backend.StockCar/export');
Route::get('backend_v1/consumer/export','api/v1.Backend.ConsumerOrder/export');
Route::get('backend_v1/consumer/index','api/v1.Backend.ConsumerOrder/index');
Route::get('backend_v1/consumer/detail','api/v1.Backend.ConsumerOrder/consumerDetail');

//后台角色管理
Route::get('backend_v1/role/index','api/v1.Backend.Role/index');
Route::post('backend_v1/role/create','api/v1.Backend.Role/create');
Route::post('backend_v1/role/edit','api/v1.Backend.Role/edit');
Route::get('backend_v1/role/remove','api/v1.Backend.Role/remove');
Route::get('backend_v1/roleaccess/index','api/v1.Backend.RoleAccess/index');
Route::post('backend_v1/roleaccess/addauth','api/v1.Backend.RoleAccess/addAuth');
Route::get('backend_v1/systemuser/index','api/v1.Backend.SystemUser/index');
Route::post('backend_v1/systemuser/create','api/v1.Backend.SystemUser/create');
Route::post('backend_v1/systemuser/edit','api/v1.Backend.SystemUser/edit');
Route::get('backend_v1/systemuser/detail','api/v1.Backend.SystemUser/detail');
Route::get('backend_v1/role/list','api/v1.Backend.Role/lists');
Route::get('backend_v1/systemuser/higherups','api/v1.Backend.SystemUser/higherUps');
Route::get('backend_v1/systemuser/remove','api/v1.Backend.SystemUser/remove');

//组织机构
Route::get('backend_v1/organization/index','api/v1.Backend.Organization/index');
Route::post('backend_v1/organization/create','api/v1.Backend.Organization/create');
Route::post('backend_v1/organization/edit','api/v1.Backend.Organization/edit');
Route::get('backend_v1/organization/detail','api/v1.Backend.Organization/detail');
Route::get('backend_v1/organization/remove','api/v1.Backend.Organization/remove');
Route::get('backend_v1/organization/getorg','api/v1.Backend.Organization/getOrg');

Route::get('backend_v1/menu/index','api/v1.Backend.Menu/index');
Route::post('backend_v1/menu/create','api/v1.Backend.Menu/create');
Route::post('backend_v1/menu/edit','api/v1.Backend.Menu/edit');
Route::get('backend_v1/menu/remove','api/v1.Backend.Menu/remove');
Route::post('backend_v1/login','api/v1.Backend.Login/index');

//验证JAVA接口权限
Route::get('backend_v1/publics/checkauth','api/v1.Backend.Publics/checkJavaApiAuth');


//官网
Route::get('pc_v1/news','api/v1.PC.News/index');
Route::get('pc_v1/news/detail','api/v1.PC.News/detail');
Route::post('pc_v1/note','api/v1.PC.News/note');
Route::get('backend_v1/note','api/v1.Backend.Note/index');
Route::get('pc_v1/organization','api/v1.PC.News/organization');