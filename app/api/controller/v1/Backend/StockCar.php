<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1\Backend;

use think\Controller;
use think\Db;
class StockCar extends Admin
{

    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 50;

        $where = [
            'is_delete' => 0,
        ];

        if(isset($this->data['frame_number']) && !empty($this->data['frame_number'])){
            $where['frame_number'] = ['like', '%' . $this->data['frame_number'] . '%'];
        }

        if(isset($this->data['cars_info']) && !empty($this->data['cars_info'])){
            $where['cars_info'] = ['like', '%' . $this->data['cars_info'] . '%'];
        }

        if(isset($this->data['orgName']) && !empty($this->data['orgName'])){
            $condition['shortName'] = ['like', '%' . $this->data['orgName'] . '%'];
            $orgIds = Db::name('system_organization')->where($condition)->field('orgId')->select();
            !$orgIds && $this->apiReturn(200, ['list' => array(), 'page' => 1, 'rows' => $rows, 'total' => 0]);
            $orgIds = array_column($orgIds, 'orgId');
            $where['org_id'] = ['in', $orgIds];
        }
        //入库时间查询，暂时先用一个开始时间
        $startTime = isset($this->data['startTime']) && !empty($this->data['startTime']) ? $this->data['startTime'] : '';
        $endTime   = isset($this->data['endTime'])   && !empty($this->data['endTime'])   ? $this->data['endTime'] : '';
        if($startTime && !$endTime){
            $where['sc.create_date'] = ['egt', $this->data['startTime']];
        }elseif(!$startTime && $endTime){
            $where['sc.create_date'] = ['elt', $this->data['endTime']];
        }else{
            if($startTime && $endTime){
                $now = date('Y-m-d H:i:s');
                if($startTime == $endTime && $endTime <= $now){
                    $where['sc.create_date'] = ['egt', $startTime];
                }elseif($startTime == $endTime && $endTime >= $now){
                    $where['sc.create_date'] = ['elt', $startTime];
                }else{
                    if($startTime > $endTime){
                        $where['sc.create_date'] = ['between', [$endTime, $startTime]];
                    }else{
                        $where['sc.create_date'] = ['between', [$startTime, $endTime]];
                    }
                }
            }
        }

        $state = isset($this->data['state']) && !is_null($this->data['state']) ? $this->data['state'] + 0 : null;
        if($state == 1){
            $where['over_sure'] = 1;//已入库
        }elseif($state == 2){
            $where['lock_state'] = 1;//已锁定
        }elseif($state == 3){
            $where['is_put_out'] = 1;//已出库
        }elseif($state === 0){
            $where['over_sure'] = 0;//新建
        }

        $field = 'stock_car_id as id,cars_info as carsInfo,frame_number as frameNumber,interior_name as  interiorName,colour_name as colourName,so.shortName as orgName,warehouse_name as warehouseName,lock_state,is_put_out,over_sure,guiding_price as guidingPrice,unit_price as unitPrice,freight,othersFee,sc.create_date as createDate';
        $join  = [
            ['system_organization so', 'so.orgId=sc.org_id', 'left']
        ];
        $count = Db::name('stock_car sc')->where($where)->count();
        $data  = Db::name('stock_car sc')->where($where)->field($field)->join($join)->page($page, $rows)->order('sc.create_date desc')->select();
        if($data){
            foreach($data as $key => &$value){
                $value['state'] = $value['lock_state'] == 1 ? '已锁定' : ($value['is_put_out'] == 1 ? '已出库' : ($value['over_sure'] == 1 ? '已入库' : '新建'));
            }
        }
        $this->apiReturn(200, ['list' => $data, 'page' => $page, 'rows' => $rows, 'total' => $count]);
    }

    public function edit(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');

        $id = $this->data['id'] + 0;

        $data['unit_price'] = input('unitPrice', '', 'htmlspecialchars,trim');
        $data['freight']    = input('freight', '', 'htmlspecialchars,trim');
        $data['othersFee']  = input('othersFee', '', 'htmlspecialchars,trim');

        $result = $this->validate($data, 'EditStock');
        $result !== true && $this->apiReturn(201, '', $result);

        $result = Db::name('stock_car')->where(['stock_car_id' => $id])->update($data);
        $result === false && $this->apiReturn(201, '', '保存失败');
        $this->apiReturn(200, '', '保存成功');
    }

    public function detail(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');

        $id = $this->data['id'] + 0;
        $field = 'stock_car_id as id,cars_info as carsName,engine_number as enginNumber,frame_number as frameNumber,colour_name as colorName,interior_name as interiorName,sc.create_date as createTime,shortName as orgName,warehouse_name as warehouseName,lock_state,is_put_out,
        guiding_price as guidingPrice,unit_price as unitPrice,freight,othersFee,factory_out as factoryOut,mileage,over_strong_insurance as overStrongInsurance,follow_information as followInformation,stock_car_images as stockCarImages';
        $data = Db::name('stock_car sc')->where(['stock_car_id' => $id])->field($field)->join('system_organization so', 'so.orgId=sc.org_id', 'left')->find();
        $this->apiReturn(200, $data);
    }

    public function export(){
        $where = [
            'is_delete' => 0,
            'org_id'    => $this->orgId
        ];

        if(isset($this->data['frame_number']) && !empty($this->data['frame_number'])){
            $where['frame_number'] = ['like', '%' . $this->data['frame_number'] . '%'];
        }

        if(isset($this->data['cars_info']) && !empty($this->data['cars_info'])){
            $where['cars_info'] = ['like', '%' . $this->data['cars_info'] . '%'];
        }

        if(isset($this->data['orgName']) && !empty($this->data['orgName'])){
            $condition['shortName'] = ['like', '%' . $this->data['orgName'] . '%'];
            $orgIds = Db::name('system_organization')->where($condition)->field('orgId')->select();
            !$orgIds && $this->apiReturn(201, '暂无数据');
            $orgIds = array_column($orgIds, 'orgId');
            $where['org_id'] = ['in', $orgIds];
        }
		
        $startTime = isset($this->data['startTime']) && !empty($this->data['startTime']) ? $this->data['startTime'] : '';
        $endTime   = isset($this->data['endTime'])   && !empty($this->data['endTime'])   ? $this->data['endTime'] : '';
        if($startTime && !$endTime){
            $where['sc.create_date'] = ['egt', $this->data['startTime']];
        }elseif(!$startTime && $endTime){
            $where['sc.create_date'] = ['elt', $this->data['endTime']];
        }else{
            $now = date('Y-m-d H:i:s');
            if($startTime == $endTime && $endTime <= $now){
                $where['sc.create_date'] = ['egt', $startTime];
            }elseif($startTime == $endTime && $endTime >= $now){
                $where['sc.create_date'] = ['elt', $startTime];
            }else{
                if($startTime > $endTime){
                    $where['sc.create_date'] = ['between', [$endTime, $startTime]];
                }else{
                    $where['sc.create_date'] = ['between', [$startTime, $endTime]];
                }
            }
        }

        $state = isset($this->data['state']) && !is_null($this->data['state']) ? $this->data['state'] + 0 : null;
        if($state == 1){
            $where['over_sure'] = 1;//已入库
        }elseif($state == 2){
            $where['lock_state'] = 1;//已锁定
        }elseif($state == 3){
            $where['is_put_out'] = 1;//已出库
        }elseif($state == 0){
            $where['over_sure'] = 0;//新建
        }
		
        $field = 'stock_car_id as id,cars_info as carsInfo,frame_number as frameNumber,interior_name as  interiorName,colour_name as colourName,so.shortName as orgName,warehouse_name as warehouseName,lock_state,is_put_out,guiding_price as guidingPrice,unit_price as unitPrice,freight,othersFee,sc.create_date as createDate';
        $join  = [
            ['system_organization so', 'so.orgId=sc.org_id', 'left']
        ];

        $data  = Db::name('stock_car sc')->where($where)->field($field)->join($join)->order('sc.create_date desc')->select();




        $name = '库存列表导出';
        $objPHPExcel = new \PHPExcel();
        $allLetter = range('A', 'Z');

        $objPHPExcel->getProperties()->setCreator($this->user['realName']);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1',$name);
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:K1');

        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(45);
        $objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(30);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(24);

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', '车辆型号')
            ->setCellValue('B2', '车架号')
            ->setCellValue('C2', '车身/内饰颜色')
            ->setCellValue('D2', '所属门店')
            ->setCellValue('E2', '库位')
            ->setCellValue('F2', '库存状态')
            ->setCellValue('G2', '指导价')
            ->setCellValue('H2', '采购价')
            ->setCellValue('I2', '运费/辆')
            ->setCellValue('J2', '其他费用')
            ->setCellValue('K2', '入库时间');

        if($data){
            foreach($data as $k => $item){
//                $item['state'] = $item['lock_state'] == 1 ? '已锁定' : ($item['is_put_out'] == 1 ? '已出库' : '在库');
                $num = $k + 3;
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $num, $item['carsInfo'])
                    ->setCellValue('B' . $num, $item['frameNumber'])
                    ->setCellValue('C' . $num, $item['colourName'] . '/' . $item['interiorName'])
                    ->setCellValue('D' . $num, $item['orgName'])
                    ->setCellValue('E' . $num, $item['warehouseName'])
                    ->setCellValue('F' . $num, $item['lock_state'] == 1 ? '已锁定' : ($item['is_put_out'] == 1 ? '已出库' : '在库'))
                    ->setCellValue('G' . $num, $item['guidingPrice'])
                    ->setCellValue('H' . $num, $item['unitPrice'])
                    ->setCellValue('I' . $num, $item['freight'])
                    ->setCellValue('J' . $num, $item['othersFee'])
                    ->setCellValue('K' . $num, $item['createDate']);
            }
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        for ($i = 1; $i <= 10; $i++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($allLetter[$i])->setWidth(20);
        }

        $endCell = 'K' . (count($data) + 2);
        $styleArray = array(
            'borders' => array(
                'allborders' => array(
                    'style'  => \PHPExcel_Style_Border::BORDER_THIN,
                    'color'  => array('argb' => 'FF000000'),
                ),
            ),  'alignment'  => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PHPExcel_Style_Alignment::VERTICAL_CENTER
            ),
        );

        $objPHPExcel->setActiveSheetIndex(0)->getStyle('A2:' . $endCell)->applyFromArray($styleArray);
        $objPHPExcel->getActiveSheet()->getStyle('A'.(count($data) + 4))
            ->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->getStyle('A'.(count($data) + 7))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('A2:K2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $objPHPExcel->getActiveSheet()->setTitle($name);
        $objPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $name . '.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

}