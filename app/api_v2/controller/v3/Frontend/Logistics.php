<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api_v2\controller\v3\Frontend;

use app\api\model\CustomerOrder;
use think\Controller;
use think\Db;
class Logistics extends Home
{


    /**
     * 物流单列表
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;

        $where = ['co.is_delete' => 0, 'di.distribution_code' => ['neq', ''], 'di.is_delete' => 0, 'di.org_id' => ['in', $this->orgIds]];
        $field = 'co.consignment_type as consignmentType,di.destination_type as destinationType,di.distribution_code as distributionCode,di.distribution_id as distributionId,di.distribution_state as distributionState,
                 di.driver_id as driverId,di.driver_name as driverName,di.driver_phone as driverPhone,ip.goods_car_ids,di.logistics_car_id';
        $join  = [
            ['logistics_consignment_in_pay ip', 'di.distribution_id=ip.distribution_id'],
            ['logistics_consignment co', 'co.consignment_id=ip.consignment_id', 'left'],
        ];

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            if(!preg_match('/^PS\w+/', $keywords)){
                $distributionIds = Db::name('logistics_goods_car')->where(['cars_name' => ['like', '%' . $keywords . '%'], 'is_delete' => 0])->field('distribution_id')->select();
                if($distributionIds){
                    $distributionIds = array_column($distributionIds, 'distribution_id');
                    $where['di.distribution_id'] = ['in', $distributionIds];
                }
            }else{
                $where['di.distribution_code'] = ['like', '%' . $keywords . '%'];
            }
        }

        $data  = Db::name('logistics_distribution di')->where($where)->field($field)->join($join)->order('di.distribution_id desc')->group('ip.consignment_id')->page($page, $rows)->select();
        $count = Db::name('logistics_distribution di')->where($where)->field($field)->join($join)->order('di.distribution_id desc')->group('ip.consignment_id')->count();
        if($data){
            $carsField = $this->createField('logistics_goods_car', 'is_delete');
            $logisticsCarField = $this->createField('logistics_car', 'is_delete');
            $state = [''];
            foreach($data as $key => &$value){
                $value['goodsCars']    = array();
                $value['logisticsCar'] = array();
                if(isset($value['goods_car_ids'])){
                    $map = ['goods_car_id' => ['in', $value['goods_car_ids']], 'is_delete' => 0];
                    $value['goodsCars'] = Db::name('logistics_goods_car')->where($map)->field($carsField)->select();
                }
                if(isset($value['logistics_car_id'])){
                    $map = ['logistics_car_id' => $value['logistics_car_id'], 'is_enable' => 1, 'is_delete' => 0];
                    $value['logisticsCar'] = Db::name('logistics_car')->where($map)->field($logisticsCarField)->find();
                }
            }

        }
        $this->apiReturn(200, ['list' => $data, 'page' => $page, 'rows' => $rows, 'total' => $count]);
    }


    /**
     * 托运单列表
     * */
    public function consignment(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;

        $field = 'consignment_id as consignmentId,consignment_code as consignmentCode,consignment_type as consignmentType,amount,appointment_time as appointmentTime,consignment_state as consignmentState,create_date as createDate,destination_address as destinationAddress,starting_point_address as startingPointAddress';
        $where = ['is_delete' => 0, 'org_id' => ['in', $this->orgIds]];
//        $where = ['is_delete' => 0];

        if(isset($this->data['consignmentType']) && !empty($this->data['consignmentType'])){
            $consignmentType = $this->data['consignmentType'] + 0;
            !in_array($consignmentType, [1, 2], true) && $this->apiReturn(201, '', '配送方式类型非法');
            $where['consignment_type'] = $consignmentType;
        }

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            if(!preg_match('/^TY\w+/', $keywords)){
                $consignmentIds = Db::name('logistics_goods_car')->where(['cars_name' => ['like', '%' . $keywords . '%'], 'is_delete' => 0])->field('consignment_id')->select();
                if($consignmentIds){
                    $consignmentIds = array_column($consignmentIds, 'consignment_id');
                }
                $orgs = Db::name('logistics_consignment')->where(['org_name' => ['like', '%' . $keywords . '%'], 'is_delete' => 0, 'org_id' => ['in', $this->orgIds]])->field('consignment_id')->select();
                if($orgs){
                    $consignmentIds = array_merge($consignmentIds, array_column($orgs, 'consignment_id'));
                }
                $where['consignment_id'] = ['in', $consignmentIds];
            }else{
                $where['consignment_code'] = ['like', '%' . $keywords . '%'];
            }
        }

        $data  = Db::name('logistics_consignment')->where($where)->field($field)->page($page, $rows)->order('appointment_time desc')->select();
        $count = Db::name('logistics_consignment')->where($where)->count();
        if($data){
            $consignmentIds   = array_column($data, 'consignmentId');
            $consignmentInPay = Db::name('logistics_consignment_in_pay')->where(['consignment_id' => ['in', $consignmentIds]])->field('consignment_in_pay_id as id,consignment_id,goods_car_ids')->select();
            $cars = [];
            if($consignmentInPay){
                $temp = [];
                foreach($consignmentInPay as $k => $val){
                    if(!isset($cars[$val['consignment_id']])){
                        $cars[$val['consignment_id']] = '';
                    }
                    if(!isset($temp[$val['consignment_id']])){
                        $temp[$val['consignment_id']] = [];
                    }
                    if(!in_array($val['goods_car_ids'], $temp[$val['consignment_id']])){
                        $cars[$val['consignment_id']] .= $cars[$val['consignment_id']] ? ',' . $val['goods_car_ids'] : $val['goods_car_ids'];
                        $temp[$val['consignment_id']][] = $val['goods_car_ids'];
                    }
                }
            }

            $carsField = $this->createField('logistics_goods_car');
            foreach($data as $key => &$value){
                $list = [];
                if(isset($cars[$value['consignmentId']])){
                    $list = Db::name('logistics_goods_car')->where(['goods_car_id' => ['in', $cars[$value['consignmentId']]]])->field($carsField)->select();
                }
                $value['list'] = $list;
            }
        }
        $this->apiReturn(200, ['list' => $data, 'page' => $page, 'rows' => $rows, 'total' => $count]);
    }

}