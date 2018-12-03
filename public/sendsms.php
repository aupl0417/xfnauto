<?php
/**
 * Created by PhpStorm.
 * User: aupl
 * Date: 2018/7/10
 * Time: 9:23
 */

    require '../extend/TaskBase.php';
    $orderId = isset($_GET['id']) && !empty($_GET['id']) ? $_GET['id'] + 0 : 0;
    $map   = $orderId ? ' and sa_id=' . $orderId : '';
    $field = 'sa_id,sa_orderId,sa_state,sa_phone,sa_userName,sa_amount,sa_totalAmount,sa_rate,sa_fee,sa_feeTotal,sa_period,sa_voucherTime';
    $where = ' WHERE sa_state=4 and sa_isDel=0 and sa_type=1' . $map;
    $sql   = 'Select ' . $field . ' FROM shop_loan_apply ' . $where . ' ORDER BY sa_id desc';

    $data  = $mysql->getAll($sql);
    !$data && apiReturn(200, '', '数据不存在');

    $field = 'sai_id,sai_amount as amount,sai_downPayments as downPayments,sai_fee as fee,sai_state as state';
    foreach($data as $key => $value){
        $where = ' WHERE sai_isDel = 0 and sai_saId = ' . $value['sa_id'] . ' and sai_state=1';
        $sql   = 'SELECT ' . $field . ' FROM shop_loan_apply_info ' . $where ;
        $loanApplyInfo = $mysql->getAll($sql);//shop_loan_apply_info表中的记录
        $paidTotal = 0;
        $paidFee   = 0;
        if($loanApplyInfo){
            $paidTotal = array_sum(array_column($loanApplyInfo, 'amount'));
            $paidFee   = array_sum(array_column($loanApplyInfo, 'fee'));
        }
        $voucherDate = date('Y-m-d', $value['sa_voucherTime']);//放款日期
        $dealLine    = strtotime($voucherDate) + $value['sa_period'] * 24 * 3600;

        //计算延期手续费
        $overdueField = 'sao_period,sao_beginTime';
        $where        = ' WHERE sao_orderId = ' . $value['sa_id'] . ' and sao_state = 1';
        $sql = 'SELECT ' . $overdueField . ' FROM shop_loan_apply_overdue ' . $where . ' order by sao_beginTime desc';
        $overDue = $mysql->getRow($sql);
        if($overDue){
            $dealLine = $overDue['sao_beginTime'] + $overDue['sao_period'] * 24 * 3600;
        }

        if(ceil(($dealLine - time()) / 24 / 3600) == 7){
            $unpayAmount = $value['sa_totalAmount'] - $paidTotal - $paidFee;
            $unpayFee    = round($value['sa_feeTotal'] - $paidFee, 2);
            $sendData    = [$value['sa_userName'], date('Y', time()), date('m', time()), date('d', time()), date('H', time()), date('i', time()), round($paidTotal + $paidFee, 2), round($paidFee, 2), $value['sa_orderId'], round($unpayAmount, 2), round($unpayFee, 2)];
            $result      = sendSms($value['sa_phone'], 2, $sendData);
            logs_write('发送短信' . ($result ? '成功' : '失败'), 'sms', 'sms', $sendData);
            !$result && apiReturn(201, '', '发送失败');
            apiReturn(200, '', '发送成功');
        }
    }
    logs_write('发送短信完成', 'sms', 'sms', []);
    apiReturn(200, '', '操作成功');
