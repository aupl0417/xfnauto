<?php
/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2018-05-31
 * Time: 14:08
 */

namespace app\api\controller\v1\Backend;

use think\Controller;
use think\Db;
class Car extends Admin
{

    /**
     * 首页
     * @return json
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 50;

        $where = ['isDelete' => 0];

        if(isset($this->data['carsName']) && !empty($this->data['carsName'])){
            $carsName = htmlspecialchars(trim($this->data['carsName']));
            $where['carName'] = ['like', '%' . $carsName];
        }

        $field = $this->createField('car_cars', 'isDelete,sort,indexImage,brandInitial,driveStyle,carName') . ',carId as carsId,carName as carsName';
        $count = Db::name('car_cars')->where($where)->count();
        $data  = Db::name('car_cars')->where($where)->field($field)->page($page, $rows)->order('carId desc')->select();
        $this->apiReturn(200, ['list' => $data, 'page' => $page, 'rows' => $rows, 'total' => $count]);
    }

    public function family(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 50;

        $where = [];
        if(isset($this->data['brandId']) && !empty($this->data['brandId'])){
            $brandId = $this->data['brandId'] + 0;
            $where['brandId'] = $brandId;
        }

        $count = Db::name('car_carfamily')->where($where)->count();
        $data  = Db::name('car_carfamily')->where($where)->page($page, $rows)->field('carFamilyId,brandId,carFamilyName')->order('carFamilyId desc')->select();
        $this->apiReturn(200, ['list' => $data ?: [], 'page' => $page, 'rows' => $rows, 'total' => $count]);
    }
    
    

}