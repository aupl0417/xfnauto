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


class Shop extends Validate{

    protected $rule = [
        'type'            => 'require|in:1,2,3',
        'shopName'        => 'require',
        'provinceId'      => 'require|number',
        'cityId'          => 'require|number',
        'areaId'          => 'require|number',
        'address'         => 'require',
        'corporation'     => 'require',
        'phone'           => 'require|checkPhone',
        'idCard'          => 'require|url',
        'license'         => 'require|url',
        'image'           => 'require|url',
    ];

    protected $message = [
        'type.require'        => '请选择商家类型',
        'type.in'             => '商家类型非法',
        'shopName.require'    => '请输入店铺名称',
        'provinceId.require'  => '请选择省份',
        'provinceId.number'   => '省份ID格式非法',
        'cityId.require'      => '请选择城市',
        'cityId.number'       => '城市ID格式非法',
        'areaId.require'      => '请选择地区',
        'areaId.number'       => '地区ID格式非法',
        'address.require'     => '请输入详细地址',
        'corporation.require' => '请输入法人姓名',
        'phone.require'       => '请输入法人联系电话',
        'phone.checkPhone'    => '法人联系电话格式非法',
        'idCard.require'      => '请上传法人身份证照片',
        'idCard.url'          => '法人身份证照片地址非法',
        'license.require'     => '请上传营业执照',
        'license.url'         => '营业执照地址非法',
        'image.require'       => '请上传店铺照片',
        'image.url'           => '店铺照片地址非法',
    ];

    public function checkPhone($phone){
        return true;
        if(!checkPhone($phone)) {
            return false;
        }
        return true;
    }
}