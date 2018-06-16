<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v2\Frontend;

use app\api\model\CustomerOrder;
use app\api\model\ShopGoodsCarsActivity;
use think\Controller;
use think\Db;
class Shop extends Home
{

    /**
     * 在售车辆列表
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;
        $overOffShelf  = isset($this->data['overOffShelf']) && !empty($this->data['overOffShelf']) ? $this->data['overOffShelf'] + 0 : 0;
        !in_array($overOffShelf, [0, 1], true) && $this->apiReturn(201, '', '参数非法');

        $where = ['over_delete' => 0, 'over_off_shelf' => $overOffShelf, 'org_id' => ['in', $this->orgIds]];

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = trim($this->data['keywords']);
            $where['cars_name'] = ['like', '%' . $keywords . '%'];
        }

        $field = $this->createField('shop_goods_cars', 'over_delete,');
        $data  = Db::name('shop_goods_cars')->where($where)->field($field)->order('create_date desc')->select();
        $count = Db::name('shop_goods_cars')->where($where)->count();if($data){
            $orgField   = $this->createField('system_organization');
            $userFileds = $this->createField('system_user');
            foreach($data as &$value){
                $organization = model('Organization')->getOrganizationByOrgId($value['orgId'], $orgField);
                $value['organization'] = $organization ?: [];
                $user = model('System_user')->getUserById($value['systemUsersId'], $userFileds);
                $value['usersVo'] = $user ?: [];
            }
        }
        $this->apiReturn(200, ['list' => $data, 'page' => $page, 'rows' => $rows, 'total' => $count]);
    }

    public function activity(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;
        $overOffShelf  = isset($this->data['overOffShelf']) && !empty($this->data['overOffShelf']) ? $this->data['overOffShelf'] + 0 : 0;
        !in_array($overOffShelf, [0, 1], true) && $this->apiReturn(201, '', '参数非法');

        $where = ['over_delete' => 0, 'over_off_shelf' => $overOffShelf, 'org_id' => ['in', $this->orgIds]];

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = trim($this->data['keywords']);
            $where['cars_name'] = ['like', '%' . $keywords . '%'];
        }

        $field = $this->createField('shop_goods_cars_activity', 'over_delete');
        $data  = Db::name('shop_goods_cars_activity')->where($where)->field($field)->order('create_date desc')->select();
        $count = Db::name('shop_goods_cars_activity')->where($where)->count();
        if($data){
            $orgField   = $this->createField('system_organization');
            $userFileds = $this->createField('system_user');
            foreach($data as &$value){
                $organization = model('Organization')->getOrganizationByOrgId($value['orgId'], $orgField);
                $value['organization'] = $organization ?: [];
                $user = model('System_user')->getUserById($value['systemUsersId'], $userFileds);
                $value['usersVo'] = $user ?: [];
            }
        }
        $this->apiReturn(200, ['list' => $data, 'page' => $page, 'rows' => $rows, 'total' => $count]);
    }

}