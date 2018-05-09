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

class Car extends Model
{

    protected $table = 'car_cars';

    public function getCarById($id, $field = '*'){
        if(empty($id) || !is_numeric($id)){
            return false;
        }

        return Db::name('car_cars')->field($field)->where(['carId' => $id, 'isDelete' => 0])->find();
    }

    public function getCarByFamilyId($familyId, $field = '*'){
        if(empty($familyId) || !is_numeric($familyId)){
            return false;
        }

        $cacheKey = md5('get_car_by_family_id_' . $familyId . $field);
        if(!$data = cache($cacheKey)){
            $data = Db::name('car_cars')->field($field)
                ->where(['familyId' => $familyId, 'isDelete' => 0])
                ->order('carId desc')
                ->select();
            cache($cacheKey, $data, 3600);
        }
        return $data;
    }
}