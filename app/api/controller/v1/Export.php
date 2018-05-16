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
class Export extends Home
{

    /**
     * 首页统计
     * @return json
     * */
    public function index(){
//数据存在则执行导出
        $name = date("Y-m") . '月提现报表';
        $objPHPExcel = new \PHPExcel();
        $allLetter = range('A', 'Z');

        $objPHPExcel->getProperties()->setCreator('生机密码');

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1',$name);
        $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:K1');

        $objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(45);
        $objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight(30);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(24);

        $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A2', '提现编号')
            ->setCellValue('B2', '用户名')
            ->setCellValue('C2', '开户行')
            ->setCellValue('D2', '提现账号')
            ->setCellValue('E2', '账号姓名')
            ->setCellValue('F2', '提现金额')
            ->setCellValue('G2', '手续费')
            ->setCellValue('H2', '实际金额')
            ->setCellValue('I2', '状态')
            ->setCellValue('J2', '提现时间')
            ->setCellValue('K2', '预计到账时间');

        $status = array(-1 => '驳回', '未处理', '结算', '在途');
        foreach ($result['list'] as $k => $item) {
            $num = $k + 3;
            $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A' . $num, $item['co_caid'])
                ->setCellValue('B' . $num, $item['co_unick'])
                ->setCellValue('C' . $num, $item['co_bankName'])
                ->setCellValue('D' . $num, $item['co_account'])
                ->setCellValue('E' . $num, $item['co_cardmaster'])
                ->setCellValue('F' . $num, $item['co_money'])
                ->setCellValue('G' . $num, $item['co_tax'])
                ->setCellValue('H' . $num, $item['co_money'] - $item['co_tax'])
                ->setCellValue('I' . $num, $status[$item['co_state']])
                ->setCellValue('J' . $num, $item['co_arriveDateTime'])
                ->setCellValue('K' . $num, $item['co_day_time']);
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        for ($i = 1; $i <= 10; $i++) {
            $objPHPExcel->getActiveSheet()->getColumnDimension($allLetter[$i])->setWidth(20);
        }
//        $objPHPExcel->getActiveSheet()->getRowDimension(3)->setRowHeight(30);
        $endCell = 'K' . (count($result['list']) + 2);
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
        $objPHPExcel->getActiveSheet()->getStyle('A'.(count($result['list']) + 4))
            ->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        $objPHPExcel->getActiveSheet()->getStyle('A'.(count($result['list']) + 7))->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

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