<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/14 0014
 * Time: 13:39
 */
namespace app\api\validate;
use think\Db;
use think\Validate;


class AddArticle extends Validate{

    protected $rule = [
        'title'                 => 'require',
//        'a_keywords'              => 'require|in:1,2',
        'content'               => 'require',
//        'excerpt'               => 'require',
//        'source'                => 'require',
        'videoUrl'              => 'checkUrl',
        'icon'                  => 'checkUrl',
    ];

    protected $message = [
        'title.require'         => '请输入标题',
        'content.require'       => '请输入文章内容',
//        'excerpt.require'       => '请输入文章摘要',
//        'source.require'        => '请输入文章来源',
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