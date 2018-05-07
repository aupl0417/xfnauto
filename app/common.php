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
