<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14 0014
 * Time: 13:39
 */
namespace app\api_v2\validate;
use think\Db;
use think\Validate;


class CreateOrg extends Validate{

    protected $rule = [
        'shortName'           => 'require',
        'linkMan'             => 'require',
//        'parentId'            => 'require|number',
        'telePhone'           => 'require',
        'address'             => 'require',
        'provinceId'          => 'require|number',
        'cityId'              => 'require|number',
        'areaId'              => 'require|number',
        'provinceName'        => 'require',
        'cityName'            => 'require',
        'areaName'            => 'require'
    ];

    protected $message = [
        'shortName.require'       => '门店名称不能为空',
        'linkMan.require'         => '联系人不能为空',
//        'parentId.require'        => '请选择上级组织',
//        'parentId.number'         => '上级组织ID非法',
        'telePhone.require'       => '联系电话不能为空',
        'telePhone.checkPhone'    => '联系电话格式非法',
        'address.require'         => '请输入地址',
        'provinceId.require'      => '请选择省',
        'provinceId.number'       => '省份ID非法',
        'cityId.require'          => '请选择市',
        'cityId.number'           => '市ID非法',
        'areaId.require'          => '请选择地区',
        'areaId.number'           => '地区ID非法',
        'provinceName.require'    => '省份名称不能为空',
        'cityName.require'        => '城市名称不能为空',
        'areaName.require'        => '地区名称不能为空',
    ];

    protected $scene = [
//        'add'   =>  ['orgId','roleName'],
//        'edit'  =>  ['id', 'orgId', 'roleName'],
    ];

    public function checkPhone($phone){
        if(!checkPhone($phone)) {
            return false;
        }
        return true;
    }

    public function checkUrl($value){
        $value = explode(',', $value);
        foreach($value as $val){
            if(!filter_var($val, FILTER_VALIDATE_URL)) {
                return false;
            }
        }
        return true;
    }
}