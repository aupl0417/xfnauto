<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1\_1;

use app\api\controller\v1\Home;
use think\Controller;
use think\Db;
class Test extends Home
{

    public function index(){
        $this->apiReturn(200);
    }

}