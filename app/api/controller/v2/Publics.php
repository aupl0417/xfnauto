<?php
namespace app\api\controller\v2;


use app\api\service\Wechat;
use aupl\Bank;
use aupl\Email;
use BaoFoo\AgreementPay\SecurityUtil\SHA1Util;
use BaoFoo\AgreementPay\SecurityUtil\SignatureUtils;
use BaoFoo\AgreementPay\Util\Tools;
use BaoFoo\BaoFooPay;
use BaoFoo\ProtocolPay;
use Pheanstalk\Pheanstalk;
use Qiniu\Auth;
use function Qiniu\base64_urlSafeDecode;
use function Qiniu\base64_urlSafeEncode;
use Qiniu\Config;
use Qiniu\Processing\PersistentFop;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use SMS\SMS;
use think\captcha\Captcha;
use think\Db;
use think\Exception;

class Publics extends Base
{
    public function _404()
    {
        $this->apiReturn(201, '', '非法请求');
    }

    /**
     * 发送短信/邮箱验证码
     * @param account string 手机号码或电子邮箱
     * @return json
     * */
    public function sendCode(){
        (!isset($this->data['account']) || empty($this->data['account'])) && $this->apiReturn(201, '', '请输入手机号码或电子邮箱');
        if(!checkPhone($this->data['account']) && !checkEmail($this->data['account'])){
            $this->apiReturn(201, '', '请输入正确格式的手机号码或电子邮箱');
        }

        $account = trim($this->data['account']);
        if(checkPhone($account)){
            $sms = new SMS();
            $result = $sms->sendCode($account);
        }else{
            $email = new Email();
            $result = $email->sendCode($account);
        }

        !$result && $this->apiReturn(201, '', '验证码发送失败' . $sms->errorMsg);
        $this->apiReturn(200, '', '验证码发送成功');
    }

    public function upload(){
        $file = request()->file('image');
        !$file && $this->apiReturn(201, '', '请上传图片');

        // 要上传图片的本地路径
        $filePath = $file->getRealPath();
        $ext      = pathinfo($file->getInfo('name'), PATHINFO_EXTENSION);  //后缀
        /* $rule     = [
             'size' => 2000000,
             'ext'  => ['jpg', 'png', 'bmp', 'jpeg'],
         ];
         if(!$file->check($rule)){
             $this->apiReturn(201, '', $file->getError());
         }*/

        // 上传到七牛后保存的文件名
        $key = substr(md5($filePath) , 0, 5). date('YmdHis') . rand(0, 9999) . '.' . $ext;

        vendor('Qiniu.autoload');
        $auth  = new Auth(config('qiniu.accesskey'), config('qiniu.secretkey'));
        $token = $auth->uploadToken(config('qiniu.bucket'));

        $upload = new UploadManager();
        list($ret, $err) = $upload->putFile($token, $key, $filePath);
        if ($err !== null) {
            $this->apiReturn(201, ['state' => 'error', 'msg' => $err]);
        } else {
            //返回图片的完整URL
            $url = 'http://' . config('qiniu.domain') . '/' . $ret['key'];
            $decode = md5($url);
            if(!Db::name('files')->where(['p_hash' => $decode])->count()){
                $data = [
                    'p_hash' => $decode,
                    'p_url'  => $url
                ];
                Db::name('files')->insert($data);
            }

            $this->apiReturn(200, ['state' => 'success', 'url' => $url]);
        }
    }

    public function getToken(){
        $cacheKey = md5('qiniu_token');
        $expires  = 86400;
//        if(!$token = cache($cacheKey)){
        vendor('Qiniu.autoload');
        $auth  = new Auth(config('qiniu.accesskey'), config('qiniu.secretkey'));
        $token = $auth->uploadToken(config('qiniu.bucket'), null, $expires);
//            cache($cacheKey, $token, $expires);
//        }
        $this->apiReturn(200, ['token' => $token, 'domain' => config('qiniu.domain')]);
    }

    public function subscribe(){
        (!isset($this->data['openid']) || empty($this->data['openid'])) && $this->apiReturn(201, '', 'OPENID不能为空');
        $openid      = $this->data['openid'];
        $service     = new Wechat();
        $accessToken = $service->getAccessToken();
        $userInfo    = $service->getUserInfo($accessToken, $openid);
        !$userInfo && $this->apiReturn(201, '', '获取用户信息失败');
        Db::startTrans();
        try{
            $wUser = Db::name('wechat_user')->where(['wu_nickName' => $userInfo['nickname'], 'wu_openId' => $userInfo['openid']])->field('wu_id as id,wu_hash as hash')->find();
            $data = [
                'wu_nickName'      => $userInfo['nickname'],
                'wu_sex'           => $userInfo['sex'],
                'wu_openId'        => $userInfo['openid'],
                'wu_city'          => $userInfo['city'],
                'wu_province'      => $userInfo['province'],
                'wu_country'       => $userInfo['country'],
                'wu_headImgUrl'    => $userInfo['headimgurl'],
                'wu_subscribe'     => $userInfo['subscribe'],
                'wu_uninId'        => $userInfo['unionid'],
                'wu_subscribeTime' => $userInfo['subscribe_time'],
            ];
            $sign = $this->makeSign($data);
            $data['wu_hash']  = $sign;
            if(!$wUser){
                $result    = Db::name('wechat_user')->insert($data);
                if(!$result){
                    throw new \Exception('添加微信用户信息失败');
                }
            }else{
                if($wUser['hash'] != $sign){
                    $result = Db::name('wechat_user')->where(['wu_id' => $wUser['id']])->update($data);
                    if($result === false){
                        throw new \Exception('更新微信用户信息失败');
                    }
                }
            }
            Db::commit();
            $this->apiReturn(200);
        }catch (\Exception $e){
            $this->apiReturn(201, '', $e->getMessage());
        }
    }

    private function makeSign($data){
        ksort($data);
        return md5($this->http_build_string($data));
    }

}
