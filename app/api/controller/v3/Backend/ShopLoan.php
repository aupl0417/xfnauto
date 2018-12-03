<?php
/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2018-05-31
 * Time: 11:46
 */

namespace app\api\controller\v3\Backend;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Controller;
use think\Db;
use think\Exception;

class ShopLoan extends Admin
{

    /**
     * 垫资列表
     * @return json
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;

        $where = ['sa_isDel' => 0, 'sa_state' => ['neq', -1]];

        if(isset($this->data['state']) && $this->data['state'] != ''){
            $state = $this->data['state'] + 0;
            $where['sa_state'] = $state;
        }

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            $where['sa_orgName|sa_orderId'] = ['like', '%' . $keywords . '%'];
        }

        $data = model('ShopLoanApply')->getDataByPage($where, $page, $rows);
        if($data['list']){
             $state = [
                '0' => '待审核',
                '1' => '已拒绝',
                '2' => '待放款',
                '3' => '已放款',
                '4' => '待还款',
                '5' => '已逾期',
                '6' => '移交处理',
                '7' => '已还清'
            ];
            foreach($data['list'] as $key => &$value){
                $value['stateName'] = $state[$value['state']];
            }
        }
        $this->apiReturn(200, $data);
    }

    /**
     * 审核详情
     * */
    public function detail(){
        (!isset($this->data['id']) || empty($this->data['id'])) && $this->apiReturn(201, '', '参数非法');

        $id = $this->data['id'] + 0;
        $data = model('ShopLoanApply')->getShopLoanApplyByIdAll($id);
        if($data){
            $field = $this->getField('shop_info', '', false, '', true);
            $data['info'] = model('ShopInfo')->getShopInfoByUserId($data['userId'], $field);
        }
        $state = [
            '0' => '待审核',
            '1' => '已拒绝',
            '2' => '待放款',
            '3' => '已放款',
            '4' => '待还款',
            '5' => '已逾期',
            '6' => '移交处理',
            '7' => '已还清'
        ];
        $data['stateName'] = $state[$data['state']];

        $this->apiReturn(200, $data);
    }

    /**
     * 审核
     * */
    public function verify(){
        (!isset($this->data['id'])    || empty($this->data['id']))    && $this->apiReturn(201, '', '参数非法');
        (!isset($this->data['state']) || empty($this->data['state'])) && $this->apiReturn(201, '', '状态参数非法');

        $id     = $this->data['id'] + 0;
        $state  = $this->data['state'] + 0;
        !in_array($state, [1, 2], true) && $this->apiReturn(201, '', '状态参数非法');
        $reason = '';
        if($state == 1){
            (!isset($this->data['reason']) || empty($this->data['reason'])) && $this->apiReturn(201, '', '请输入拒绝原因');

            $reason = htmlspecialchars(trim($this->data['reason']));
        }

        $info = model('ShopLoanApply')->getById($id, 'sa_state as state');
        !$info && $this->apiReturn(201, '', '数据不存在');
        $info['state'] != 0 && $this->apiReturn(201, '', '订单已处理');

        $data = [
            'sa_state'  => $state,
            'sa_reason' => $reason,
            'sa_operatorId' => $this->userId,
            'sa_operatorName' => $this->user['realName'],
            'sa_updateTime'   => time()
        ];

        $result = Db::name('shop_loan_apply')->where(['sa_id' => $id])->update($data);
        $result === false && $this->apiReturn(201, '', '操作失败');
        $this->apiReturn(200, '', '操作成功');
    }

    public function getRate(){
        $rate = model('ShopLoan')->getRate();
        $this->apiReturn(200, ['rate' => $rate]);
    }

    /**
     * 设置手续费率
     * */
    public function setRate(){
        $cacheKey = md5('set_loan_rate');
        (!isset($this->data['rate']) || empty($this->data['rate'])) && $this->apiReturn(201, '', '费率不能为空');

        !is_numeric($this->data['rate']) && $this->apiReturn(201, '', '参数非法');

        $rate = floatval($this->data['rate']);
        $result = Db::name('dictionary')->where(['d_typeid' => 1, 'd_key' => 0])->update(['d_value' => $rate]);
        $result === false && $this->apiReturn(201, '', '设置手续费率失败');
        cache($cacheKey, $rate);
        $this->apiReturn(200, ['rate' => cache($cacheKey)]);
    }

    /**
     * 上传放款凭证
     * */
    public function loanVoucher(){
        (!isset($this->data['id'])    || empty($this->data['id']))    && $this->apiReturn(201, '', '参数非法');
        (!isset($this->data['voucher'])    || empty($this->data['voucher']))    && $this->apiReturn(201, '', '请上传放款凭证');

        $id      = $this->data['id'] + 0;
        $voucher = htmlspecialchars(trim($this->data['voucher']));
        !filter_var($voucher, FILTER_VALIDATE_URL) && $this->apiReturn(201, '', '放款凭证地址非法');

        $loanInfo  = model('ShopLoanApply')->getById($id, 'sa_state as state,sa_userName as userName,sa_phone as phone,sa_amount as amount,sa_orderId as orderId,sa_createTime as createTime');
        !$loanInfo && $this->apiReturn(201, '', '该垫资数据不存在');
        $loanInfo['state'] != 2 && $this->apiReturn(201, '', '非待放款状态不能上传');

        $data = [
            'sa_state'             => 3,
            'sa_voucher'           => $voucher,
            'sa_voucherPersonId'   => $this->userId,
            'sa_voucherPersonName' => $this->user['realName'],
            'sa_voucherTime'       => time()
        ];

        $result = Db::name('shop_loan_apply')->where(['sa_id' => $id])->update($data);
        $result === false && $this->apiReturn(201, '', '上传放款凭证失败');
        $sendData = [$loanInfo['userName'], date('Y', $loanInfo['createTime']), date('m', $loanInfo['createTime']), date('d', $loanInfo['createTime']), date('H', $loanInfo['createTime']), date('i', $loanInfo['createTime']), $loanInfo['orderId'], round($loanInfo['amount'], 2)];
        sendSms($loanInfo['phone'], 1, $sendData);
        $this->apiReturn(200, '', '上传放款凭证成功');
    }

    /**
     * 还款
     * */
    public function payVoucher(){
        (!isset($this->data['orderId'])  || empty($this->data['orderId']))  && $this->apiReturn(201, '', '参数非法');
        (!isset($this->data['voucher'])  || empty($this->data['voucher']))  && $this->apiReturn(201, '', '请上传还款凭证');
        (!isset($this->data['infoIds'])  || empty($this->data['infoIds']))  && $this->apiReturn(201, '', '请选择还款车辆');

        $orderId  = $this->data['orderId'] + 0;
        $voucher  = htmlspecialchars(trim($this->data['voucher']));
        $vouchers = explode(',', $voucher);
        foreach($vouchers as $value){
            !filter_var($value, FILTER_VALIDATE_URL) && $this->apiReturn(201, '', '还款凭证地址非法');
        }

        $applyInfo = model('ShopLoanApply')->getById($orderId, 'sa_id as id,sa_userName as userName,sa_phone as phone,sa_state as state,sa_orderId as orderId,sa_voucherTime as voucherTime,sa_period as period,sa_rate as rate,sa_totalAmount as totalAmount,sa_feeTotal as feeTotal');
        !$applyInfo && $this->apiReturn(201, '', '数据不存在');
        !in_array(intval($applyInfo['state']), [3, 4]) && $this->apiReturn(201, '', '未到还款状态');

        $infoIds       = htmlspecialchars(trim($this->data['infoIds']));
        $infoIds       = explode(',', $infoIds);
        $loanApplyInfo = Db::name('shop_loan_apply_info')->where(['sai_saId' => $orderId, 'sai_isDel' => 0])->field('sai_id,sai_amount as amount,sai_downPayments as downPayments,sai_fee as fee,sai_state as state')->select();
        !$loanApplyInfo && $this->apiReturn(201, '', '该垫资详情记录不存在或已还款');
        $unpayCount    = Db::name('shop_loan_apply_info')->where(['sai_saId' => $orderId, 'sai_state' => 0])->count();
        try{
            Db::startTrans();
            $result = Db::name('shop_loan_apply_info')->where(['sai_id' => ['in', $infoIds], 'sai_isDel' => 0, 'sai_state' => 0])->update(['sai_state' => 1]);
            if($result === false){
                throw new Exception('更新车型垫资状态失败');
            }

            $amount   = 0;
            $paidTotal = 0;
            $fee       = 0;
            if($loanApplyInfo){
                foreach($loanApplyInfo as $info){
                    if($info['state'] == 0 && in_array($info['sai_id'], $infoIds)){
                        $amount += $info['amount'] + $info['fee'];
                        $fee    += $info['fee'];
                    }elseif($info['state'] == 1){
                        $paidTotal += $info['amount'] + $info['fee'];
                        $fee       += $info['fee'];
                    }
                }
            }

            $records = [
                'spr_orderId' => $orderId,
                'spr_infoIds' => implode(',', $infoIds),
                'spr_period'  => ceil((time() - $applyInfo['voucherTime']) / 24 / 3600),
                'spr_amount'  => $amount,
                'spr_voucher' => $voucher,
                'spr_createTime' => time()
            ];

            $result = Db::name('shop_loan_pay_records')->insert($records);
            if(!$result){
                throw new Exception('插入还款记录表失败');
            }

            $applyInfoCount = model('ShopLoanApplyInfo')->getCountById($applyInfo['orderId']);
            $data = [
                'sa_state'  => (count($infoIds) == $applyInfoCount || ($unpayCount - count($infoIds) <= 0)) ? 7 : 4
            ];
            
            $result = Db::name('shop_loan_apply')->where(['sa_id' => $orderId])->update($data);
            if($result === false){
                throw new Exception('更新垫资表状态失败');
            }
            Db::commit();
            $fee = round($fee, 2);
            $unpayAmount = $applyInfo['totalAmount'] - $amount - $paidTotal;
            $unpayAmount = round($unpayAmount, 2);
            $unpayFee = round($applyInfo['feeTotal'] - $fee, 2);
            $sendData = [$applyInfo['userName'], date('Y', time()), date('m', time()), date('d', time()), date('H', time()), date('i', time()), round($amount, 2), round($fee, 2), $applyInfo['orderId'], round($unpayAmount, 2), round($unpayFee, 2)];
            $result = sendSms($applyInfo['phone'], 2, $sendData);

            $this->apiReturn(200, '', '操作成功');
        }catch (Exception $e){
            Db::rollback();
            $this->apiReturn(201, '', '操作失败');
        }
    }

    /**
     * 待还款列表
     * */
    public function unpayList(){
        (!isset($this->data['orderId'])  || empty($this->data['orderId']))  && $this->apiReturn(201, '', '参数非法');

        $orderId = $this->data['orderId'] + 0;

        $applyInfo = model('ShopLoanApply')->getById($orderId, 'sa_id as id,sa_state as state');
        !$applyInfo && $this->apiReturn(201, '', '数据不存在');
        $state = ['0' => '待审核', '1' => '拒绝',  '2 ' => '待放款',  '3' => '已放款',  '4' => '请还款',  '5' => '已逾期',  '6' => '移交处理', '7' => '已还清'];
        !in_array(intval($applyInfo['state']), [3, 4]) && $this->apiReturn(201, '', '该订单状态为' . $state[$applyInfo['state']]);

        $data    = model('ShopLoanApplyInfo')->getUnpaidDataBySaId($orderId);
        if($data){
            foreach($data as $key => &$value){
                $value['fee'] = round($value['fee'], 2);
            }
        }
        $this->apiReturn(200, $data);
    }

    /**
     * 延期操作
     * */
    public function overdue(){
        if(isset($this->data['id']) && !empty($this->data['id'])){
            $overDueId = $this->data['id'] + 0;
            $result = $this->validate($this->data, 'OverDue');
        }else{
            $result = $this->validate($this->data, 'OverDue.add');
        }

        $result !== true && $this->apiReturn(201, '', $result);

        $orderId = $this->data['orderId'] + 0;
        $applyInfo = model('ShopLoanApply')->getById($orderId, 'sa_id as id,sa_state as state,sa_amount as amount,sa_rate as rate,sa_period as period,sa_voucherTime as voucherTime');
        !$applyInfo && $this->apiReturn(201, '', '数据不存在');
        !in_array(intval($applyInfo['state']), [4, 5], true) && $this->apiReturn(201, '', '只有在请还款和已逾期状态才能延期');

//        $deadLine = date('Y-m-d', $applyInfo['voucherTime'] + 24 * 3600 * $applyInfo['period']);
//        $overdueDays = ceil((time() - strtotime($deadLine)) / 24 / 3600);//当前时间距还款期限的天数，如果这个天数比设置的延期天数大，则提示延期天数有误
//        if($overdueDays > floatval($this->data['period'])){
//            $this->apiReturn(201, '', '当前时间距还款期限已过' . $overdueDays . '天，设置的天数不能小于这个数');
//        }

        //当前未还垫资总额
        $unpayTotal = Db::name('shop_loan_apply_info')->where(['sai_saId' => $orderId, 'sai_isDel' => 0, 'sai_state' => 0])->sum('sai_amount');
        $voucher    = (isset($this->data['voucher']) && !empty($this->data['voucher'])) ? htmlspecialchars($this->data['voucher']) : '';
        $data = [
            'sao_orderId'        => $orderId,
            'sao_downpayment'    => floatval($this->data['downpayment']),
            'sao_downpaymentFee' => floatval($this->data['amount']),
            'sao_rate'           => (isset($this->data['rate']) && !empty($this->data['rate'])) ? floatval($this->data['rate']) : $applyInfo['rate'],
            'sao_period'         => floatval($this->data['period']),
            'sao_fee'            => ceil($applyInfo['amount'] * floatval($this->data['rate'])) / 100,
            'sao_unpayAmount'    => $unpayTotal,
            'sao_state'          => 0,
            'sao_operatorId'     => $this->userId,
            'sao_operatorName'   => $this->user['realName']
        ];

        try{

            Db::startTrans();
            if(isset($overDueId)){
                $data['sao_voucher']     = $voucher;
                $data['sao_updateTime']  = time();
                $data['sao_state']       = $voucher ? 1 : 0;
                $result = Db::name('shop_loan_apply_overdue')->where(['sao_id' => $overDueId, 'sao_orderId' => $orderId])->update($data);
                if($result === false){
                    throw new Exception('确认延期失败');
                }
            }else{
                if(Db::name('shop_loan_apply_overdue')->where(['sao_orderId' => $orderId, 'sao_state' => 0])->count()){
                    $this->apiReturn(201, '', '已添加过未付款延期，不能重复添加');
                }
                $data['sao_createTime'] = time();
                $data['sao_state']      = 0;
                if($voucher){
                    $deadLine  = strtotime(date('Y-m-d', $applyInfo['voucherTime'])) + $applyInfo['period'] * 24 * 3600;
                    $overDue   = Db::name('shop_loan_apply_overdue')->where(['sao_orderId' => $orderId, 'sao_state' => 1])->field('sao_beginTime as beginTime,sao_period as period')->order('sao_beginTime desc')->find();
                    $currentDate  = strtotime(date('Y-m-d', strtotime('+1 day')));
                    $beginDate = time() <= $deadLine ? $deadLine : $currentDate;//如果当前时间在正常的垫资期限内，则延期开始时间为截止日期，否则为当天的0时0分0秒
                    if($overDue){
                        $overDueDeadLine = strtotime(date('Y-m-d', $overDue['beginTime'] + $overDue['period'] * 24 * 3600));//延期截止日期时间戳
                        if(time() < $overDueDeadLine){//如果当前时间在延期截止日期内，则用该延期截止日期作为另一个延期的开始时间，否则是当天的0时0分0秒
                            $beginDate = $overDueDeadLine;
                        }else{
                            $beginDate = $currentDate;
                        }
                    }
                    $data['sao_voucher']    = $voucher;
                    $data['sao_state']      = 1;
                    $data['sao_updateTime'] = time();
                    $data['sao_beginTime']  = $beginDate;
                }
                $result = Db::name('shop_loan_apply_overdue')->insert($data);
                if(!$result){
                    throw new Exception('插入延期记录失败');
                }
            }
            if($data['sao_state'] == 1){
                $result = Db::name('shop_loan_apply')->where(['sa_id' => $orderId, 'sa_state' => ['in', [4, 5]], 'sa_isDel' => 0])->update(['sa_state' => 4]);
                if($result === false){
                    throw new Exception('更新总单状态失败');
                }
            }
            Db::commit();
            $this->apiReturn(200, '', '操作成功');
        }catch (Exception $e){
            Db::rollback();
            $this->apiReturn(201, '', '操作失败');
        }


    }

    /**
     * 延期详情
     * */
    public function overdueDetail(){
        (!isset($this->data['orderId'])  || empty($this->data['orderId']))  && $this->apiReturn(201, '', '参数非法');

        $orderId = $this->data['orderId'] + 0;
        $field   = 'sao_id as id,sao_orderId as orderId,sao_downpayment as downpayment,sao_downpaymentFee as downpaymentFee,sao_rate as rate,sao_period as period,sao_voucher as voucher,sao_state as state';
        $data    = Db::name('shop_loan_apply_overdue')->where(['sao_orderId' => $orderId])->field($field)->order('sao_id desc')->find();
        (!$data ||$data['state'] == 1) && $this->apiReturn(200);
        $this->apiReturn(200, $data);
    }

    public function payRecord(){
        (!isset($this->data['orderId'])  || empty($this->data['orderId']))  && $this->apiReturn(201, '', '参数非法');

        $orderId = $this->data['orderId'] + 0;
        $data    = model('ShopLoanApply')->getPayRecords($orderId);
        $this->apiReturn(200, $data ?: []);
    }

    public function export(){
        $where = ['sa_isDel' => 0, 'sa_state' => ['neq', -1]];

        if(isset($this->data['state']) && $this->data['state'] != ''){
            $state = $this->data['state'] + 0;
            $where['sa_state'] = $state;
        }

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            $where['sa_orgName|sa_orderId'] = ['like', '%' . $keywords . '%'];
        }

        $data = model('ShopLoanApply')->getDataForNonePage($where);
        !$data && $this->apiReturn(201, '', '暂无数据');

        $state = [
            '0' => '待审核',
            '1' => '已拒绝',
            '2' => '待放款',
            '3' => '已放款',
            '4' => '待还款',
            '5' => '已逾期',
            '6' => '移交处理',
            '7' => '已还清'
        ];
        foreach($data as $key => &$value){
            $value['stateName'] = $state[$value['state']];
        }

        $name = '垫资列表导出';
        $objPHPExcel = new \PHPExcel();
        $allLetter   = range('A', 'Z');

        $objPHPExcel->getProperties()->setCreator($this->user['realName']);

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $name);
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:H1');

        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(45);
        $objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(30);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(24);

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', '垫资单号')
            ->setCellValue('B2', '门店名称')
            ->setCellValue('C2', '申请时间')
            ->setCellValue('D2', '垫资本金总额（元）')
            ->setCellValue('E2', '生成手续费总额（元）')
            ->setCellValue('F2', '待还总额（元）')
            ->setCellValue('G2', '垫资期限（天）')
            ->setCellValue('H2', '状态');

        if($data){
            foreach($data as $k => $item){
                $num = $k + 3;
                $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $num, $item['orderId'])
                    ->setCellValue('B' . $num, $item['orgName'])
                    ->setCellValue('C' . $num, $item['createTime'])
                    ->setCellValue('D' . $num, $item['amount'])
                    ->setCellValue('E' . $num, $item['feeTotal'])
                    ->setCellValue('F' . $num, $item['unpayAmount'])
                    ->setCellValue('G' . $num, $item['period'])
                    ->setCellValue('H' . $num, $item['stateName']);
                $objPHPExcel->getActiveSheet()->getRowDimension($num)->setRowHeight(30);
            }
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        for ($i = 2; $i <= 8; $i++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($allLetter[$i])->setWidth(20);
        }

        $endCell = 'H' . (count($data) + 2);
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
        $objPHPExcel->getActiveSheet()->getStyle('A2:H2')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setWrapText(true);

        $objPHPExcel->getActiveSheet()->setTitle($name);
        $objPHPExcel->setActiveSheetIndex(0);

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($name . '.xlsx');
        if(file_exists($name . '.xlsx')){
            vendor('Qiniu.autoload');
            $auth  = new Auth(config('qiniu.accesskey'), config('qiniu.secretkey'));
            $token = $auth->uploadToken(config('qiniu.bucket'));

            $upload = new UploadManager();
            list($ret, $err) = $upload->putFile($token, md5($name . microtime(true)) . '.xlsx', $name . '.xlsx');
            if ($err !== null) {
                $this->apiReturn(201, ['state' => 'error', 'msg' => $err]);
            } else {
                //返回图片的完整URL
                unlink($name . '.xlsx');
                $this->apiReturn(200, ['state' => 'success', 'url' => 'https://' . config('qiniu.domain') . '/' . $ret['key']]);
            }
        }else{
            $this->apiReturn(201, '', '文件不存在');
        }
    }

    public function transferDeal(){
        (!isset($this->data['orderId'])  || empty($this->data['orderId']))  && $this->apiReturn(201, '', '参数非法');
        $orderId = $this->data['orderId'] + 0;

        $info = model('ShopLoanApply')->getById($orderId, 'sa_state as state');
        !$info && $this->apiReturn(201, '', '数据不存在');
        $info['state'] == 6 && $this->apiReturn(201, '', '该订单已移交处理');
        !in_array($info['state'], [3, 4, 5]) && $this->apiReturn(201, '', '未还清或已逾期才可移交处理');
        try{
            Db::startTrans();
            $data = [
                'sa_state'        => 6,
                'sa_operatorId'   => $this->userId,
                'sa_operatorName' => $this->user['realName'],
                'sa_updateTime'   => time()
            ];
            $result = Db::name('shop_loan_apply')->where(['sa_id' => $orderId, 'sa_state' => ['in', [3, 4, 5]], 'sa_isDel' => 0])->update($data);
            if($result === false){
                throw new Exception('更新垫资单表失败');
            }

            $result = Db::name('shop_loan_apply_info')->where(['sai_saId' => $orderId, 'sai_state' => 0, 'sai_isDel' => 0])->update(['sai_state' => 2]);
            if($result === false){
                throw new Exception('更新未还款车状失败');
            }
            Db::commit();
            $this->apiReturn(200, '', '移交处理成功');
        }catch (Exception $e){
            Db::rollback();
            $this->apiReturn(201, '', '移交处理失败');
        }

    }

    public function overdueRecords(){
        (!isset($this->data['orderId'])  || empty($this->data['orderId']))  && $this->apiReturn(201, '', '参数非法');
        $orderId = $this->data['orderId'] + 0;

        $field = getField('shop_loan_apply_overdue', 'sao_isDelete,sao_operatorId,sao_operatorName', false, '', true);
        $data  = Db::name('shop_loan_apply_overdue')->where(['sao_orderId' => $orderId, 'sao_isDelete' => 0])->field($field)->select();
        if($data){
            foreach($data as $key => &$value){
                $value['createTime'] = $value['createTime'] ? date('Y-m-d H:i:s', $value['createTime']) : '';
                $value['updateTime'] = $value['updateTime'] ? date('Y-m-d H:i:s', $value['updateTime']) : '';
            }
        }
        $this->apiReturn(200, $data);
    }

    public function sendSms(){
        sendSms('18675343869', 259008, []);
    }




}