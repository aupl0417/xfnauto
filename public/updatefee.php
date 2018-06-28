<?php
    $field = 'sa_id,sa_state,sa_amount,sa_totalAmount,sa_rate,sa_fee,sa_feeTotal,sa_period,sa_voucherTime';
    $where = ['sa_state' => ['in', [3, 4]], 'sa_isDel' => 0, 'sa_type' => 1];
    $where = ' WHERE sa_state in(3,4) and sa_isDel=0 and sa_type=1';
    $sql   = 'Select ' . $field . ' FROM shop_loan_apply ' . $where . ' ORDER BY sa_id desc';
    require '../extend/mysql.php';
    $config = [
        'type'            => 'mysql',
        // 服务器地址
        'hostname'        => 'gz-cdb-b1xeazxc.sql.tencentcdb.com',
//        'hostname'        => '127.0.0.1',
        // 数据库名
        'database'        => 'tautotest',
//        'database'        => 'xfnauto',
        // 用户名
        'username'        => 'root',
        // 密码
        'password'        => 'ivystudio2018',
//        'password'        => '',
        // 端口
        'hostport'        => '63840',
//        'hostport'        => '3306',
        // 连接dsn
        'dsn'             => '',
        // 数据库连接参数
        'params'          => [],
        // 数据库编码默认采用utf8
        'charset'         => 'utf8',
        // 数据库表前缀
        'prefix'          => '',
    ];

    $mysql = new mysql($config);
    $data  = $mysql->getAll($sql);

    if($data){
        $field = 'sai_id,sai_saId,sai_price,sai_downPayments,sai_amount,sai_number,sai_fee,sai_state';
        $infoData  = [];
        $applyData = [];
        foreach($data as $key => $value){
            $where = ' WHERE sai_isDel = 0 and sai_saId = ' . $value['sa_id'];
            $sql = 'SELECT ' . $field . ' FROM shop_loan_apply_info ' . $where ;
            $infoList = $mysql->getAll($sql);
            $applyData[$value['sa_id']] = ['sa_id' => $value['sa_id']];
            if($infoList){
                $applyData[$value['sa_id']]['sa_feeTotal'] = 0;
                $days = ceil((time() - $value['sa_voucherTime']) / 3600 / 24);
                if($value['sa_state'] == 3 && ($value['sa_period'] - $days <= 7)){
                    $applyData[$value['sa_id']]['sa_state'] = 4;
                }elseif($value['sa_state'] == 4 && ($value['sa_period'] - $days <= 0) ){
                    $applyData[$value['sa_id']]['sa_state'] = 5;
                }
                foreach($infoList as $k => $val){
                    if($val['sai_state'] == 0){//如果是未支付状态（0），则计算手续费，否则直接统计总手续费
                        $fee = ceil($val['sai_price'] * $val['sai_number'] * (100 - $val['sai_downPayments']) / 100 * $value['sa_rate']  * $days) / 100;//同定单下的车型的总手续费
                        $infoData[] = [
                            'sai_id'  => $val['sai_id'],
                            'sai_fee' => $fee
                        ];
                    }else{
                        $fee = $val['sai_fee'];
                    }
                    $applyData[$value['sa_id']]['sa_feeTotal'] += $fee;//正常情况下，同一定单的总手续费
                }
                $overdueField = 'sao_rate,sao_fee,sao_period,sao_updateTime';
                $where        = ' WHERE sao_orderId = ' . $value['sa_id'] . ' and sao_state = 1';
                $sql = 'SELECT ' . $overdueField . ' FROM shop_loan_apply_overdue ' . $where . ' order by sao_id desc';
                $overDue = $mysql->getRow($sql);
                //计算并加上每天的逾期手续费
                if($overDue){
                    $overDueDays = ceil((time() - $overDue['sao_updateTime']) / 24 / 3600);
                    $applyData[$value['sa_id']]['sa_feeTotal'] += $value['sa_amount'] * $overDue['sao_rate'] / 100 * $overDueDays;//加上逾期总手续费
                }
                $applyData[$value['sa_id']]['sa_feeTotal']    = ceil($applyData[$value['sa_id']]['sa_feeTotal'] * 100) / 100;
                $applyData[$value['sa_id']]['sa_totalAmount'] = $value['sa_amount'] + $applyData[$value['sa_id']]['sa_feeTotal'];
                $applyData[$value['sa_id']]['sa_totalAmount'] = ceil($applyData[$value['sa_id']]['sa_totalAmount'] * 100) / 100;
            }
        }
//        echo '<pre>';
//        print_r($applyData);
//        print_r($infoData);die;
        try{
            $mysql->beginTRAN();
//            $result = $mysql->saveAll('shop_loan_apply', $applyData);
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

//            $result = $mysql->saveAll('shop_loan_apply_info', $infoData);
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
    }



/**
* 运行日志
* @param $data       数据 type : mixed
* @param $controller 所在控制器
* @param $action     方法
* @param $params     参数 type : mixed
* */
function logs_write($data, $controller, $action, $params){
    $fp = @fopen('debug_' . date('Y-m-d') . ".txt", "a+");
    fwrite($fp, "运行：" . "----" . date('Y-m-d H:i:s') . "\n");
    fwrite($fp, "Data:" . (is_array($data) ? json_encode($data) : $data) . "\n");
    fwrite($fp, "Controller:" . $controller . " Action:" . $action . "\n");
    fwrite($fp, "Params:" . (is_array($params) ? json_encode($params) : $params) . "\n");
    fwrite($fp, "------------------------------------------------------------------------\n\n");
    fclose($fp);
}

/**
* 	返回数据到客户端
*	@param $code type : int		状态码
*   @param $info type : string  状态信息
*	@param $data type : mixed	要返回的数据
*	return json
*/
function apiReturn($code, $data = null, $msg = ''){
    header('Content-Type:application/json; charset=utf-8');//返回JSON数据格式到客户端 包含状态信息

    $jsonData = array(
        'code' => $code,
        'msg'  => $msg ?: ($code == 200 ? '操作成功' : '操作失败'),
        'data' => $data ? $data : null
    );

    exit(json_encode($jsonData));
}
