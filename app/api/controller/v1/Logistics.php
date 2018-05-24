<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1;

use app\api\model\CustomerOrder;
use think\Controller;
use think\Db;
class Logistics extends Home
{

    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;

        $where = ['co.is_delete' => 0];
        $field = 'co.consignment_type as consignmentType,di.destination_type as destinationType,di.distribution_code as distributionCode,di.distribution_id as distributionId,di.distribution_state as distributionState,
                 di.driver_id as driverId,di.driver_name as driverName,di.driver_phone as driverPhone,ip.goods_car_ids,di.logistics_car_id';
        $join  = [
            ['logistics_consignment_in_pay ip', 'co.consignment_id=ip.consignment_id', 'left'],
            ['logistics_distribution di', 'ip.distribution_id=di.distribution_id', 'left'],
        ];
        $data  = Db::name('logistics_consignment co')->where($where)->field($field)->join($join)->order('co.create_date desc')->page($page, $rows)->select();
        if($data){
            $carsField = 'goods_car_id as goodsCarId,frame_number as frameNumber,consignment_id as consignmentId,consignment_code as consignmentCode,distribution_id as distributionId,distribution_code as distributionCode,
                         goods_car_state as goodsCarState,brand_id as brandId,brand_name as brandName,family_id as familyId,family_name as familyName,create_date as createDate,accept_image as acceptImage,deliver_to_image as deliverToImage,
                         cars_id as carsId,colour_id as colourId,cars_name as carsName,colour_name as colourName,sign_pic as signPic,interior_id as interiorId,interior_name as interiorName,sign_name as signName,followInformation';
            foreach($data as $key => &$value){
                $value['goodsCars']    = array();
                $value['logisticsCar'] = array();
                if(isset($value['goods_car_ids'])){
                    $goodsCars = Db::name('logistics_goods_car')->where(['goods_car_id' => ['in', $value['goods_car_ids']], 'is_delete' => 0])->field($carsField)->select();
                    $value['goodsCars'] = $goodsCars;
                }
                if(isset($value['logistics_car_id'])){

                }
            }

        }
        $this->apiReturn(200, $data);
    }

}