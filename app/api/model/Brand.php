<?php

/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2018-05-08
 * Time: 9:47
 */
namespace app\api\model;

use think\Cache;
use think\Db;
use think\Model;

class Brand extends Model
{

    protected $table = 'car_carbrand';

    public function getActivityById($id){
        if(empty($id) || !is_numeric($id)){
            return false;
        }

        return Db::name($this->table)->field('')->where(['a_id' => $id])->find();
    }

    public function getBrandList(){
        $cacheKey = md5('brand_list');
        if(!$data = cache($cacheKey)){
            $field = 'brandId as id,brandName as name,brandCode as code,brandInitial as initial,imgUrl as image';
            $res   = Db::name($this->table)->field($field)->order('brandInitial asc')->select();
            if($res){
                foreach($res as $key => $val){
                    $data[$val['initial']][] = $val;
                }
                unset($res, $val);
            }
            cache($cacheKey, $data, 86400);
        }

        return $data;
    }

    public function getCarFamilyByBrandId($brandId){
        if(!$brandId || !is_numeric($brandId)){
            return false;
        }

        $cacheKey = md5('brand_cars_family_' . $brandId);
        if(!$data = cache($cacheKey)){
            $list = Db::name('car_carfamily')->where(['brandId' => $brandId])->field('carFamilyId as id,carFamilyName as name,vehicle_name as type')->select();
            if($list){
                foreach($list as $key => $val){
                    $data[$val['type']][] = $val;
                }
                unset($val, $list);
            }
        }

        return $data;
    }

    public function getBrandListAll($field = ''){
        $cacheKey = md5('brand_list' . $field);
        if(!$data = cache($cacheKey)){
            if($field == ''){
                $field = 'brandId as id,brandName as name,brandCode as code,brandInitial as initial,imgUrl as image';
            }

            $data   = Db::name($this->table)->field($field)->order('brandInitial asc')->select();
            cache($cacheKey, $data, 86400);
        }

        return $data;
    }
}