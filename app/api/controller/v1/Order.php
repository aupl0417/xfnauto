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
class Order extends Home
{

    /**
     * 资源订单
     * @return json
     * */
    public function consumerList(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;
        if(isset($this->data['keywords'])&& !empty($this->data['keywords'])){
            $keywords= htmlspecialchars(trim($this->data['keywords']));
            $field = !preg_match('/^DG\d+/', $keywords) ? 'cars_name' : 'order_code';
            $where[$field] = ['like', '%' . $keywords . '%'];
        }
        $where = [
            'co.state'   => ['not in', [-1, 37]],
            'co.is_del'  => 0
        ];

        if(isset($this->data['state'])&& !empty($this->data['state'])){
            $where['co.state'] = $this->data['state'] + 0;
        }

        $where['co.creator_id'] = $this->userId;
        if($this->isRole){
            $userIds = model('SystemUser')->getUserByOrgId($this->orgId, 'usersId');
            if($userIds){
                $userIds = array_column($userIds, 'usersId');
                $where['co.creator_id'] = ['in', $userIds];
            }
        }

        if(isset($this->data['month']) && !empty($this->data['month'])){
            $month      = trim($this->data['month']);
            $monthStart = $month . '-01';
            !checkDateIsValid($monthStart) && $this->apiReturn(201, '', '输入年月格式非法');
            $monthEnd   = $month . '-' . date('t') . ' 23:59:59';
            $where['co.create_time']    = ['between', [date($monthStart), date($monthEnd)]];
        }


//        $where['co.org_id']         = $this->orgId;

        $data = model('ConsumerOrder')->getOrderList($where, $page, $rows);
        if($data){
            $field = 'brand_id as brandId,cars_id as carsId,brand_name as brandName,car_num as carNum,cars_name as carsName,change_price as changePrice,color_id as colorId,color_name as colorName,commercial_insurance_price as commercialInsurancePrice,create_time as createTime,customer_id as customerId,deposit_price as depositPrice,family_id as familyId,family_name as familyName,guide_price as guidePrice,id,interior_id as interiorId,interior_name as interiorName,naked_price as nakedPrice,order_id as orderId,remark,state,ticket_pic as ticketPic,traffic_compulsory_insurance_price as trafficCompulsoryInsurancePrice';
            $carField = 'oc.vin,oc.audit_remark as auditRemark,oc.audit_state as auditState,oc.brand_id as brandId,oc.brand_name as brandName,oc.cars_id as carsId,oc.cars_name as carsName,oc.certification_pic as certificationPic,oc.check_car_pic as checkCarPic,oc.ci_pic as ciPic,oc.color_id as colorId,oc.color_name as colorName,oc.create_time as createTime,oc.express_pic as expressPic,oc.family_id as familyId,oc.family_name as familyName,oc.id,oc.info_id as infoId,oc.interior_id as interiorId,oc.interior_name as interiorName,oc.is_del as isDel,oc.other_pic as otherPic,oc.stock_car_id as stockCarId,oc.tci_pic as tciPic,oc.ticket_pic as ticketPic,oc.ticket_remark as ticketRemark,sc.frame_number as frameNumber,sc.engine_number';
            foreach($data as $key => &$value){
                $value['infos'] = Db::name('consumer_order_info')->field($field)->where(['order_id' => $value['id']])->select();
                if($value['infos']){
                    foreach($value['infos'] as $k => &$val){
                        $val['cars'] = Db::name('consumer_order_car oc')->where(['oc.cars_id' => $val['carsId']])->join('stock_car sc', 'sc.stock_car_id=oc.stock_car_id')->field($carField)->select();
//                        $val['cars'] = $cars ? array_column($cars, 'vin') : [];
                    }
                }
            }
        }
        $this->apiReturn(200, $data);
    }

    /**
     * 用户订单列表
     * @return json
     * */
    public function customerList(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;
        if(isset($this->data['keywords'])&& !empty($this->data['keywords'])){
            $keywords= htmlspecialchars(trim($this->data['keywords']));
            $field = !preg_match('/^DD\d+/', $keywords) ? 'cars_name' : 'customer_order_code';
            $where[$field] = ['like', '%' . $keywords . '%'];
        }

        if(isset($this->data['state'])&& !empty($this->data['state'])){
            $state = $this->data['state'] + 0;
            $where['customer_order_state'] = $state;
            if($state == 6){
                $where['customer_order_state'] = ['in', [7, 9, 11]];
            }
        }

//        if(!$this->isRole){
//            $where['system_user_id'] = $this->userId;
//        }
        $group = model('SystemUser')->getUserGroupInfo($this->userId);
        if($group['over_manage'] == 1){
            $where['org_id']         = $group['orgId'];
        }else{
            $where['system_user_id'] = $this->userId;
        }
        $where['create_date']    = ['between', [date('Y-m-01'), date('Y-m-t 23:59:59')]];
        $where['is_delete']      = 0;

        $data = model('CustomerOrder')->getOrderList($where, $page, $rows);
        $this->apiReturn(200, $data);
    }

    /**
     * 费用统计
     * @return json
     * */
    public function statistics(){
        $page = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;
        $type = isset($this->data['type']) && !empty($this->data['type']) ? htmlspecialchars(trim($this->data['type'])) : 'customer';
        $mode = isset($this->data['mode']) && !empty($this->data['mode']) ? htmlspecialchars(trim($this->data['mode'])) : 'insurance';
        !in_array($type, ['customer', 'consumer']) && $this->apiReturn(201, '', '类型非法');

        if($type == 'customer'){
            !in_array($mode, ['insurance', 'mortgage', 'boutique', 'license']) && $this->apiReturn(201, '', '统计类型非法');
            $model = 'CustomerOrder';
        }else{
            !in_array($mode, ['traffic', 'commercial']) && $this->apiReturn(201, '', '统计类型非法');
            $model = 'ConsumerOrder';
        }
        $data = model($model)->orderFeeList($mode, $this->userId, $this->orgId, $this->isRole, $page, $rows);
//        echo model($model)->getLastSql();die;
        $this->apiReturn(200, $data);
    }

}