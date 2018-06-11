<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/*
* 	返回数据到客户端
*	@param $code type : int		状态码
*   @param $info type : string  状态信息
*	@param $data type : mixed	要返回的数据
*	return json
*/
function apiReturn($code, $data = null, $msg = ''){
    header('Content-Type:application/json; charset=utf-8');//返回JSON数据格式到客户端 包含状态信息

    $jsonData = array(
        'code' => $code,
        'msg'  => $msg ?: ($code == 200 ? '操作成功' : '操作失败'),
        'data' => $data ? $data : null
    );

    exit(json_encode($jsonData));
}


//得到高强度不可逆的加密字串
function getSuperMD5($str) {
    return MD5(SHA1($str) . '@$^^&!##$$%%$%$$^&&asdtans2g234234HJU');
}

function checkPhone($phone){
    if(!preg_match_all("/^1([358][0-9]|4[579]|66|7[0135678]|9[89])[0-9]{8}$/", $phone, $array)){
        return false;
    }
    return true;
}

function checkEmail($email){
    return preg_match("/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i", $email);
}

/*
 * 生成单号
 * */
function makeOrder(){
    $order = date('YmdHis');
    $array = explode('.', microtime(true));
    return $order . end($array);
}

function curl_post($url,$data,$param=null){
    $curl = curl_init($url);// 要访问的地址
    //curl_setopt($curl, CURLOPT_REFERER, $param['referer']);

    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 MSIE 8.0'); // 模拟用户使用的浏览器
    //curl_setopt($curl, CURLOPT_USERAGENT, 'spider'); // 模拟用户使用的浏览器
    //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    //curl_setopt($curl, CURLOPT_ENCODING, ''); // handle all encodings
    //curl_setopt($curl, CURLOPT_HTTPHEADER, $refer);

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
    //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
    //curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址

    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
    curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
    curl_setopt($curl,CURLOPT_POST,true); // post传输数据
    curl_setopt($curl,CURLOPT_POSTFIELDS,$data);// post传输数据

    //是否为上传文件
    if(!is_null($param)) curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
    $res = curl_exec($curl);
    //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
    curl_close($curl);

    return $res;
}

function curl_get($url){
    $curl = curl_init($url);// 要访问的地址
    //curl_setopt($curl, CURLOPT_REFERER, $param['referer']);

    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.11 (KHTML, like Gecko) Chrome/23.0.1271.97 MSIE 8.0'); // 模拟用户使用的浏览器
    //curl_setopt($curl, CURLOPT_USERAGENT, 'spider'); // 模拟用户使用的浏览器
    //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    //curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    //curl_setopt($curl, CURLOPT_ENCODING, ''); // handle all encodings
    //curl_setopt($curl, CURLOPT_HTTPHEADER, $refer);

    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//SSL证书认证
    //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
    //curl_setopt($curl, CURLOPT_CAINFO,$cacert_url);//证书地址

    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
    curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
    $res = curl_exec($curl);
    //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
    curl_close($curl);

    return $res;
}

function getJson($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
    return json_decode($output, true);
}

function sign($data){
    $app  = array(
        '1' => 'D8OZLSE2NEDC0FR4XTGBKHY67UJZ8IK9', //ios
        '2' => 'DFHGKZLSE2NFDEHGFHHR4XTGBKHY67EJZ8IK9', //安卓
    );

    $secretKey = $app[$data['appId']];
    ksort($data);

    return md5(http_build_string($data) . $secretKey);
}

/**
 * 跟系统的http_build_str()功能相同，但不用安装pecl_http扩展
 *
 * @param array $array      需要组合的数组
 * @param string $separator 连接符
 *
 * @return string               连接后的字符串
 * eg: 举例说明
 */
function http_build_string ( $array, $separator = '&' ) {
    $string = '';
    foreach ( $array as $key => $val ) {
        $string .= "{$key}={$val}{$separator}";
    }
    //去掉最后一个连接符
    return substr( $string, 0, strlen( $string ) - strlen( $separator ) );
}

/**
 * PDF2PNG
 * @param $pdf  待处理的PDF文件
 * @param $path 待保存的图片路径
 * @param $page 待导出的页面 -1为全部 0为第一页 1为第二页
 * @return      保存好的图片路径和文件名
 */
function pdf2png($pdf, $path, $page=-1){
    error_reporting(7);
    if(!extension_loaded('imagick')){
        return false;
    }
    if(!file_exists($pdf)){
        return false;
    }
    $im = new Imagick();
    $im->setResolution(240,240);
    $im->setCompressionQuality(100);
    if($page == -1){
        $im->readImage($pdf);
    }else{
        $im->readImage($pdf."[".$page."]");
    }

    foreach ($im as $Key => $Var) {
        $Var->setImageFormat('png');
        $filename = $path."/". md5($Key.time()).'.png';
        if($Var->writeImage($filename) == true){
            $Return[] = $filename;
        }
    }

    return $Return;
}

/**
 * 运行日志
 * @param $data       数据 type : mixed
 * @param $controller 所在控制器
 * @param $action     方法
 * @param $params     参数 type : mixed
 * */
function logs_write($data, $controller, $action, $params){
    $fp = @fopen(ROOT_PATH . 'debug_' . date('Y-m-d') . ".txt", "a+");
    fwrite($fp, "运行：" . "----" . date('Y-m-d H:i:s') . "\n");
    fwrite($fp, "Data:" . (is_array($data) ? json_encode($data) : $data) . "\n");
    fwrite($fp, "Controller:" . $controller . " Action:" . $action . "\n");
    fwrite($fp, "Params:" . (is_array($params) ? json_encode($params) : $params) . "\n");
    fwrite($fp, "------------------------------------------------------------------------\n\n");
    fclose($fp);
}

function generateImg($source, $text1, $text2, $text3, $font = './msyhbd.ttf') {
    $date = '' . date ( 'Ymd' ) . '/';
    $img  = $date . md5($source . $text1 . $text2 . $text3) . '.jpg';
    if (file_exists ('./' . $img )){
        return $img;
    }

    $main   = imagecreatefromjpeg($source);
    $width  = imagesx($main);
    $height = imagesy($main);

    $target = imagecreatetruecolor($width, $height);
    $white  = imagecolorallocate($target, 255, 255, 255);
    imagefill ($target, 0, 0, $white );

    imagecopyresampled ($target, $main, 0, 0, 0, 0, $width, $height, $width, $height );

    $fontSize  = 18;//磅值字体
    $fontColor = imagecolorallocate ( $target, 255, 0, 0 );//字的RGB颜色
    $fontBox   = imagettfbbox($fontSize, 0, $font, $text1);//文字水平居中实质
    imagettftext ($target, $fontSize, 0, ceil(($width - $fontBox[2]) / 2), 190, $fontColor, $font, $text1);

    $fontBox   = imagettfbbox($fontSize, 0, $font, $text2);
    imagettftext ($target, $fontSize, 0, ceil(($width - $fontBox[2]) / 2), 370, $fontColor, $font, $text2);

    $fontBox   = imagettfbbox($fontSize, 0, $font, $text3);
    imagettftext ($target, $fontSize, 0, ceil(($width - $fontBox[2]) / 2), 560, $fontColor, $font, $text3);

    //imageantialias($target, true);//抗锯齿，有些PHP版本有问题，谨慎使用

    imagefilledpolygon ( $target, array (10 + 0, 0 + 142, 0, 12 + 142, 20 + 0, 12 + 142), 3, $fontColor );//画三角形
    imageline($target, 100, 200, 20, 142, $fontColor);//画线
    imagefilledrectangle ( $target, 50, 100, 250, 150, $fontColor );//画矩形

    //bof of 合成图片
    $child1 = imagecreatefromjpeg ( 'http://gtms01.alicdn.com/tps/i1/T1N0pxFEhaXXXxK1nM-357-88.jpg' );
    imagecopymerge ($target, $child1, 0, 400, 0, 0, imagesx ( $child1 ), imagesy ( $child1 ), 100 );
    //eof of 合成图片

    @mkdir ( './' . $date );
    imagejpeg ( $target, './' . $img, 95 );

    imagedestroy ( $main );
    imagedestroy ( $target );
    imagedestroy ( $child1 );
    return $img;
}

/**
 * 文字自动换行
 * @param $fontsize 字体大小
 * @param $angle    角度
 * @param $fontface 字体名称
 * @param $string   要自动换行的字符串
 * @param $width    预设宽度
 * @return string
 * */
function autowrap($fontsize, $angle, $fontface, $string, $width) {
    $content = "";
    // 将字符串拆分成一个个单字 保存到数组 letter 中
    for ($i=0; $i < mb_strlen($string); $i++) {
        $letter[] = mb_substr($string, $i, 1);
    }
    $height = 0;
    foreach ($letter as $l) {
        $teststr = $content." ".$l;
        $testbox = @imagettfbbox($fontsize, $angle, $fontface, $teststr);
        // 判断拼接后的字符串是否超过预设的宽度
        if (($testbox[2] > $width) && ($content !== "")) {
            $content .= "\n";
        }
        $height = abs($testbox[1]) + abs($testbox[7]);
        $content .= $l;
    }

    return ['content' => $content, 'height' => $height];
}


/**
 * 校验日期格式是否正确
 *
 * @param string $date 日期
 * @param array $formats 需要检验的格式数组
 * @return boolean
 */
function checkDateIsValid($date, $formats = array("Y-m-d", "Y/m/d")) {
    $unixTime = strtotime($date);
    if (!$unixTime) { //strtotime转换不对，日期格式显然不对。
        return false;
    }
    //校验日期的有效性，只要满足其中一个格式就OK
    foreach ($formats as $format) {
        if (date($format, $unixTime) == $date) {
            return true;
        }
    }

    return false;
}

/**
 * 校验日期格式是否正确
 *
 * @param string $date 日期
 * @param array $formats 需要检验的格式数组
 * @return boolean
 */
function checkTimeIsValid($date, $formats = array("Y-m-d H:i:s", "Y/m/d  H:i:s")) {
    $unixTime = strtotime($date);
    if (!$unixTime) { //strtotime转换不对，日期格式显然不对。
        return false;
    }
    //校验日期的有效性，只要满足其中一个格式就OK
    foreach ($formats as $format) {
        if (date($format, $unixTime) == $date) {
            return true;
        }
    }

    return false;
}

/**
 * 按指定宽度等比缩放图片
 * @param string  $img 图片相对地址
 * @param int     $targetWidth 指定宽度
 * @return Array  $path
 * */
function resizeImage($img, $targetWidth = 640){
    if(file_exists($img)){
        $imgInfo = getimagesize($img);
        $width   = $imgInfo[0];
        $height  = $imgInfo[1];
        $ext     = explode('/', $imgInfo['mime'])[1];
        $resizeHeight = $targetWidth * $height / $width;

        $service = new \app\api\service\ImagickService();
        $image   = $service->open($img);
        $result  = $service->resize($targetWidth, $resizeHeight);
        $path    = 'upload/image/' . md5(microtime(true)) . '.' . $ext;
        $service->save_to($path);
        if(!file_exists($path)){
            return false;
        }

        return ['path' => $path, 'height' => $resizeHeight];
    }
}

/**
 * 按指定宽度等比缩放图片
 * @param string  $img 图片相对地址
 * @param int     $targetWidth 指定宽度
 * @return Array  $path
 * */
function resize($img, $targetWidth = 640){
    if(file_exists($img)){
        $imgInfo = getimagesize($img);
        $width   = $imgInfo[0];
        $height  = $imgInfo[1];
        $ext     = explode('/', $imgInfo['mime'])[1];
        $resizeHeight = $targetWidth * $height / $width;

        $service = new \app\api\service\ImagickService();
        $image   = $service->open($img);
        $result  = $service->resize($targetWidth, $resizeHeight);
        $path    = 'upload/image/' . md5(microtime(true)) . '.' . $ext;
        $service->save_to($path);
        if(!file_exists($path)){
            return false;
        }

        return ['path' => $path, 'height' => $resizeHeight];
    }
}

/*
 * 判断是不是https
 * */
function is_https(){
    if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off'){
        return true;
    }elseif(isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ) {
        return true;
    }elseif(!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
        return true;
    }
    return false;
}

/**
 * 生成pdf
 * @param  string $html      需要生成的内容
 * @param $output            保存路径及文件名称
 * @param $type              类型  'I'表示在线展示 'D'则显示下载窗口
 */
function pdf($html='<h1 style="color:red">hello word</h1>', $output, $type = ''){
    vendor('Tcpdf.tcpdf');
    $pdf = new \Tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    // 设置打印模式
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Nicola Asuni');
    $pdf->SetTitle('TCPDF Example 001');
    $pdf->SetSubject('TCPDF Tutorial');
    $pdf->SetKeywords('TCPDF, PDF, example, test, guide');
    // 是否显示页眉
    $pdf->setPrintHeader(false);
    // 设置页眉显示的内容
//    $pdf->SetHeaderData('logo.png', 60, 'baijunyao.com', '白俊遥博客', array(0,64,255), array(0,64,128));
    $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 061', PDF_HEADER_STRING);
    // 设置页眉字体
    $pdf->setHeaderFont(Array('dejavusans', '', '12'));
    // 页眉距离顶部的距离
    $pdf->SetHeaderMargin('5');
    // 是否显示页脚
    $pdf->setPrintFooter(true);
    // 设置页脚显示的内容
    $pdf->setFooterData(array(0,64,0), array(0,64,128));
    // 设置页脚的字体
    $pdf->setFooterFont(Array('dejavusans', '', '10'));
    // 设置页脚距离底部的距离
    $pdf->SetFooterMargin('10');
    // 设置默认等宽字体
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
    // 设置行高
    $pdf->setCellHeightRatio(1);
    // 设置左、上、右的间距
    $pdf->SetMargins('10', '10', '10');
    // 设置是否自动分页  距离底部多少距离时分页
    $pdf->SetAutoPageBreak(TRUE, '15');
    // 设置图像比例因子
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
//    if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
//        require_once(dirname(__FILE__).'/lang/eng.php');
//        $pdf->setLanguageArray($l);
//    }
    $pdf->setFontSubsetting(true);
    $pdf->AddPage();
    // 设置字体
    $pdf->SetFont('stsongstdlight', '', 14, '', true);
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
    $pdf->Output($output, $type);
}

function getRandomString($length = 32){
    $string = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $string = str_shuffle($string);
    return strtoupper(substr(md5($string), 0, $length));
}

/**
  16-19 位卡号校验位采用 Luhm 校验方法计算：
    1，将未带校验位的 15 位卡号从右依次编号 1 到 15，位于奇数位号上的数字乘以 2
    2，将奇位乘积的个十位全部相加，再加上所有偶数位上的数字
    3，将加法和加上校验位能被 10 整除。
*/
function luhm($s) {
    $n = 0;
    for ($i = strlen($s); $i >= 1; $i--) {
        $index = $i-1;
        //偶数位
        if ($i % 2 == 0){
            $n += $s{$index};
        }else{//奇数位
            $t = $s{$index} * 2;
            if ($t > 9){
                $t =(int)($t/10)+ $t % 10;
            }
            $n += $t;
        }
    }
    return ($n % 10) == 0;
}

function checkBankCardNumber($cardNumber){
    $number = str_split($cardNumber);
    $last_n = $number[count($number) - 1];
    krsort($number);

    $total = 0;
    foreach ($number as $key => $n){
        if(($key + 1) % 2 == 0){
            $t = $n * 2;
            if($t >= 10){
                $t = 1 + ($t % 10);
                $total += $t;
            }else{
                $total += $t;
            }
        }else{
            $total += $n;
        }
    }

    $total -= $last_n;
    $total *= 9;
    if($last_n == ($total % 10)){
        return true;
    }
    return false;
}


function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] = &$list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId =  $data[$pid];
            if ($root == $parentId) {
                $tree[] = &$list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent = &$refer[$parentId];
                    $parent[$child][] = &$list[$key];
                }
            }
        }
    }

    return $tree;
}


function getMoneyNumberString($number){
    $moneyNum = explode('.', $number);
    $numberInt = $moneyNum[0];
    $numberArr = array('零', '壹', '贰', '叁', '肆', '伍', '陆', '柒','捌', '玖');
    $numberString = getIntMoney($numberInt, $numberArr);
    if(isset($moneyNum[1])){
        $numberSmall = $moneyNum[1];//小数点后面的数字
        $numberString .= getSmallMoney($numberSmall,$numberArr);
    }
    return $numberString;
}
//处理整数
function getIntMoney($number, $numberArr, $size = 4){
    $moneyUnitSmall = array('', '十', '百','千');//去掉元：不是重复使用的单位
    $moneyUnitBig = array('', '万', '亿', '万亿', '亿亿', '万亿亿');
    $data = array();
    $cycleSize = ceil(strlen($number) / $size) - 1; //外循环次数
    for($i = $cycleSize; $i >= 0; $i--){
        $data[$i] = strrev(substr(strrev($number), $i * $size,$size));//每4位一组
        $data[$i] = str_split($data[$i]);//拆分成数组
        $keySize = count($data[$i]) - 1;//子数组键的最大值
        foreach($data[$i] as $key=>&$val){//使用引用，减少内存开销
            $val = $numberArr[$val] . $moneyUnitSmall[$keySize--];//拼接小单位
        }

        if($data[$i][count($data[$i]) - 1] == '零'){
            unset($data[$i][count($data[$i]) - 1]);
        }
        $data[$i][] = $moneyUnitBig[$cycleSize]; //拼接大单位
        $data[$i] = implode('', $data[$i]);
        $cycleSize --;
    }
    return implode('', $data) . '圆';
}

//处理小数（小数点后三位）
function getSmallMoney($number, $numberArr, $size = 3){
    $moneyUnitArr = array('角','分','厘');
    $count = strlen($number);
    $data = str_split(substr($number, 0, $size));
    $string = '';
    foreach($data as $key=>$val){
        $string .= $numberArr[$val] . $moneyUnitArr[$key];
    }
    return $string;
}

/**
 * 系统邮件发送函数
 * @param string $mailTo     接收邮件者邮箱
 * @param string $name       接收邮件者名称
 * @param string $subject    邮件主题
 * @param string $body       邮件内容
 * @param string $attachment 附件列表
 * @return boolean
 */
function sendMail($mailTo, $body, $name = '', $subject = '', $attachment = null){
    $mail = new \PHPMailer\PHPMailer\PHPMailer();           //实例化PHPMailer对象
    $mail->CharSet = 'UTF-8';           //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码

    $mail->setLanguage('zh_cn');

//    $mail->isSMTP();
//    $mail->Host = 'relay-hosting.secureserver.net';
//    $mail->Port = 25;
//    $mail->SMTPAuth = false;
//    $mail->SMTPSecure = false;

    $mail->isSMTP();
    $mail->Host     = 'smtp.qq.com';
    $mail->Port = 25;
//    $mail->SMTPAuth = false;
//    $mail->SMTPSecure = false;
    $mail->SMTPAuth = true;
    $mail->Username = "770517692@qq.com";    // SMTP服务器用户名
    $mail->Password = "6485654jjun";     // SMTP服务器密码
    $mail->From     = "770517692@qq.com";
    $mail->FromName = '喜蜂鸟';

    if (is_array($mailTo) && !empty($mailTo)) {
        foreach ($mailTo as $item) {
            $mail->addAddress($item);
        }
    } else {
        $mail->addAddress($mailTo, $name);
    }

    if (is_array($attachment) && !empty($attachment)) { // 添加附件
        foreach ($attachment as $file) {
            is_file($file) && $mail->AddAttachment($file);
        }
    }

    $mail->WordWrap = 50;
    $mail->isHTML(true);
    $mail->Subject  = $subject ?: '大唐云商邮件提醒';
    $mail->Body     = $body;
    $mail->AltBody  = "这是一封HTML邮件，请用HTML方式浏览!";

    return $mail->Send() ? true : $mail->ErrorInfo;
}

