<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14 0014
 * Time: 13:39
 */
namespace app\api\validate;
use think\Db;
use think\Validate;


class ConsumerOrderUpdate extends Validate{

    protected $rule = [
        'id'                 => 'require|number',
        'orgId'              => 'require|number',
        'orgName'            => 'require',
        'orgLinker'          => 'require',
        'orgPhone'           => 'require|number',
        'orderType'          => 'require|number',
        'logisticsType'      => 'require|number',
        'freight'            => 'require|number|egt:0',
        'pickCarDate'        => 'require|checkDate',
        'pickCarAddr'        => 'require',
    ];

    protected $message = [
        'id.require'           => 'ID不能为空',
        'id.number'            => 'ID格式非法',
        'orgId.require'        => '请选择汽贸店',
        'orgId.number'         => '汽贸店ID非法',
        'orgName.require'      => '请选择汽贸店',
        'orgLinker.require'    => '联系人不能为空',
        'orderType.require'    => '请选择订单类型',
        'orderType.number'     => '订单类型非法',
        'logisticsType.require'=> '请选择物流类型',
        'logisticsType.number' => '物流类型非法',
        'freight.require'      => '运费不能为空',
        'freight.number'       => '运费格式非法',
        'pickCarDate.require'  => '提车时间不能为空',
        'pickCarDate.checkDate'=> '提车时间格式非法',
        'pickCarAddr.require'  => '提车地址不能为空',
    ];

    public function checkDate($value){
        if(!checkDateIsValid($value)){
            return false;
        }
        return true;
    }
}