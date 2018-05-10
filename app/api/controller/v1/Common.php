<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v1;

use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use think\Controller;
use think\Db;
use think\Request;

class Common extends Home
{

    public function area(){
        $pid = isset($this->data['id']) || empty($this->data['id']) ? $this->data['id'] + 0 : 0;
        $data = model('Area')->getAreaList($pid);
        $this->apiReturn(200, $data);
    }

    public function brand(){
        $model = model('Brand');
        $data  = $model->getBrandList();
        !$data && $this->apiReturn(201);
        $this->apiReturn(200, $data);
    }

    /*
     * 通过汽车品牌ID获取车系
     * */
    public function series(){
        (!isset($this->data['bid']) || empty($this->data['bid'])) && $this->apiReturn(201, '', '品牌ID非法');
        $brandId = $this->data['bid'] + 0;

        $model = model('Brand');
        $data  = $model->getCarFamilyByBrandId($brandId);
        !$data && $this->apiReturn(201);
        $this->apiReturn(200, $data);
    }

    /*
     * 通过车系查询
     * */
    public function carList(){
        (!isset($this->data['fid']) || empty($this->data['fid'])) && $this->apiReturn(201, '', '系列ID非法');
        $page = isset($this->data['page']) ? $this->data['page'] + 0: 1;

        $familyId = $this->data['fid'] + 0;
        $field = 'carId as id,carName as name,indexImage as image,price,pl as output,styleName';
        $model = model('Car');
        $data  = $model->getCarByFamilyId($familyId, $field, $page);
        !$data && $this->apiReturn(201);
        $this->apiReturn(200, $data);
    }

    public function share(){
        set_time_limit(0);
        require_once '../extend/mpdf/mpdf.php';
//        $mpdf = new \mPDF('utf-8','A4','','',25 , 25, 16, 16); //'utf-8' 或者 '+aCJK' 或者 'zh-CN'都可以显示中文
        $mpdf = new \mPDF('utf-8','A4','','',0 , 0, 0, 0); //'utf-8' 或者 '+aCJK' 或者 'zh-CN'都可以显示中文
        //设置字体，解决中文乱码
        $mpdf->useAdobeCJK = TRUE;
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;
        //$mpdf-> showImageErrors = true; //显示图片无法加载的原因，用于调试，注意的是,我的机子上gif格式的图片无法加载出来。
        //设置pdf显示方式
        $mpdf->SetDisplayMode('fullpage');
        //目录相关设置：
        //Remember bookmark levels start at 0(does not work inside tables)H1 - H6 must be uppercase
        //$this->h2bookmarks = array('H1'=>0, 'H2'=>1, 'H3'=>2);
//        $mpdf->h2toc = array('H3'=>0,'H4'=>1,'H5'=>2);
//        $mpdf->h2bookmarks = array('H3'=>0,'H4'=>1,'H5'=>2);
        $mpdf->mirrorMargins = 1;
        //是否缩进列表的第一级
        $mpdf->list_indent_first_level = 0;

        $options = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];

        $html = $this->fetch('v1/common/index');
        $html = urldecode($html);
//        $url = 'http://api.xfnauto.com/index.html';
//        $html = file_get_contents($url, false, stream_context_create($options));
        $mpdf->WriteHTML($html);  //$html中的内容即为变成pdf格式的html内容。
        $microtime   = explode('.', microtime(true));
        $fileName    = date('YmdHis') . end($microtime);
        $pdfFileName = $fileName . '.pdf';
        //输出pdf文件
        $mpdf->Output('upload/' . $pdfFileName); //'I'表示在线展示 'D'则显示下载窗口
        if(file_exists('upload/' . $pdfFileName)){
            $result = pdf2png('upload/' . $pdfFileName, 'upload/image');
            unlink('upload/' . $pdfFileName);
            if($result){
                $auth  = new Auth(config('qiniu.accesskey'), config('qiniu.secretkey'));
                $token = $auth->uploadToken(config('qiniu.bucket'));
                $data  = array();
                foreach($result as $key => $value){
                    $upload = new UploadManager();
                    list($ret, $err) = $upload->putFile($token, $value, $value);
                    if($err === null){
                        $data[$key] = 'http://' . config('qiniu.domain') . '/' . $ret['key'];
                    }
                    unlink($value);
                }
                $this->apiReturn(200, $data);
            }else{
                $this->apiReturn(201, '', '图片生成失败');
            }
        }else{
            $this->apiReturn(201, '', '文件不存在');
        }
    }

}