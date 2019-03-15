<?php
/**
 * Created by PhpStorm.
 * User: liaozijie
 * Date: 2018-04-25
 * Time: 9:28
 */

namespace app\api\controller\v2;

use aupl\Email;
use Qiniu\Auth;
use function Qiniu\base64_urlSafeEncode;
use Qiniu\Processing\PersistentFop;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use think\Cache;
use think\Controller;
use think\Db;
class Upload extends Controller {

    public function index(){
        $title = '请上传Excel文件';
        cache(md5('file_count'), null);
        cache(md5('files_zip'), null);
        $domain = (is_https() ? 'https://api.' : 'http://api.') . config('url_domain_root');
        return $this->fetch('index',['sessionId' => session_id(), 'title' => $title, 'type' => 'excel', 'domain' => $domain]);
    }

    public function upImage(){
//        empty(input('email')) && $this->apiReturn(201, '', '');
        $title = '请上传图片';
        $domain = (is_https() ? 'https://api.' : 'http://api.') . config('url_domain_root');
        return $this->fetch('index',['sessionId' => session_id(), 'title' => $title, 'type' => 'image', 'domain' => $domain]);
    }

    public function uploadHandle(){
        $file = request()->file('file');
        !$file && $this->apiReturn(201, '', '请上传文件');

        //要上传图片的本地路径
        $filePath = $file->getRealPath();
        $fileName = $file->getInfo('name');
        $ext      = pathinfo($fileName, PATHINFO_EXTENSION);  //后缀
        $type     = input('type');
        !in_array($type, ['excel', 'image'], true) && $this->apiReturn(201, '', '上传类型不正确');
        if($type == 'excel'){
            $extArr = ['xlsx', 'xls'];
            $errorMsg = '请上传Excel文件';
        }else{
            $extArr = ['jpg', 'png', 'bmp', 'jpeg'];
            $errorMsg = '请上传图片文件';
        }

        if(!in_array(strtolower($ext), $extArr, true)){
            $this->apiReturn(201, '', $errorMsg);
        }
        $domain = config('url_domain_root');
        if($type == 'excel'){
            $reader = \PHPExcel_IOFactory::createReader('Excel2007');
            $excel = $reader->load($filePath);
            //读取第一张表
            $sheet = $excel->getSheet(0);
            //获取总行数
            $rowNum = $sheet->getHighestRow();
            //获取总列数
            $colNum = $sheet->getHighestColumn();
            $data = []; //数组形式获取表格数据
            for($col = 'A';$col <= $colNum; $col++) {
                for($row = 2; $row <= $rowNum; $row++) {
                    $cellValue = $sheet->getCell($col . $row)->getValue();
                    $data[$row-2][] = $cellValue;
                }
            }

            if($data){
                $i = 0;
                foreach($data as $key => &$item){
                    $item = array_filter($item);
                    if(empty($item)){
                        unset($data[$key]);
                    }else{
                        $i += count($item) - 1;
                        $dir = ROOT_PATH . 'zip/' . $item[0];
                        if(!is_dir(ROOT_PATH . 'zip/')){
                            @mkdir(ROOT_PATH . 'zip/', 0777);
                        }
                        if(!is_dir($dir)){
                            @mkdir($dir, 0777);
                        }
//                        $fp = @fopen($dir . '/' . $item[0] . '.txt', 'a+');
//                        fwrite($fp, implode('    ', $item));
//                        fclose($fp);
                    }
                }
            }
            cache(md5('file_count'), $i, 86400);
            cache(md5('files_zip'), $data, 86400);
            $this->apiReturn(200, ['url' => (is_https() ? 'https://api.' : 'http://api.') . $domain . '/upload_v2/upImage']);
        }else{
            $data = cache(md5('files_zip'));
//            dump($data);die;
            !$data && $this->apiReturn(202, ['url' => (is_https() ? 'https://api.' : 'http://api.') . $domain . '/upload_v2/index'], '请上传Excel文件');
            $fileCount = cache(md5('file_count'));
//            cache(md5('files_zip'), null);
            $infoKey = md5('info_key');
            $info = Cache::has($infoKey) ? \cache($infoKey) : [];
            foreach($data as $key => $value){
                $frameNumber = array_shift($value);//车架号
                $dir = ROOT_PATH . 'zip/' . trim($frameNumber);
                foreach($value as $files){
                    if(strpos($fileName, $files) === false){//如果不是对应文件，则跳过
                        continue;
                    }
                    $grabImage = grabImage($filePath, $dir, $files);
                    if($grabImage){
                        if(!in_array($grabImage, $info, true)){
                            $info[] = $grabImage;
                        }
                    }
                    \cache($infoKey, $info, 3600);
                }
            }

            if(count($info) == $fileCount){
                \cache($infoKey, null);
                $filename = '../zip.zip';
                $cacheKey = md5('zip_file_name');
                \cache($cacheKey, $filename, 3600);
            }
            $this->apiReturn(200);
        }
    }

    public function makeZip(){
        set_time_limit(0);
        $to = input('email');
        empty($to) && $this->apiReturn(201, '', '请输入接收邮箱');
        $filename = '../zip.zip';
        if(!file_exists($filename)){
            $zip = new \ZipArchive();
            if ($zip->open($filename, \ZipArchive::OVERWRITE|\ZipArchive::CREATE)) {
                if(!is_dir('../zip/')){
                    $this->apiReturn(201, '', '请先上传Excel文件');
                }
                addFileToZip('../zip/', $zip); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
                @$zip->close(); //关闭处理的zip文件
            }
            delDirAndFile(ROOT_PATH . 'zip/');
        }
        $email  = new Email();
        $body   = '请下载以下附件！';
        $zipName = date('YmdHis');
        $attachment = [
            $zipName . '.zip' => $filename
        ];
        $result = $email->sendMail($to, $body, '', '谢谢老板', $attachment, '谢谢老板');
        if($result){
            if(file_exists($filename)){
                unlink($filename);
            }
            delDirAndFile(ROOT_PATH . 'zip/');
            $result && statistics(true, 1);
        }
        $this->apiReturn($result ? 200 : 201);
    }

    public function uploadHandle_bac(){
        $file = request()->file('file');
        !$file && $this->apiReturn(201, '', '请上传文件');

        //要上传图片的本地路径
        $filePath = $file->getRealPath();
        $fileName = $file->getInfo('name');
        $ext      = pathinfo($fileName, PATHINFO_EXTENSION);  //后缀
        $type     = input('type');
        !in_array($type, ['excel', 'image'], true) && $this->apiReturn(201, '', '上传类型不正确');
        if($type == 'excel'){
            $extArr = ['xlsx', 'xls'];
            $errorMsg = '请上传Excel文件';
        }else{
            $extArr = ['jpg', 'png', 'bmp', 'jpeg'];
            $errorMsg = '请上传图片文件';
        }
        $rule     = [
            'size' => 2000000,
            'ext'  => $extArr,
        ];
        if(!$file->check($rule)){
            $this->apiReturn(201, '', $errorMsg . '_' . $file->getError());
        }

        if($type == 'excel'){
            $reader = \PHPExcel_IOFactory::createReader('Excel2007');
            $excel = $reader->load($filePath);
            //读取第一张表
            $sheet = $excel->getSheet(0);
            //获取总行数
            $rowNum = $sheet->getHighestRow();
            //获取总列数
            $colNum = $sheet->getHighestColumn();
            $data = []; //数组形式获取表格数据
            for($col = 'A';$col <= $colNum; $col++) {
                for($row = 2; $row <= $rowNum; $row++) {
                    $cellValue = $sheet->getCell($col . $row)->getValue();
                    $data[$row-2][] = $cellValue;
                }
            }

            $i = 0;
            if($data){
                foreach($data as $key => &$item){
                    $item = array_filter($item);
                    if(empty($item)){
                        unset($data[$key]);
                    }
                    $i += count($item) - 1;
                }
            }

            cache(md5('file_count'), $i, 86400);
            cache(md5('files_zip'), $data, 86400);
            $this->apiReturn(200, ['url' => 'http://api.xfnautos.com/upload_v2/upImage']);
        }else{
//            cache(md5('file_count'), null);
//            cache(md5('files_zip'), null);die;
//            cache(md5('url_data'), null);die;
//            dump(cache(md5('url_data')));die;
            $data = cache(md5('files_zip'));
            !$data && $this->apiReturn(201, ['url' => 'http://api.xfnautos.com/upload_v2/index'], '请上传Excel文件');
            $fileCount = cache(md5('file_count'));
//            dump($fileCount);die;
            foreach($data as $key => $value){
                $frameNumber = $value[0];//车架号
                $frameNumber = array_shift($value);
                foreach($value as $file){
                    if(strpos($fileName, $file) === false){//如果不是对应文件，则跳过
                        continue;
                    }
                    // 上传到七牛后保存的文件名
                    vendor('Qiniu.autoload');
                    $accessKey = '540c9PMHKRytUmSi0879CAVu17gaDSFdOtz29vWI';
                    $secret    = 'YYTu6sfB95H3EYnxIIuDsoxJcxRRCppkY-FqFSpD';
                    $bucket    = 'dcsx004';
                    $domain    = 'qiniu4.xfnauto.com';
                    $auth      = new Auth($accessKey, $secret);
                    $token     = $auth->uploadToken($bucket);
                    $bucketManager = new BucketManager($auth);

                    $upload    = new UploadManager();
                    $pFop      = new PersistentFop($auth, null);
                    list($ret, $err) = $upload->putFile($token, $frameNumber . '/' . str_replace(['交强险', '商业险'], '', $fileName), $filePath);
                    if ($err !== null) {
                        $this->apiReturn(201, ['state' => 'error', 'msg' => $err]);
                    } else {
                        $url = 'http://' . $domain . '/' . $ret['key'];
                        $urlDataKey = md5('url_data');
                        $urlData   = Cache::has($urlDataKey) ? cache($urlDataKey) : [];
                        if(!in_array($url, $urlData, true)){
                            $urlData[] = $url;
                            cache($urlDataKey, $urlData, 3600);
                        }
//                        dump($urlData);
//                        dump($fileCount);
//                        dump(count($urlData));
                        if($fileCount == count($urlData)){//如果是最后一个文件
                            $endKey = $urlData[0];
                            $endKey = explode('/', $endKey);
                            $endKeyCount = count($endKey);
                            $endKey = $endKey[$endKeyCount - 2] . '/' . end($endKey);
//                            dump($endKey);
                            $zipKey = $endKey . date('YmdHi') . '.zip';
                            $fops       = 'mkzip/2/encoding/' . base64_urlSafeEncode('utf-8');
                            $bucket     = 'dcsx004';
                            $domain     = 'qiniu4.xfnauto.com';
                            $notify_url = 'http://api.' . config('url_domain_root') . '/upload_v2/notify';
                            $force      = false;
                            $pipeline   = 'xfnauto_pipeline';
                            for($i = 0; $i < count($urlData); $i ++){
                                $path = $urlData[$i];
                                $path = explode('/', $path);
                                $path = $path[count($path) - 2] . '/' . end($path);
//                                $fops .= '/url/' . base64_urlSafeEncode($urlData[$i]) . '/alias/' . base64_urlSafeEncode($urlData[$i]);
                                $fops .= '/url/' . base64_urlSafeEncode($urlData[$i]) . '/alias/' . base64_urlSafeEncode($path);
                            }

                            $fops .= '|saveas/' . base64_urlSafeEncode("$bucket:$zipKey");
                            cache($urlDataKey, null);
                            list($id, $err) = $pFop->execute($bucket, $endKey, $fops, $pipeline, $notify_url, $force);
                            dump($id);
                            dump($err);
                            if($err === null){
                                $cacheKey = md5('prefopId_' . $id);
                                $postData = [
                                    'body'       => '这是邮件内容',
                                    'email'      => '770517692@qq.com',
                                    'user'       => '',
                                    'subject'    => '',
//                                    'attachment' => './loan_' . $orderId . '.zip',
                                ];
//                                cache($cacheKey, null);
                                cache($cacheKey, $postData, 86400 * 7);
                                $res = "http://api.qiniu.com/status/get/prefop?id=$id";
                                $result = curl_get($res);
                                return true;
                            }else{
                                continue;
                            }
                        }else{
                            //返回图片的完整URL
                            $this->apiReturn(200, ['state' => 'success', 'fileUrl' => $url, 'url' => '']);
                        }
                    }
                }
            }
        }
    }

    /*
	* 	返回数据到客户端
	*	@param $code type : int		状态码
	*   @param $info type : string  状态信息
	*	@param $data type : mixed	要返回的数据
	*	return json
	*/
    public function apiReturn($code, $data = null, $msg = ''){
        header('Content-Type:application/json; charset=utf-8');//返回JSON数据格式到客户端 包含状态信息

        $jsonData = array(
            'resultCode' => $code,
            'message'    => $msg ?: ($code == 200 ? '请求成功' : '请求失败'),
            'data'       => $data ? $data : null
        );

        exit(json_encode($jsonData));
    }

    public function excelImport(){
        $title = '请上传Excel文件';
        $domain = (is_https() ? 'https://api.' : 'http://api.') . config('url_domain_root');
        return $this->fetch('index3',['sessionId' => session_id(), 'title' => $title, 'type' => 'excel', 'domain' => $domain]);
    }

    public function uploadExcel(){
        $file = request()->file('file');
        !$file && $this->apiReturn(201, '', '请上传文件');

        //要上传图片的本地路径
        $filePath = $file->getRealPath();
        $fileName = $file->getInfo('name');
        $ext      = pathinfo($fileName, PATHINFO_EXTENSION);  //后缀
        $type     = input('type');
        !in_array($type, ['excel', 'image'], true) && $this->apiReturn(201, '', '上传类型不正确');
        if($type == 'excel'){
            $extArr = ['xlsx', 'xls'];
            $errorMsg = '请上传Excel文件';
        }else{
            $extArr = ['jpg', 'png', 'bmp', 'jpeg'];
            $errorMsg = '请上传图片文件';
        }

        if(!in_array(strtolower($ext), $extArr, true)){
            $this->apiReturn(201, '', $errorMsg);
        }
        $domain = config('url_domain_root');
        if($type == 'excel'){
            $reader = \PHPExcel_IOFactory::createReader('Excel5');
            $excel = $reader->load($filePath);
            //读取第一张表
            $sheet = $excel->getSheet(0);
            //获取总行数
            $rowNum = $sheet->getHighestRow();
            //获取总列数
            $colNum = $sheet->getHighestColumn();
            $data = []; //数组形式获取表格数据
            for($col = 'A';$col <= $colNum; $col++) {
                for($row = 2; $row <= $rowNum; $row++) {
                    $cellValue = $sheet->getCell($col . $row)->getValue();
                    $data[$row-2][] = $cellValue ?: '';
                }
            }

            $carData = [];
            if($data){
                $field = ['carName', 'color', 'interiorColor', 'guidePrice', 'frameNumber', 'gearbox', 'produceYear', 'type', 'purchasePrice', 'paidDeposit', 'amount', 'userName', 'phone', 'buyerNumber', 'purchaseAmount', 'address'];
                foreach($data as $key => $item){
                    if(empty(array_filter($item))){
                        unset($data[$key]);
                        continue;
                    }
                    array_shift($item);
                    $carData[] = array_combine($field, $item);
                }
            }
            $carData = array_values($carData);
            unset($data, $item, $value);
            dump($carData);die;
        }else{
            $data = cache(md5('files_zip'));
//            dump($data);die;
            !$data && $this->apiReturn(202, ['url' => (is_https() ? 'https://api.' : 'http://api.') . $domain . '/upload_v2/index'], '请上传Excel文件');
            $fileCount = cache(md5('file_count'));
//            cache(md5('files_zip'), null);
            $infoKey = md5('info_key');
            $info = Cache::has($infoKey) ? \cache($infoKey) : [];
            foreach($data as $key => $value){
                $frameNumber = array_shift($value);//车架号
                $dir = ROOT_PATH . 'zip/' . trim($frameNumber);
                foreach($value as $files){
                    if(strpos($fileName, $files) === false){//如果不是对应文件，则跳过
                        continue;
                    }
                    $grabImage = grabImage($filePath, $dir, $files);
                    if($grabImage){
                        if(!in_array($grabImage, $info, true)){
                            $info[] = $grabImage;
                        }
                    }
                    \cache($infoKey, $info, 3600);
                }
            }

            if(count($info) == $fileCount){
                \cache($infoKey, null);
                $filename = '../zip.zip';
                $cacheKey = md5('zip_file_name');
                \cache($cacheKey, $filename, 3600);
            }
            $this->apiReturn(200);
        }
    }

}