<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1\Shop;

use app\api\model\CustomerOrder;
use think\Controller;
use think\Db;
class Loan extends Base
{

    public function index(){
        $result = sendMail('770517692@qq.com', '这是测试内容', 'jiangjun', '测试');
        dump($result);
    }

}