<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v3\Shop;

use app\api\model\CustomerOrder;
use Symfony\Component\Config\Definition\Exception\Exception;
use think\Controller;
use think\Db;
class Index extends Base
{
    public function index(){

    }

    /**
     * 店铺认证
     * */
    public function verify(){
        if(Db::name('shop_info')->where(['si_userId' => $this->userId, 'si_state' => ['neq', 2]])->count()){
            $this->apiReturn(201, '', '您已提交申请，请勿重复提交');
        }

        if(Db::name('shop_info')->where(['si_shopName' => $this->data['shopName'], 'si_state' => ['neq', 2]])->count()){
            $this->apiReturn(201, '', '店铺名称已存在');
        }

        if(Db::name('shop_info')->where(['si_phone' => $this->data['phone'], 'si_state' => ['neq', 2]])->count()){
            $this->apiReturn(201, '', '手机号码已存在');
        }

        $result = $this->validate($this->data, 'Shop');
        $result !== true && $this->apiReturn(201, '', $result);

        $data = [];
        $fields = ['shopName', 'type', 'provinceId', 'provinceName', 'cityId', 'cityName', 'areaId', 'areaName', 'address', 'describes', 'corporation', 'phone', 'idCard', 'idCardPicOn', 'idCardPicOff', 'license', 'image'];
        foreach($this->data as $key => $value){
            if(in_array($key, $fields)){
                $data['si_' . $key] = $value;
            }
        }

        $data['si_createTime'] = time();
        $data['si_userId']     = $this->userId;

        try{
            Db::startTrans();

            $result = Db::name('shop_info')->insert($data);
            if(!$result){
                throw new Exception('提交失败');
            }

            $orgId = Db::name('shop_info')->getLastInsID();

            $user = [
                'shopId'   => $orgId,
            ];

            $result = Db::name('shop_user')->where(['shop_user_id' => $this->userId])->update($user);
            if($result === false){
                throw new Exception('更新用户信息失败');
            }

            Db::commit();
            $this->apiReturn(200, '', '提交成功');
        }catch (Exception $e){
            Db::rollback();
            $this->apiReturn(201, '', '提交失败');
        }
    }

    public function advanceOrder(){
        $page = (isset($this->data['page']) && !empty($this->data['page'])) ? $this->data['page'] + 0 : 1;
        $rows = (isset($this->data['rows']) && !empty($this->data['rows'])) ? $this->data['rows'] + 0 : 10;

        $where = ['shop_user_id' => $this->userId];
        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars($this->data['keywords']);
            
        }
    }

}