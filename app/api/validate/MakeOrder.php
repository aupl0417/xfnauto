<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14 0014
 * Time: 13:39
 */
namespace app\api\validate;
use think\Validate;


class MakeOrder extends Validate{

    protected $rule = [
        'o_id'          => 'require|number',
        'o_type'        => 'require|number',
        'o_offerType'   => 'checkOfferType',
        'o_price'       => 'checkOfferType|number',
    ];

    protected $message = [
        'o_id.require'      => 'id不能为空',
        'o_id.number'         => 'id格式非法',
        'o_offerType.checkOfferType'  => '请选择报价方式',
        'o_price.checkOfferType'      => '报价不能为空',
        'o_price.number'          => '报价必须是数字串',
    ];

    public function checkOfferType(){
        $type      = $_REQUEST['type'] + 0;
        $offerType = $_REQUEST['offerType'] + 0;
        $price     = $_REQUEST['price'] + 0;

        if($type == 1 && empty($offerType)){
            return '请选择报价方式';
        }else if(($type == 0 || ($type == 1 && $offerType != 0)) && empty($price)){
            return '报价不能为空';
        }

        return true;
    }

}