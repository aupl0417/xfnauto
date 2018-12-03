<?php
    require '../extend/TaskBase.php';
    $orderId = isset($_GET['id']) && !empty($_GET['id']) ? $_GET['id'] + 0 : 0;
    $map = $orderId ? ' and sa_id=' . $orderId : '';
    $field = 'sa_id,sa_state,sa_amount,sa_totalAmount,sa_rate,sa_fee,sa_feeTotal,sa_period,sa_voucherTime';
    $where = ' WHERE sa_state in(3,4,5) and sa_isDel=0 and sa_type=1' . $map;
    $sql   = 'Select ' . $field . ' FROM shop_loan_apply ' . $where . ' ORDER BY sa_id desc';
    $data  = $mysql->getAll($sql);
    !$data && apiReturn(200, '', '数据不存在');
//dump($data);die;
//    echo '<pre>';
//    print_r($data);die;
    $field = 'sai_id,sai_saId,sai_price,sai_downPayments,sai_amount,sai_number,sai_fee,sai_state';
    $infoData  = [];
    $applyData = [];
    $time      = isset($_GET['time']) && !empty($_GET['time']) ? strtotime($_GET['time']) : time();
    $time < time() && apiReturn(201, '', '时间不能小于当前时间');
//        echo $time;die;
//        $time      = 1532756800;
    foreach($data as $key => $value){
        $where = ' WHERE sai_isDel = 0 and sai_saId = ' . $value['sa_id'];
        $sql = 'SELECT ' . $field . ' FROM shop_loan_apply_info ' . $where ;
        $infoList = $mysql->getAll($sql);//shop_loan_apply_info表中的记录
//            print_r($infoList);die;
        $applyData[$value['sa_id']] = ['sa_id' => $value['sa_id']];
        if($infoList){
            $applyData[$value['sa_id']]['sa_feeTotal'] = 0;
            $voucherDate = date('Y-m-d', $value['sa_voucherTime']);//放款日期

            //计算延期手续费
            $overdueField = 'sao_rate,sao_fee,sao_period,sao_updateTime,sao_beginTime,sao_unpayAmount';
            $where        = ' WHERE sao_orderId = ' . $value['sa_id'] . ' and sao_state = 1';
            $sql = 'SELECT ' . $overdueField . ' FROM shop_loan_apply_overdue ' . $where . ' order by sao_beginTime asc';
            $overDue = $mysql->getAll($sql);
            //计算并加上每天的逾期手续费
            $totalOverDueDays = 0;//总延期天数
            $hasOverDue   = 0;//是否有延期 0：没有延期
            //在还还清之前，会每天按正常垫资费率计算手续费，并累加，直到垫资期限时间用完
            foreach($infoList as $k => $val){
                $fee = $val['sai_fee'];
                $days = ceil(($time - strtotime($voucherDate)) / 3600 / 24);
                $loanDeadLine = strtotime($voucherDate) + $value['sa_period'] * 24 * 3600;//垫资截止日期
                $nextTime     = $loanDeadLine;
                $nextRate     = $value['sa_rate'];//下次循环的费率
                if($val['sai_state'] == 0){//如果是未支付状态（0），则计算手续费，否则直接统计总手续费
                    $overDueFee = 0;
                    if($overDue){
                        $days     = $days <= $value['sa_period'] ? $days : $value['sa_period'];
                        $loanDays = (strtotime(date('Y-m-d', time())) - strtotime(date('Y-m-d', $value['sa_voucherTime']))) / 24 / 3600 + 1;//已垫资天数（不包含延期）
                        $totalOverDueDays = array_sum(array_column($overDue, 'sao_period'));
                        $overDueDays  = 0;
                        $nextFee      = 0;
                        $hasOverDue   = 1;
                        $overDueSave  = [];
                        $nextFeeTotal = 0;
                        $totalOverDueTime = 0;

                        $overDueFee = 0;
                        foreach($overDue as $overKey => $due){
                            $beginTime    = $due['sao_beginTime'];//延期开始时间
                            $deadLine     = $beginTime + $due['sao_period'] * 24 * 3600;//延期截止时间
                            $overDueTime  = ($beginTime - $nextTime) / 24 / 3600;//延期前的天数间隔
                            $totalOverDueTime += $overDueTime;//总的延期间隔天数
                            $nextFee      = $val['sai_price'] * $val['sai_number'] * (100 - $val['sai_downPayments']) / 100 * ($nextRate) / 100 * $overDueTime;//由于非延期间隔期间，还会按正常垫资计费，这里去重
                            $nextFeeTotal += $nextFee;
                            $rate         = $due['sao_rate'];//当前
                            if($time < $beginTime){
                                $overDueDays = 0;
                            }else if($time > $beginTime && $time < $deadLine){
                                $overDueDays = ceil(($time - $beginTime) / 24 / 3600);
                            }else{
                                $overDueDays = $due['sao_period'];
                            }
                            $nextRate     = $due['sao_rate'];
                            $nextTime     = $deadLine;
                            $overDueFee += $val['sai_price'] * $val['sai_number'] * (100 - $val['sai_downPayments']) / 100 * $rate / 100 * $overDueDays;//加上逾期总手续费
                        }

                        if($time > $nextTime){//过了所有的延期或无延期，之后的每天会按照最后一个延期的费率计算每天的手续费，并累加
                            $nextFeeTotal += $val['sai_price'] * $val['sai_number'] * (100 - $val['sai_downPayments']) / 100 * $nextRate / 100 * ceil(($time - $nextTime) / 24 / 3600);
                            $days = $days <= $value['sa_period'] ? $days : $value['sa_period'];
                        }
                    }

                    $loanFee = $val['sai_price'] * $val['sai_number'] * (100 - $val['sai_downPayments']) / 100 * $value['sa_rate']  * $days / 100;//单辆车正常垫资总额
                    $fee = $loanFee + $overDueFee + $nextFeeTotal;//同定单下的车型的总手续费 = 正常垫资总额 + 逾期手续费总额 + 延期手续费总额
                    $fee = sprintf('%.3f', $fee);
                    $infoData[] = [
                        'sai_id'  => $val['sai_id'],
                        'sai_fee' => $fee
                    ];
                }
                $applyData[$value['sa_id']]['sa_feeTotal'] += $fee;//正常情况下，同一定单的总手续费
                $nextFeeTotal = 0;
            }

            $applyData[$value['sa_id']]['sa_feeTotal']    = round($applyData[$value['sa_id']]['sa_feeTotal'], 2);
            $applyData[$value['sa_id']]['sa_totalAmount'] = $value['sa_amount'] + $applyData[$value['sa_id']]['sa_feeTotal'];
            $applyData[$value['sa_id']]['sa_totalAmount'] = ceil($applyData[$value['sa_id']]['sa_totalAmount'] * 100) / 100;

            $seconds = strtotime($voucherDate) + ($value['sa_period'] + $totalOverDueDays + $totalOverDueTime) * 24 * 3600 - $time;
            if($value['sa_state'] == 3 && $seconds < 7 * 24 * 3600){//已放款状态，距还款日期还差7天时，更新状态为请还款状态（4）
                $applyData[$value['sa_id']]['sa_state'] = 4;
            }elseif($value['sa_state'] == 4 && $seconds < 0 ){//请还款状态，如果当前时间大于放款时间 + 垫资期限+ 延期时间 ，则状态改为已逾期（5）状态
                $applyData[$value['sa_id']]['sa_state'] = 5;
            }
        }
    }
//    dump($applyData);
//    dump($infoData);die;
    try{
        $mysql->beginTRAN();
        foreach($applyData as $key => &$value){
            $id = $value['sa_id'];
            $where = 'sa_id=' . $id;
            unset($value['sa_id']);
            $result = $mysql->update('shop_loan_apply', $value, $where);
            if($result === false){
                throw new Exception('更新shop_loan_apply表id为' . $id . '总费用及总手续费失败');
            }
        }
        unset($key, $value);
        foreach($infoData as $key => &$value){
            $id = $value['sai_id'];
            $where = 'sai_id=' . $id;
            unset($value['sai_id']);
            $result = $mysql->update('shop_loan_apply_info', $value, $where);
            if($result === false){
                throw new Exception('更新垫资申请明细表id为' . $id . '总手续费失败');
            }
        }
        if($result === false){
            throw new Exception('更新垫资申请明细表总手续费用失败');
        }
        logs_write('更新完毕', 'test', 'test', []);
        $mysql->commitTRAN();
        apiReturn(200, '', '更新完毕');
    }catch (Exception $e){
        logs_write('更新失败' . $e->getMessage(), 'test', 'test', []);
        $mysql->rollBackTRAN();
        apiReturn(201, '', '更新失败' . $e->getMessage());
    }