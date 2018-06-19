<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14 0014
 * Time: 13:39
 */
namespace app\api_v2\validate;
use think\Db;
use think\Validate;


class AddArticle extends Validate{

    protected $rule = [
        'title'                 => 'require',
        'content'               => 'require',
        'type'                  => 'require|in:1,2,3,4',
        'videoUrl'              => 'checkUrl',
        'icon'                  => 'checkUrl',
    ];

    protected $message = [
        'title.require'         => '请输入标题',
        'content.require'       => '请输入文章内容',
        'type.require'          => '请选择文章类型',
        'type.in'               => '文章类型非法',
        'videoUrl.checkUrl'     => '视频地址非法',
        'icon.checkUrl'         => '图片地址非法',
    ];

    public function checkUrl($value){
        if(isset($_REQUEST['videoUrl']) && !empty($_REQUEST['videoUrl'])){
            $videoUrl = htmlspecialchars(trim($_REQUEST['videoUrl']));
            if(!filter_var($videoUrl, FILTER_VALIDATE_URL)) {
                return false;
            }
            return true;
        }

        if(isset($_REQUEST['icon']) && !empty($_REQUEST['icon'])){
            $icon = htmlspecialchars(trim($_REQUEST['icon']));
            if(!filter_var($icon, FILTER_VALIDATE_URL)){
                return false;
            }
            return true;
        }
    }
}