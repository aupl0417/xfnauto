<?php

/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:47
 */
namespace app\api\model;

use think\Db;
use think\Model;

class ShopGoodsCarsActivity extends Model
{

    protected $table = 'shop_goods_cars_activity';

    public function organization(){
        return $this->hasOne('organization', 'orgId');
    }

    public function user(){
        return $this->hasOne('systemuser', 'usersId');
    }
}