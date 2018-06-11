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
class Index extends Base
{
    public function index(){

    }

    public function verify(){
        if($this->user['user_type'] == 1){
            $this->apiReturn(201, '', '您不是商家');
        }

        if(Db::name('shop_info')->where(['si_shopName' => $this->data['shopName'], 'si_userId' => $this->userId, 'si_state' => ['neq', 2]])->count()){
            $this->apiReturn(201, '', '您已提交申请');
        }

        $result = $this->validate($this->data, 'Shop');
        $result !== true && $this->apiReturn(201, '', $result);

        if($this->data['shopName'] !== $this->user['org_name']){
            $this->apiReturn(201, '', '您输入的店铺名称有误');
        }

        $data = [];
        foreach($this->data as $key => $value){
            $data['si_' . $key] = $value;
        }

        $data['si_createTime'] = time();
        $data['si_shopId']     = $this->user['org_id'];
        $data['si_userId']     = $this->userId;
        $result = Db::name('shop_info')->insert($data);
        !$result && $this->apiReturn(201, '', '提交失败');

        $this->apiReturn(200, '', '提交成功');
    }

}