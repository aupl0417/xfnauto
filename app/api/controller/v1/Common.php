<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1;

use think\Controller;
use think\Db;
class Common extends Home
{

    public function area(){
        $pid = isset($this->data['id']) || empty($this->data['id']) ? $this->data['id'] + 0 : 0;
        $data = model('Area')->getAreaList($pid);
        $this->apiReturn(200, $data);
    }

}