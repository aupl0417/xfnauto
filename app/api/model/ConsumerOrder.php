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

class ConsumerOrder extends Model
{

    protected $table = 'consumer_order';
    protected $state = [
        '1' => '新建',
        '5' => '待收定金',
        '10' => '待配车',
        '15' => '待验车',
        '20' => '换车申请',
        '25' => '待换车',
        '30' => '待协商',
        '35' => '待收尾款',
        '37' => '已退款',
        '40' => '待出库',
        '45' => '待上传票证',
        '50' => '完成',
    ];

    public function getOrderById($id, $field = '*'){
        if(empty($id) || !is_numeric($id)){
            return false;
        }

        return Db::name($this->table)->field($field)->where(['id' => $id])->find();
    }

    public function getOrderList($where = '', $page = 1, $pageSize = 10){
        $field = 'co.id as id,co.order_code as orderId,co.state as orderState';
        $data  = Db::name('consumer_order co')->where($where)->page($page, $pageSize)
               ->field($field)->order('create_time desc')
               ->select();//->join('consumer_order_info oi', 'co.id=oi.order_id', 'left')
        if($data){
            $field = 'id,cars_name as carName,color_name as colorName,interior_name as interiorName,state as orderInfoState,car_num as carNum';
            foreach($data as $key => &$value){
                $value['orderStateName'] = $this->state[$value['orderState']];
                $value['infos'] = Db::name('consumer_order_info')->where(['order_id' => $value['id'], 'is_del' => 0])->field($field)->select();
            }
        }

        return $data;
    }

    /*
     * 单月各状态的订单统计
     * */
    public function orderCount($condition = '', $userId, $orgId, $isRole = false){
        $where['creator_id']  = ['in', $userId];
        $where['org_id']      = ['in', $orgId];
        $where['create_time'] = ['between', [date('Y-m-01'), date('Y-m-t 23:59:59')]];
        $obj  = Db::name($this->table)->where($where);
        $cond = array('is_del' => 0);
        if($condition){
            if(!is_array($condition)){
                $condition = explode(',', $condition);
                $cond['state'] = ['in', $condition];
            }else{
                $cond['state'] = $condition;//支持['in/between', []]形式
            }
        }else{
            $cond['state'] =  ['not in', [-1, 37]];
        }

        $obj = $obj->where($cond);

        return $obj->count();;
    }

    /*
     * 订单各费用统计
     * */
    public function orderFeeCount($type, $userId, $orgId, $isRole = false){
        $where = [
            'co.state'       => ['not in', [-1, 37]],
            'co.is_del'      => 0,
            'co.creator_id'  => ['in', $userId],
            'co.create_time' => ['between', [date('Y-m-01'), date('Y-m-t 23:59:59')]],
            'co.org_id'      => ['in', $orgId]
        ];

        $obj = Db::name('consumer_order co')->where($where)->join('consumer_order_info oi', 'co.id=oi.order_id', 'left');
        switch ($type){
            case 'traffic':
                $total = $obj->sum('traffic_compulsory_insurance_price');
                break;
            case 'commercial':
                $total = $obj->sum('commercial_insurance_price');
                break;
        }

        return $total;
    }

    /*
     * 订单各费用统计列表
     * */
    public function orderFeeList($type, $userId, $orgId, $isRole = false, $page = 1, $pageSize = 10){
        $where = [
            'co.state'       => ['not in', [-1, 37]],
            'co.is_del'      => 0,
            'co.creator_id'  => ['in', $userId],
            'co.create_time' => ['between', [date('Y-m-01'), date('Y-m-t 23:59:59')]],
            'co.org_id'      => ['in', $orgId]
        ];

        $field = 'co.id as id,co.order_code as orderId,co.state as orderState,oi.cars_name as carName,oi.color_name as colorName,oi.interior_name as interiorName,oi.state as orderInfoState,oi.car_num as carNum';
        switch ($type){
            case 'traffic':
                $where['oi.traffic_compulsory_insurance_price'] = ['>', 0];
                break;
            case 'commercial':
                $where['oi.commercial_insurance_price'] = ['>', 0];
                break;
        }

        return Db::name('consumer_order co')->where($where)->field($field)->page($page, $pageSize)->join('consumer_order_info oi', 'co.id=oi.order_id', 'left')->order('co.id desc')->select();
    }

    public function getOrderDetailByOrderId($orderId){
        $field = 'co.id as id,co.order_code as orderCode,co.state as state,org_id as orgId,org_name as orgName,
        org_linker as orgLinker,org_phone as orgPhone,logistics_type as logisticsType,logistics_plate_number as logisticsPlateNumber,
        logistics_driver as logisticsDriver,logistics_driver_phone as logisticsDriverPhone,logistics_company as logisticsCompany,
        logistics_order_code as logisticsOrderCode,pick_car_date as pickCarDate,pick_car_addr as pickCarAddr,
        create_time as createTime,creator_id as creatorId,creator,freight,shortName as partyA,bankName,openingBranch as bankBranch,bankAccount as bankCardNum,
        out_stocker as outStocker,countermand_reason as countermandReason,countermand_apply as countermandApply,countermand_pic as countermandPic,order_type as orderType,out_stock_time as outStockTime,nameOfAccount as bankAccountName,signet';
        $where = ['id' => $orderId];
        $join  = [
            ['system_user su', 'su.usersId=co.creator_id', 'left'],
            ['system_organization org', 'su.orgId=org.orgId', 'left']
        ];
        $data  = Db::name('consumer_order co')->where($where)->field($field)->join($join)->order('create_time desc')->find();
        if($data){
            $data['bankBranch'] = $data['bankName'] . ' ' . $data['bankBranch'];
            $field = 'id,cars_name as carName,color_name as colorName,interior_name as interiorName,state as orderInfoState,car_num as carNum';
            $data['orderStateName']    = $this->state[$data['state']];
            $data['totalDepositPrice'] = Db::name('consumer_order_info')->where(['order_id' => $orderId, 'is_del' => 0])->sum('deposit_price');
            $data['totalFinalPrice']   = 0;
            $data['totalRestPrice']    = 0;
            $orderInfo = Db::name('consumer_order_info')->where(['order_id' => $orderId, 'is_del' => 0])->field('naked_price,traffic_compulsory_insurance_price,commercial_insurance_price,car_num')->select();
            if($orderInfo){
                $total = 0;
                foreach($orderInfo as $vo){
                    $total += ($vo['naked_price'] + $vo['traffic_compulsory_insurance_price'] + $vo['commercial_insurance_price']) * $vo['car_num'];
                }
                $data['totalFinalPrice']   = $total + $data['freight'];
                $data['totalRestPrice']    = $data['totalFinalPrice'] - $data['totalDepositPrice'];
            }

            $orderUserField            = 'id,order_id as orderId,user_name as userName,user_phone as userPhone,id_card as idCard,id_card_pic_on as idCardPicOn,id_card_pic_off as idCardPicOff,type,create_time as createTime,is_del as isDel';
            $data['customers']         = Db::name('consumer_order_user')->where(['order_id' => $orderId, 'type' => 1])->field($orderUserField)->select();
            if($data['customers']){
                $field = 'id,cars_id as carsId,cars_name as carsName,order_id as orderId,customer_id as customerId,brand_id as brandId,brand_name as brandName,color_id as colorId,color_name as colorName,
                interior_id as interiorId,interior_name as interiorName,family_id as familyId,family_name as familyName,car_num as carNum,guide_price as guidePrice,deposit_price as depositPrice,naked_price as nakedPrice,
                traffic_compulsory_insurance_price as trafficCompulsoryInsurancePrice,commercial_insurance_price as commercialInsurancePrice,change_price as changePrice,ticket_pic as ticketPic,remark,create_time as createTime,state';
//                $field = 'brand_id as brandId,cars_id as carsId,brand_name as brandName,car_num as carNum,cars_name as carsName,change_price as changePrice,color_id as colorId,color_name as colorName,commercial_insurance_price as commercialInsurancePrice,create_time as createTime,customer_id as customerId,deposit_price as depositPrice,family_id as familyId,family_name as familyName,guide_price as guidePrice,id,interior_id as interiorId,interior_name as interiorName,naked_price as nakedPrice,order_id as orderId,remark,state,ticket_pic as ticketPic,traffic_compulsory_insurance_price as trafficCompulsoryInsurancePrice';
                $carField = 'oc.vin,oc.audit_remark as auditRemark,oc.audit_state as auditState,oc.brand_id as brandId,oc.brand_name as brandName,oc.cars_id as carsId,oc.cars_name as carsName,oc.certification_pic as certificationPic,oc.check_car_pic as checkCarPic,oc.ci_pic as ciPic,oc.color_id as colorId,oc.color_name as colorName,oc.create_time as createTime,oc.express_pic as expressPic,oc.family_id as familyId,oc.family_name as familyName,oc.id,oc.info_id as infoId,oc.interior_id as interiorId,oc.interior_name as interiorName,oc.is_del as isDel,oc.other_pic as otherPic,oc.stock_car_id as stockCarId,oc.tci_pic as tciPic,oc.ticket_pic as ticketPic,oc.ticket_remark as ticketRemark,sc.frame_number as frameNumber,sc.engine_number';

                foreach($data['customers'] as $key => &$value){
                    $value['infos'] = Db::name('consumer_order_info')->field($field)->where(['order_id' => $orderId, 'customer_id' => $value['id'], 'is_del' => 0])->select();
                    if($value['infos']){
                        foreach($value['infos'] as $k => &$val){
                            $val['cars'] = Db::name('consumer_order_car oc')->where(['oc.info_id' => $val['id']])->join('stock_car sc', 'sc.stock_car_id=oc.stock_car_id')->field($carField)->select();
                        }
                    }
                }
            }
            $data['orderPaymentVOs'] = Db::name('consumer_order_payment')->where(['order_id' => $orderId])->field('id,order_id as orderId,amount,type,voucher,remark')->select();
        }

        return $data;
    }

    public function getOrderListAll($where = '', $page = 1, $pageSize = 10){
        $field = 'id as id,order_code as orderId,state as orderState,org_name as orgName,order_type as orderType,freight,creator,create_time as createTime,countermand_apply as countermandApply,countermand_reason as countermandReason,countermand_pic as countermandPic';
        $count = Db::name('consumer_order')->where($where)->count();
        $data  = Db::name('consumer_order')->where($where)->page($page, $pageSize)
            ->field($field)->order('id desc')
            ->select();
        
        if($data){
            foreach($data as $key => &$value){
                $orderId = $value['id'];
                $value['orderStateName']    = $value['countermandApply'] ? ($value['countermandApply'] == 1 ? '申请退款中' : '已退款') : $this->state[$value['orderState']];
                $value['totalDepositPrice'] = Db::name('consumer_order_info')->where(['order_id' => $orderId])->sum('deposit_price');
                $value['totalFinalPrice']   = 0;
                $value['totalRestPrice']    = 0;
                $orderInfo = Db::name('consumer_order_info')->where(['order_id' => $orderId, 'is_del' => 0])->field('naked_price,traffic_compulsory_insurance_price,commercial_insurance_price,car_num')->select();
//                echo Db::name('consumer_order_info')->getLastSql();die;
                if($orderInfo){
                    $total = 0;
                    foreach($orderInfo as $vo){
                        $total += ($vo['naked_price'] + $vo['traffic_compulsory_insurance_price'] + $vo['commercial_insurance_price']) * $vo['car_num'];
                    }
                    $value['totalFinalPrice']   = $total + $value['freight'];
                    $value['totalRestPrice']    = $value['totalFinalPrice'] - $value['totalDepositPrice'];
                }

                $orderUserField      = 'id,order_id as orderId,user_name as userName,user_phone as userPhone,id_card as idCard,id_card_pic_on as idCardPicOn,id_card_pic_off as idCardPicOff,type,create_time as createTime,is_del as isDel';
                $value['customers']  = Db::name('consumer_order_user')->where(['order_id' => $orderId, 'type' => 1])->field($orderUserField)->select();
                if($value['customers']){
                    $field = 'id,cars_id as carsId,cars_name as carsName,order_id as orderId,customer_id as customerId,brand_id as brandId,brand_name as brandName,color_id as colorId,color_name as colorName,
                interior_id as interiorId,interior_name as interiorName,family_id as familyId,family_name as familyName,car_num as carNum,guide_price as guidePrice,deposit_price as depositPrice,naked_price as nakedPrice,
                traffic_compulsory_insurance_price as trafficCompulsoryInsurancePrice,commercial_insurance_price as commercialInsurancePrice,change_price as changePrice,ticket_pic as ticketPic,remark,create_time as createTime,state';
                    $carField = 'oc.vin,oc.audit_remark as auditRemark,oc.audit_state as auditState,oc.brand_id as brandId,oc.brand_name as brandName,oc.cars_id as carsId,oc.cars_name as carsName,oc.certification_pic as certificationPic,
                    oc.check_car_pic as checkCarPic,oc.ci_pic as ciPic,oc.color_id as colorId,oc.color_name as colorName,oc.create_time as createTime,oc.express_pic as expressPic,oc.family_id as familyId,oc.family_name as familyName,
                    oc.id,oc.info_id as infoId,oc.interior_id as interiorId,oc.interior_name as interiorName,oc.is_del as isDel,oc.other_pic as otherPic,oc.stock_car_id as stockCarId,oc.tci_pic as tciPic,oc.ticket_pic as ticketPic,
                    oc.ticket_remark as ticketRemark,sc.frame_number as frameNumber,sc.engine_number';

                    foreach($value['customers'] as $key => &$val){
                        $val['infos'] = Db::name('consumer_order_info')->field($field)->where(['order_id' => $orderId, 'customer_id' => $val['id'], 'is_del' => 0])->select();
//                        echo Db::name('consumer_order_info')->getLastSql();die;
                        if($val['infos']){
                            foreach($val['infos'] as $k => &$v){
                                $v['cars'] = Db::name('consumer_order_car oc')->where(['oc.info_id' => $v['id']])->join('stock_car sc', 'sc.stock_car_id=oc.stock_car_id')->field($carField)->select();
//                                echo Db::name('consumer_order_car oc')->getLastSql();die;
                            }
                        }
                    }
                }
            }
        }

        return ['list' => $data, 'count' => $count];
    }


    public function getOrderListAllForNonePage($where = ''){
        $field = 'co.id as id,co.order_code as orderId,co.state as orderState,org_name as orgName,order_type as orderType,co.freight,creator,co.create_time as createTime';
        $data  = Db::name('consumer_order co')->where($where)
            ->field($field)->order('co.create_time desc')
            ->select();
        if($data){
            foreach($data as $key => &$value){
                $orderId = $value['id'];
                $value['orderStateName'] = $this->state[$value['orderState']];
                $value['totalDepositPrice'] = Db::name('consumer_order_info')->where(['order_id' => $orderId])->sum('deposit_price');
                $value['totalFinalPrice']   = 0;
                $value['totalRestPrice']    = 0;
                $orderInfo = Db::name('consumer_order_info')->where(['order_id' => $orderId, 'is_del' => 0])->field('naked_price,traffic_compulsory_insurance_price,commercial_insurance_price,car_num')->select();
                if($orderInfo){
                    $total = 0;
                    foreach($orderInfo as $vo){
                        $total += ($vo['naked_price'] + $vo['traffic_compulsory_insurance_price'] + $vo['commercial_insurance_price']) * $vo['car_num'];
                    }
                    $value['totalFinalPrice']   = $total + $value['freight'];
                    $value['totalRestPrice']    = $value['totalFinalPrice'] - $value['totalDepositPrice'];
                }

                $orderUserField      = 'id,order_id as orderId,user_name as userName,user_phone as userPhone,id_card as idCard,id_card_pic_on as idCardPicOn,id_card_pic_off as idCardPicOff,type,create_time as createTime,is_del as isDel';
                $value['customers']  = Db::name('consumer_order_user')->where(['order_id' => $orderId, 'type' => 1])->field($orderUserField)->select();
                if($value['customers']){
                    $field = 'id,cars_id as carsId,cars_name as carsName,order_id as orderId,customer_id as customerId,brand_id as brandId,brand_name as brandName,color_id as colorId,color_name as colorName,
                interior_id as interiorId,interior_name as interiorName,family_id as familyId,family_name as familyName,car_num as carNum,guide_price as guidePrice,deposit_price as depositPrice,naked_price as nakedPrice,
                traffic_compulsory_insurance_price as trafficCompulsoryInsurancePrice,commercial_insurance_price as commercialInsurancePrice,change_price as changePrice,ticket_pic as ticketPic,remark,create_time as createTime,state';
                    $carField = 'oc.vin,oc.audit_remark as auditRemark,oc.audit_state as auditState,oc.brand_id as brandId,oc.brand_name as brandName,oc.cars_id as carsId,oc.cars_name as carsName,oc.certification_pic as certificationPic,
                    oc.check_car_pic as checkCarPic,oc.ci_pic as ciPic,oc.color_id as colorId,oc.color_name as colorName,oc.create_time as createTime,oc.express_pic as expressPic,oc.family_id as familyId,oc.family_name as familyName,
                    oc.id,oc.info_id as infoId,oc.interior_id as interiorId,oc.interior_name as interiorName,oc.is_del as isDel,oc.other_pic as otherPic,oc.stock_car_id as stockCarId,oc.tci_pic as tciPic,oc.ticket_pic as ticketPic,
                    oc.ticket_remark as ticketRemark,sc.frame_number as frameNumber,sc.engine_number';

                    foreach($value['customers'] as $key => &$val){
                        $val['infos'] = Db::name('consumer_order_info')->field($field)->where(['order_id' => $orderId, 'customer_id' => $val['id'], 'is_del' => 0])->select();
                        if($val['infos']){
                            foreach($val['infos'] as $k => &$v){
                                $v['cars'] = Db::name('consumer_order_car oc')->where(['oc.info_id' => $v['id']])->join('stock_car sc', 'sc.stock_car_id=oc.stock_car_id')->field($carField)->select();
                            }
                        }
                    }
                }
            }
        }

        return $data;
    }
}
