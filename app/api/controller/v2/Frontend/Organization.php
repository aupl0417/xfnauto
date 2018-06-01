<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v2\Frontend;

use app\api\model\CustomerOrder;
use think\Controller;
use think\Db;
class Organization extends Home
{

    /**
     * 门店列表
     * */
    public function index(){
        $page  = isset($this->data['page']) && !empty($this->data['page']) ? $this->data['page'] + 0 : 1;
        $rows  = isset($this->data['rows']) && !empty($this->data['rows']) ? $this->data['rows'] + 0 : 10;

        $where   = ['status' => 1, 'orgId'    => ['in', $this->orgIds]];

        if(isset($this->data['keywords']) && !empty($this->data['keywords'])){
            $keywords = htmlspecialchars(trim($this->data['keywords']));
            $field    = is_numeric($keywords) ? 'telephone' : 'shortName';
            $where[$field] = ['like', '%' . $keywords . '%'];
        }

        $data = model('Organization')->getOrgData($where, [], $page, $rows, 'orgLevel asc,orgId desc');
        $this->apiReturn(200, $data);
    }

}