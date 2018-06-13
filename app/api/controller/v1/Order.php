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
use think\Exception;

class Order extends Home
{

    /**
     * 资源订单
     * @return json
     * */
    public function consumerList(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;

        $where = [
            'co.state'   => ['not in', [-1, 37]],
            'co.is_del'  => 0
        ];

        if(isset($this->data['keywords'])&& !empty($this->data['keywords'])){
            $keywords= htmlspecialchars(trim($this->data['keywords']));
//            $field = !preg_match('/^DG\d+/', $keywords) ? 'cars_name' : 'order_code';
//            $where[$field] = ['like', '%' . $keywords . '%'];
            $orderIds = Db::name('consumer_order_info')->where(['cars_name' => ['like', '%' . $keywords . '%'], 'is_del' => 0])->field('order_id')->select();
            if($orderIds){
                $orderIds = array_column($orderIds, 'order_id');
            }
            $ids = Db::name('consumer_order')->where(['order_code' => ['like', '%' . $keywords . '%'], 'is_del' => 0])->field('id')->select();
            if($ids){
                $ids = array_column($ids, 'id');
            }
            $ids = array_merge($ids, $orderIds);
            $where['co.id'] = ['in', $ids];
        }

        if(isset($this->data['state'])&& !empty($this->data['state'])){
            $where['co.state'] = $this->data['state'] + 0;
        }

        $where['co.creator_id'] = ['in', $this->userIds];
        /*if($this->isRole){
            $userIds = model('SystemUser')->getUserByOrgId($this->orgId, 'usersId');
            if($userIds){
                $userIds = array_column($userIds, 'usersId');
                $where['co.creator_id'] = ['in', $userIds];
            }
        }*/
//        if(count($this->orgIds) > 1){//如果是大于1，则有下级
//            $userIds = model('SystemUser')->getDataAll(['orgId' => ['in', $this->orgIds], 'isEnable' => 1], 'usersId');
//            if($userIds){
//                $userIds = array_column($userIds, 'usersId');
//                $where['co.creator_id'] = ['in', $userIds];
//            }
//        }

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
            $field = 'id,brand_id as brandId,cars_id as carsId,brand_name as brandName,car_num as carNum,cars_name as carsName,change_price as changePrice,color_id as colorId,color_name as colorName,commercial_insurance_price as commercialInsurancePrice,create_time as createTime,customer_id as customerId,deposit_price as depositPrice,family_id as familyId,family_name as familyName,guide_price as guidePrice,id,interior_id as interiorId,interior_name as interiorName,naked_price as nakedPrice,order_id as orderId,remark,state,ticket_pic as ticketPic,traffic_compulsory_insurance_price as trafficCompulsoryInsurancePrice';
            $carField = 'oc.vin,oc.audit_remark as auditRemark,oc.audit_state as auditState,oc.brand_id as brandId,oc.brand_name as brandName,oc.cars_id as carsId,oc.cars_name as carsName,oc.certification_pic as certificationPic,oc.check_car_pic as checkCarPic,oc.ci_pic as ciPic,oc.color_id as colorId,oc.color_name as colorName,oc.create_time as createTime,oc.express_pic as expressPic,oc.family_id as familyId,oc.family_name as familyName,oc.id,oc.info_id as infoId,oc.interior_id as interiorId,oc.interior_name as interiorName,oc.is_del as isDel,oc.other_pic as otherPic,oc.stock_car_id as stockCarId,oc.tci_pic as tciPic,oc.ticket_pic as ticketPic,oc.ticket_remark as ticketRemark,sc.frame_number as frameNumber,sc.engine_number';
            foreach($data as $key => &$value){
                $value['infos'] = Db::name('consumer_order_info')->field($field)->where(['order_id' => $value['id'], 'is_del' => 0])->select();
                if($value['infos']){
                    foreach($value['infos'] as $k => &$val){
                        $val['cars'] = Db::name('consumer_order_car oc')->where(['oc.cars_id' => $val['carsId'], 'oc.is_del' => 0, 'oc.info_id' => $val['id']])->join('stock_car sc', 'sc.stock_car_id=oc.stock_car_id')->field($carField)->select();
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
            }elseif ($state == 12){
                $where['customer_order_state'] = ['in', [13, 15, 17]];
            }
        }

        if(isset($this->data['month']) && !empty($this->data['month'])){
            $month      = trim($this->data['month']);
            $monthStart = $month . '-01';
            !checkDateIsValid($monthStart) && $this->apiReturn(201, '', '输入年月格式非法');
            $monthEnd   = $month . '-' . date('t') . ' 23:59:59';
            $where['create_date']    = ['between', [date($monthStart), date($monthEnd)]];
        }

        $where['system_user_id'] = ['in', $this->userIds];
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
        $data = model($model)->orderFeeList($mode, $this->userIds, $this->orgIds, $this->isRole, $page, $rows);
//        echo model($model)->getLastSql();die;
        $this->apiReturn(200, $data);
    }

    /*
     * 更新订单的出库状态
     * */
    public function stockOut(){
        (!isset($this->data['orderId']) || empty($this->data['orderId'])) && $this->apiReturn(201, '', '订单ID非法');
        (!isset($this->data['state'])   || empty($this->data['state']))   && $this->apiReturn(201, '', '状态非法');

        $orderId = $this->data['orderId'] + 0;
        $state   = $this->data['state'] + 0;

        Db::startTrans();
        try{
            $result = Db::name('consumer_order')->where(['id' => $orderId])->update(['state' => $state]);
            if($result === false){
                throw new Exception('更新状态失败', 1);
            }
            $stockCar = Db::name('consumer_order_info oi')->where(['oi.order_id' => $orderId])->join('consumer_order_car oc', 'oc.info_id=oi.id')->field('stock_car_id')->select();
            if(!$stockCar){
                throw new Exception('数据不存在', 2);
            }
            $stockIds = array_column($stockCar, 'stock_car_id');
            $result   = Db::name('stock_car')->where(['stock_car_id' => ['in', $stockIds]])->update(['is_put_out' => 1]);
            if($result === false){
                throw new Exception('更新库存表出库状态失败', 3);
            }
            Db::commit();
            $this->apiReturn(200, '', '更新成功');
        }catch (Exception $e){
            Db::rollback();
            $this->apiReturn(201, '', '更新失败');
        }
    }

    public function stockList(){
        $page = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;

        $where = [
            'is_delete' => 0
        ];
        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            $where['storage_code'] = ['like', '%' . $keywords . '%'];
        }

        if(isset($this->data['storageCode']) && !empty($this->data['storageCode'])){
            $storageCode = htmlspecialchars(trim($this->data['storageCode']));
            $where['storage_code'] = ['like', '%' . $storageCode . '%'];
        }

        $where['org_id'] = ['in', $this->orgIds];
        $field = 'storage_id as storageId,storage_code as storageCode,create_date as createDate,supplier_id as supplierId,supplier_name as supplierName,system_users_id as systemUsersId,
                 system_user_name as systemUserName,total_purchase_price as totalPurchasePrice,total_purchase as totalPurchase,logistics_cost as logisticsCost,storage_source as storageSource,
                 org_id as orgId,org_name as orgName,remarks,contract_number as contractNumber,contract_image as contractImage,over_sure as overSure';
        $data  = Db::name('stock_storage')->where($where)->page($page, $rows)->field($field)->select();
        if($data){
            foreach($data as $key => &$value){
                $map = ['storage_id' => $value['storageId'], 'is_delete' => 0, 'is_put_out' => 0];
                $value['carsNumber'] = Db::name('stock_car')->where($map)->sum('number');
            }
        }

        $count = Db::name('stock_storage')->where($where)->count();
        $this->apiReturn(200, ['list' => $data, 'total' => $count, 'page' => $page, 'rows' => $rows]);
    }

    /**
     * 等待车辆出库列表
     * */
    public function stockCarList(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;

        $where = ['customer_order_state' => 5, 'is_delete' => 0, 'org_id' => ['in', $this->orgIds]];

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            $where['cars_name'] = ['like', '%' . $keywords . '%'];
        }

        $field = 'cars_id as carsId,cars_name as carsName,colour_id as colourId,colour_name as colourName,create_date as createDate,customer_name as customerName,customer_order_code as customerOrderCode,
        customer_order_id as customerOrderId,interior_id as interiorId,interior_name as interiorName,1 as number';
        $count = Db::name('customer_order')->where($where)->count();
        $data  = Db::name('customer_order')->where($where)->field($field)->page($page, $rows)->order('customer_order_id desc')->select();
        if($data){
            foreach($data as &$value){
                $value['stockCarNumber'] = Db::name('stock_car')->where(['order_id' => $value['customerOrderId'], 'is_delete' => 0])->sum('stock_car_id');
            }
        }
        $this->apiReturn(200, ['list' => $data, 'page' => $page, 'rows' => $rows, 'total' => $count]);
    }

    /**
     * 精品加装/等待上牌/等待贴膜/待交车列表
     * */
    public function carsProductList(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;

        $state = (isset($this->data['state']) && !empty($this->data['state'])) ? $this->data['state'] + 0 : 7;
        !in_array($state, [7, 9, 11, 12], true) && $this->apiReturn(201, '', '参数非法');

        if($state == 12){
            $state = ['in', [13, 15, 17]];
        }

        $where = ['co.customer_order_state' => $state, 'co.is_delete' => 0, 'co.org_id' => ['in', $this->orgIds]];

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            $where['cars_name'] = ['like', '%' . $keywords . '%'];
        }

        $field = 'co.cars_id as carsId,co.cars_name as carsName,co.colour_id as colourId,co.colour_name as colourName,co.create_date as createDate,co.customer_name as customerName,co.customer_order_code as customerOrderCode,
        co.customer_order_id as customerOrderId,co.interior_id as interiorId,co.interior_name as interiorName,co.estimate_Date as estimateDate,car.frame_number as frameNumber,co.is_mortgage as isMortgage,over_the_line as overTheLine,
        co.system_user_id as systemUserId,co.system_user_name as systemUserName,co.system_user_phone as systemUserPhone';
        $join = [
            ['customer_car car', 'car.customer_order_id=co.customer_order_id', 'left'],
        ];
        $count = Db::name('customer_order co')->where($where)->join($join)->count();
        $data  = Db::name('customer_order co')->where($where)->field($field)->join($join)->page($page, $rows)->order('co.customer_order_id desc')->select();
        $this->apiReturn(200, ['list' => $data, 'page' => $page, 'rows' => $rows, 'total' => $count]);
    }

    /**
     * 资源单更新
     * */
    public function consumerUpdate(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');
        $id = $this->data['id'] + 0;
        unset($this->data['id'], $this->data['sessionId']);
    }

}