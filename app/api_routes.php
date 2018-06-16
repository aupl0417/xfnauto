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
Route::get('ucenter_v1/stockcarlist','api/v1.Order/stockCarList');
Route::get('ucenter_v1/organizationlist','api/v1.Organization/index');
Route::post('ucenter_v1/organization/create','api/v1.Organization/create');
Route::post('ucenter_v1/organization/edit','api/v1.Organization/edit');
Route::get('ucenter_v1/logistics','api/v1.Logistics/index');
Route::get('ucenter_v1/logistics/consignment','api/v1.Logistics/consignment');
Route::get('ucenter_v1/shop','api/v1.Shop/index');
Route::get('ucenter_v1/shop/activity','api/v1.Shop/activity');
Route::get('ucenter_v1/userlist','api/v1.UserCenter/userList');
Route::get('ucenter_v1/userdetail','api/v1.UserCenter/userDetail');
Route::get('ucenter_v1/getseller','api/v1.UserCenter/getSeller');
Route::get('ucenter_v1/supplier','api/v1.Supplier/index');
Route::get('ucenter_v1/carsproductlist','api/v1.Order/carsProductList');
Route::post('login_v1/index','api/v1.Login/index');
//Route::get('publics_v1/test','api/v1.Common/test');
//Route::get('activity_v1.1/index','api/v1._1.Test/index');
//前台文章列表及详情
Route::get('article_v1/index','api/v1.Article/index');
Route::get('article_v1/detail','api/v1.Article/detail');



//前台店铺垫资接口
Route::post('shop_v1/loan/index','api/v1.Shop.Loan/index');
Route::get('shop_v1/user/profile','api/v1.Shop.User/profile');
Route::post('shop_v1/Index/verify','api/v1.Shop.Index/verify');

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
Route::get('backend_v1/supplier','api/v1.Backend.Supplier/index');
Route::post('backend_v1/supplier/create','api/v1.Backend.Supplier/create');
Route::post('backend_v1/supplier/remove','api/v1.Backend.Supplier/remove');
Route::get('backend_v1/car','api/v1.Backend.Car/index');
Route::get('backend_v1/car/family','api/v1.Backend.Car/family');

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
Route::get('backend_v1/brand','api/v1.Backend.Publics/brand');
Route::get('backend_v1/customer','api/v1.Backend.CustomerOrder/index');
Route::get('backend_v1/customer/detail','api/v1.Backend.CustomerOrder/detail');
Route::get('backend_v1/systemuser/info','api/v1.Backend.SystemUser/userInfo');
Route::post('backend_v1/customer/pay','api/v1.Backend.CustomerOrder/pay');
Route::get('backend_v1/consumer/getpayinfo','api/v1.Backend.ConsumerOrder/getPayInfo');
Route::post('backend_v1/consumer/pay','api/v1.Backend.ConsumerOrder/pay');
Route::get('backend_v1/stocklist','api/v1.Backend.Stock/index');
Route::post('backend_v1/editstock','api/v1.Backend.Stock/edit');
Route::post('backend_v1/createstock','api/v1.Backend.Stock/create');
Route::get('backend_v1/removestock','api/v1.Backend.Stock/remove');

//验证JAVA接口权限
Route::get('backend_v1/publics/checkauth','api/v1.Backend.Publics/checkJavaApiAuth');
Route::get('ucenter_v1/export/test','api/v1.Export/test');


//官网
Route::get('pc_v1/news','api/v1.PC.News/index');
Route::get('pc_v1/news/detail','api/v1.PC.News/detail');
Route::post('pc_v1/note','api/v1.PC.News/note');
Route::get('backend_v1/note','api/v1.Backend.Note/index');
Route::get('pc_v1/organization','api/v1.PC.News/organization');

/**
 * V2版本接口路由
 * */
Route::get('ucenter_v2/index','api/v2.UserCenter/index');
Route::get('ucenter_v2/consumerlist','api/v2.Order/consumerList');
Route::get('ucenter_v2/customerlist','api/v2.Order/customerList');
Route::get('ucenter_v2/customers','api/v2.UserCenter/customers');
Route::get('order_v2/statistics','api/v2.Order/statistics');
Route::get('ucenter_v2/visit','api/v2.UserCenter/visit');
Route::get('ucenter_v2/stockout','api/v2.Order/stockOut');
Route::get('publics_v2/brand','api/v2.Common/brand');
Route::get('publics_v2/series','api/v2.Common/series');
Route::get('publics_v2/carlist','api/v2.Common/carList');
Route::post('publics_v2/upload','api/v2.Common/upload');
Route::get('publics_v2/gettoken','api/v2.Common/getToken');
Route::post('ucenter_v2/quotation','api/v2.UserCenter/quotation');
Route::get('ucenter_v2/quotationDetail','api/v2.Common/quotationDetail');
Route::get('publics_v2/share','api/v2.Common/share');
Route::get('publics_v2/contract','api/v2.Common/contract');
Route::get('publics_v2/createImage','api/v2.Common/createImage');
Route::get('ucenter_v2/consumerDetail','api/v2.Common/consumerDetail');
Route::get('ucenter_v2/stocklist','api/v2.Order/stockList');
Route::get('ucenter_v2/stockcarlist','api/v2.Order/stockCarList');
Route::get('ucenter_v2/organizationlist','api/v2.Organization/index');
Route::post('ucenter_v2/organization/create','api/v2.Organization/create');
Route::post('ucenter_v2/organization/edit','api/v2.Organization/edit');
Route::get('ucenter_v2/logistics','api/v2.Logistics/index');
Route::get('ucenter_v2/logistics/consignment','api/v2.Logistics/consignment');
Route::get('ucenter_v2/shop','api/v2.Shop/index');
Route::get('ucenter_v2/shop/activity','api/v2.Shop/activity');
Route::get('ucenter_v2/userlist','api/v2.UserCenter/userList');
Route::get('ucenter_v2/userdetail','api/v2.UserCenter/userDetail');
Route::get('ucenter_v2/getseller','api/v2.UserCenter/getSeller');
Route::get('ucenter_v2/supplier','api/v2.Supplier/index');
Route::get('ucenter_v2/carsproductlist','api/v2.Order/carsProductList');
Route::post('login_v2/index','api/v2.Login/index');
//Route::get('publics_v2/test','api/v2.Common/test');
//Route::get('activity_v2.1/index','api/v2._1.Test/index');
//前台文章列表及详情
Route::get('article_v2/index','api/v2.Article/index');
Route::get('article_v2/detail','api/v2.Article/detail');



//前台店铺垫资接口
Route::post('shop_v2/loan/index','api/v2.Shop.Loan/index');
Route::get('shop_v2/user/profile','api/v2.Shop.User/profile');
Route::post('shop_v2/Index/verify','api/v2.Shop.Index/verify');

//后台接口
//后台文章管理
Route::get('backend_v2/article/index','api/v2.Backend.Article/index');
Route::post('backend_v2/article/create','api/v2.Backend.Article/create');
Route::post('backend_v2/article/edit','api/v2.Backend.Article/edit');
Route::get('backend_v2/article/detail','api/v2.Backend.Article/detail');
Route::get('backend_v2/article/remove','api/v2.Backend.Article/remove');
Route::get('backend_v2/article/publish','api/v2.Backend.Article/publish');

//后台库存管理
Route::get('backend_v2/stock','api/v2.Backend.StockCar/index');
Route::post('backend_v2/stock/edit','api/v2.Backend.StockCar/edit');
Route::get('backend_v2/stock/detail','api/v2.Backend.StockCar/detail');
Route::get('backend_v2/stock/export','api/v2.Backend.StockCar/export');
Route::get('backend_v2/consumer/export','api/v2.Backend.ConsumerOrder/export');
Route::get('backend_v2/consumer/index','api/v2.Backend.ConsumerOrder/index');
Route::get('backend_v2/consumer/detail','api/v2.Backend.ConsumerOrder/consumerDetail');
Route::get('backend_v2/supplier','api/v2.Backend.Supplier/index');
Route::post('backend_v2/supplier/create','api/v2.Backend.Supplier/create');
Route::post('backend_v2/supplier/remove','api/v2.Backend.Supplier/remove');
Route::get('backend_v2/car','api/v2.Backend.Car/index');
Route::get('backend_v2/car/family','api/v2.Backend.Car/family');

//后台角色管理
Route::get('backend_v2/role/index','api/v2.Backend.Role/index');
Route::post('backend_v2/role/create','api/v2.Backend.Role/create');
Route::post('backend_v2/role/edit','api/v2.Backend.Role/edit');
Route::get('backend_v2/role/remove','api/v2.Backend.Role/remove');
Route::get('backend_v2/roleaccess/index','api/v2.Backend.RoleAccess/index');
Route::post('backend_v2/roleaccess/addauth','api/v2.Backend.RoleAccess/addAuth');
Route::get('backend_v2/systemuser/index','api/v2.Backend.SystemUser/index');
Route::post('backend_v2/systemuser/create','api/v2.Backend.SystemUser/create');
Route::post('backend_v2/systemuser/edit','api/v2.Backend.SystemUser/edit');
Route::get('backend_v2/systemuser/detail','api/v2.Backend.SystemUser/detail');
Route::get('backend_v2/role/list','api/v2.Backend.Role/lists');
Route::get('backend_v2/systemuser/higherups','api/v2.Backend.SystemUser/higherUps');
Route::get('backend_v2/systemuser/remove','api/v2.Backend.SystemUser/remove');

//组织机构
Route::get('backend_v2/organization/index','api/v2.Backend.Organization/index');
Route::post('backend_v2/organization/create','api/v2.Backend.Organization/create');
Route::post('backend_v2/organization/edit','api/v2.Backend.Organization/edit');
Route::get('backend_v2/organization/detail','api/v2.Backend.Organization/detail');
Route::get('backend_v2/organization/remove','api/v2.Backend.Organization/remove');
Route::get('backend_v2/organization/getorg','api/v2.Backend.Organization/getOrg');

Route::get('backend_v2/menu/index','api/v2.Backend.Menu/index');
Route::post('backend_v2/menu/create','api/v2.Backend.Menu/create');
Route::post('backend_v2/menu/edit','api/v2.Backend.Menu/edit');
Route::get('backend_v2/menu/remove','api/v2.Backend.Menu/remove');
Route::post('backend_v2/login','api/v2.Backend.Login/index');
Route::get('backend_v2/brand','api/v2.Backend.Publics/brand');
Route::get('backend_v2/customer','api/v2.Backend.CustomerOrder/index');
Route::get('backend_v2/customer/detail','api/v2.Backend.CustomerOrder/detail');
Route::get('backend_v2/systemuser/info','api/v2.Backend.SystemUser/userInfo');
Route::post('backend_v2/customer/pay','api/v2.Backend.CustomerOrder/pay');
Route::get('backend_v2/consumer/getpayinfo','api/v2.Backend.ConsumerOrder/getPayInfo');
Route::post('backend_v2/consumer/pay','api/v2.Backend.ConsumerOrder/pay');
Route::get('backend_v2/stocklist','api/v2.Backend.Stock/index');
Route::post('backend_v2/editstock','api/v2.Backend.Stock/edit');
Route::post('backend_v2/createstock','api/v2.Backend.Stock/create');
Route::get('backend_v2/removestock','api/v2.Backend.Stock/remove');

//验证JAVA接口权限
Route::get('backend_v2/publics/checkauth','api/v2.Backend.Publics/checkJavaApiAuth');
Route::get('ucenter_v2/export/test','api/v2.Export/test');


//官网
Route::get('pc_v2/news','api/v2.PC.News/index');
Route::get('pc_v2/news/detail','api/v2.PC.News/detail');
Route::post('pc_v2/note','api/v2.PC.News/note');
Route::get('backend_v2/note','api/v2.Backend.Note/index');
Route::get('pc_v2/organization','api/v2.PC.News/organization');