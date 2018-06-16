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


class Organization extends Validate{

    protected $rule = [
        'shortName'           => 'require',
//        'orgLevel'            => 'require|in:1,2,3',
        'orgType'             => 'require|in:1,2,3,4',
//        'parentId'            => 'require|number',
        'telephone'           => 'require|checkPhone',
        'address'             => 'require',
        'longitude'           => 'number',//经度
        'latitude'            => 'number',//纬度
        'provinceId'          => 'require|number',
        'cityId'              => 'require|number',
        'areaId'              => 'require|number',
        'introduce'           => 'require',
        'bankAccount'         => 'require',
        'nameOfAccount'       => 'require',
        'bankName'            => 'require',
        'openingBranch'       => 'require',
        'imageurl'            => 'require|checkUrl',
        'signet'              => 'checkUrl',
    ];

    protected $message = [
        'shortName.require'       => '名称不能为空',
//        'orgLevel.require'        => '请选择级别',
//        'orgLevel.in'             => '级别非法',
        'orgType.require'         => '请选择性质',
        'orgType.in'              => '性质非法',
//        'parentId.require'        => '请选择上级组织',
//        'parentId.number'         => '上级组织ID非法',
        'telephone.require'       => '请输入手机号码',
        'telephone.checkPhone'    => '手机号码格式非法',
        'address.require'         => '请输入地址',
        'longitude.number'        => '经度必须是数字',
        'latitude.number'         => '纬度必须是数字',
        'provinceId.require'      => '请选择省',
        'provinceId.number'       => '省份ID非法',
        'cityId.require'          => '请选择市',
        'cityId.number'           => '市ID非法',
        'areaId.require'          => '请选择地区',
        'areaId.number'           => '地区ID非法',
        'introduce.require'       => '请输入简要介绍',
        'bankAccount.require'     => '请输入银行卡账号',
        'nameOfAccount.require'   => '请输入开户姓名',
        'bankName.require'        => '请输入银行名称',
        'openingBranch.require'   => '请输入开户支行',
        'imageurl.require'        => '请上传图片',
        'imageurl.checkUrl'       => '图片地址非法',
        'signet.checkUrl'         => '印章地址非法',
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