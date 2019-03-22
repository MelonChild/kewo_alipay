<?php
/*
 *  Copyright (c) 2014 The CCP project authors. All Rights Reserved.
 *
 */
namespace Kewo;
require_once "lib/phpqrcode.php";

use Kewo\lib\WxPayApi;
use Kewo\lib\WxPayException;
use Kewo\lib\JsApiPay;

class Wechat
{
    private $appid; //公众账号ID
    private $mchid; //商户号
    private $paykey; //支付key
    private $appsecret;
    private $app;
    private $enabeLog = true; //日志开关。可填值：true、
    private $Filename = "./wechatlog.txt"; //日志文件
    private $Handle;
    private $batch; //时间戳
    private $otherObj; //支付接口对象
    private $signType = 'MD5'; //支付签名方式
    private $proxyHost = '0.0.0.0'; //默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
    private $proxyPort = 0; 
    private $sslCertPath; //证书路径,注意应该填写绝对路径（仅退款、撤销订单时需要，可登录商户平台下载，
    private $sslKeyPath; 

    public function __construct($app='')
    {
        $this->app = $app;
    }
    
    /**
     * 创建支付环境
     * 
     * @param appid 公众账号ID
     * @param mchid 商户号
     *
     * @param AppId 应用ID
     */
    static function payInstance($appid,$mchid,$paykey,$app=1)
    {
        $obj = new Wechat;
        $obj->appid = $appid;
        $obj->mchid = $mchid;
        $obj->paykey = $paykey;
        $obj->app = $app;

        $obj->otherObj = new WxPayApi;

        return $obj;
    }
       
    /**
     * 
     * 创建二维码生成环境
     * 
     */
    static function qrInstance()
    {
        $obj = new Wechat;
        $obj->otherObj = new WxPayApi;

        return $obj;
    }
      
    /**
     * 
     * 创建js授权环境
     * 
     */
    static function jsInstance($appid,$appsecret)
    {
        $obj = new Wechat;
        $obj->appid = $appid;
        $obj->appsecret = $appsecret;
        $obj->otherObj = new JsApiPay;

        return $obj;
    }
        
    /**
     * 创建回调环境
     * 
     * @param appid 公众账号ID
     * @param mchid 商户号
     *
     * @param AppId 应用ID
     */
    static function notifyInstance($paykey,$app=1)
    {
        $obj = new Wechat;
        $obj->paykey = $paykey;
        $obj->app = $app;

        $obj->otherObj = new WxPayApi;

        return $obj;
    }

    /**
     * 获取支付key
     *
     * @param 
     */
    public function getPayKey()
    {
        return $this->paykey;
    }
    
    /**
     * 获取签名方式
     *
     * @param 
     */
    public function getSignType()
    {
        return $this->signType;
    }
    
    /**
     * 设置签名方式
     *
     * @param string type
     */
    public function setSignType($type='MD5')
    {
        if($type =='MD5' || $type =='HMAC-SHA256'){
            $this->signType = $type;
        }
    }

    /**
	 * 
	 * APPID：绑定支付的APPID
	 * 
	 * MCHID：商户号
	 * 
	 */
	public function GetAppId()
	{
		return $this->appid;
    }
    public function GetAppsecret()
	{
		return $this->appsecret;
    }
	public function GetMerchantId()
	{
		return $this->mchid;
    }
    
    /**
	 * TODO：这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
	 * 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
	 * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
	 * @var unknown_type
	 */
    public function setProxy($proxyHost="0.0.0.0", $proxyPort=0)
	{
		$this->proxyHost = "0.0.0.0";
		$this->proxyPort = 0;
	}
	public function GetProxy(&$proxyHost, &$proxyPort)
	{
		$proxyHost = $this->proxyHost;
		$proxyPort = $this->proxyPort;
    }
    
    /**
	 * TODO：设置商户证书路径
	 * 证书路径,注意应该填写绝对路径（仅退款、撤销订单时需要，可登录商户平台下载，
	 * API证书下载地址：https://pay.weixin.qq.com/index.php/account/api_cert，下载之前需要安装商户操作证书）
	 * 注意:
	 * 1.证书文件不能放在web服务器虚拟目录，应放在有访问权限控制的目录中，防止被他人下载；
	 * 2.建议将证书文件名改为复杂且不容易猜测的文件名；
	 * 3.商户服务器要做好病毒和木马防护工作，不被非法侵入者窃取证书文件。
	 * @var path
	 */
    public function setSSLCertPath($sslCertPath, $sslKeyPath)
	{
		$this->sslCertPath = $sslCertPath;
		$this->sslKeyPath = $sslKeyPath;
	}
	public function GetSSLCertPath(&$sslCertPath, &$sslKeyPath)
	{
		$sslCertPath = $this->sslCertPath;
		$sslKeyPath = $this->sslKeyPath;
	}

    /**
     * 主帐号鉴权
     */
    public function accAuth($type=1)
    {
        //检查应用id
        if ($this->app == "") {
            throw new WxPayException('应用ID为空',1004);
        }

        switch ($type) {
            case '1':
                if ($this->appid == "") {
                    throw new WxPayException('appid为空',1002);
                }
                if ($this->mchid == "") {
                    throw new WxPayException('商户id为空',1003);
                }
                if ($this->paykey == "") {
                    throw new WxPayException('商户key为空',1003);
                }
                break;
            
            default:
                # code...
                break;
        }
    }

    /**
	 * 
	 * 生成直接支付url，支付url有效期为2小时,模式二
     * 公众账号ID	appid  是
     * 商户号	mch_id  是
     * 设备号	device_info	否
     * 随机字符串	nonce_str	是
     * 签名	sign	是
     * 签名类型	sign_type	否
     * 商品描述	body	是	
     * 商品详情	detail	否
     * 附加数据	attach	否
     * 商户订单号	out_trade_no	是
     * 标价币种	fee_type	否
     * 标价金额	total_fee	是	
     * 终端IP	spbill_create_ip	是
     * 交易起始时间	time_start	否
     * 交易结束时间	time_expire	否
     * 订单优惠标记	goods_tag	否
     * 通知地址	notify_url	是
     * 交易类型	trade_type	是
     * 商品ID	product_id	否
     * 指定支付方式	limit_pay	否
     * 用户标识	openid	否
     * 电子发票入口开放标识	receipt	否
     * 
	 * @param array $input 参数
	 */
	public function GetPayUrl($input)
	{
        //账号验证
        $this->accAuth();
        //商品描述 验证
        $this->otherObj->verifyData($input,'body',1005,'商品描述为空');
        //商品价格 验证
        $this->otherObj->verifyData($input,'total_fee',1005,'商品价格为空');

        //接口统一提供
        $input['appid'] = $this->appid;
        $input['mch_id'] = $this->mchid;
        $input['device_info'] = $this->app;

        //获取值
        try{
            $result = $this->otherObj->unifiedOrder($input,$this);
            if($result['return_code'] != 'SUCCESS'){
                throw new WxPayException('统一下单失败;'.$result['return_msg'],5707);
            }
            return $result;
        } catch(Exception $e) {
            throw new WxPayException('统一下单失败2',5707);
        }
        
		return false;
    }
    
    
    /**
	 * 
	 * @param string $value 二维码内容
	 * @param string $size 二维码尺寸
	 * @param string $margin 二维码边距
	 */
	public function qrShow($value='',$size=12,$margin=2)
	{
        return \QRcode::png($value, false, 'L', $size, $margin);
    }
         
    /**
	 * 
     * 获取jsapi 配置
	 * @param 
	 */
	public function getJsApiParameters($value='')
	{
        //实例化js
        $jsApiPay = new JsApiPay;
        $key = $this->paykey;
        $result = $jsApiPay->GetJsApiParameters($value ,$key);
        return $result;
    }
    
            
    /**
	 * 
     * 异步通知
	 * @param 
	 */
	public function notify($callback)
	{
        if ($this->paykey == "") {
            throw new WxPayException('商户key为空',1003);
        }
        $key = $this->paykey;
        $result = $this->otherObj->notify($key);

        return call_user_func($callback, $result);
	}
    
    
    
    /**
	 * 
     * 用户授权获取 openid unionid
	 * @param 
	 */
	public function getOpenId()
	{ 
        $configs["appid"] = $this->appid;
		$configs["secret"] = $this->appsecret;
        $openId = $this->otherObj->GetOpenid($configs);

        return $openId;
	}

    /**
     * 设置应用ID
     *
     * @param AppId 应用ID
     */
    public function setAppId($appid)
    {
        $this->appid = $appid;
    }

    /**
     * 设置应用密匙
     *
     * @param Appsecret 应用密匙
     */
    public function setAppSecret($appsecret)
    {
        $this->appsecret = $appsecret;
    }
    
    /**
     * 设置接口所属应用
     *
     * @param app 应用id
     */
    public function setApp($app)
    {
        $this->app = $app;
    }
    
    /**
     * 设置用户角色，登录时候用
     *
     * @param role 用户角色
     */
    public function setRole($role)
    {
        $this->role = $role;
    }
    
    /**
     * 设置日志开关
     *
     * @param enabeLog 应用密匙
     */
    public function setEnabeLog($enabeLog)
    {
        $this->enabeLog = $enabeLog;
    }

    /**
     * 打印日志
     *
     * @param log 日志内容
     */
    public function showlog($log)
    {
        if ($this->enabeLog) {
            fwrite($this->Handle, $log . "\n");
        }
    }

    /**
     * 发起HTTPS请求
     *
     * @param url 请求路径
     * @param data 发送数据
     * @param header 请求头部信息
     * @param post 请求方式  默认为1 post请求   0为get 请求
     */
    public function curl_post($url, $data=[], $header, $post = 1)
    {
        //初始化curl
        $ch = curl_init();
        //参数设置
        if ($post) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        } else {
            $url = $url.'?'.http_build_query($data);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, $post);
        

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        //连接失败
        if ($result == false) {
            $result = "{\"errcode\":\"1001\",\"errmsg\":\"网络错误\"}";
        }

        curl_close($ch);
        return $result;
    }

    /**
     * 发起HTTPS请求
     *
     * @param url 请求路径
     * @param path 文件相对路径
     */
    public function curl_post_file($url, $path)
    {
        //初始化curl
        $ch = curl_init();
        if (class_exists('\CURLFile')) {
            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
            $data = array('media' => new \CURLFile(realpath($path))); //>=5.5
        } else {
            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
            }
            $data = array('media' => '@' . realpath($path)); //<=5.5
        }
        //参数设置
        $res = curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        //连接失败
        if ($result == false) {
            $result = "{\"errcode\":\"1001\",\"errmsg\":\"网络错误\"}";
        }

        curl_close($ch);
        return $result;
    }

    /**
     * 账号密码登录
     */
    public function loginByAccount($username,$password,$role=3,$version=1)
    {
        //鉴权信息验证，对必选参数进行判空。
        $auth = $this->accAuth();
        if ($auth != "") {
            return $auth;
        }
        //生成token

        //检测用户角色
        $role || $role = $this->role;
        $this->showlog("login by account, request datetime = " . date('y/m/d h:i') . "\n");

        // 生成请求URL
        $url = $this->baseUrl."loginIn";

        // 生成包头
        $header = array("Accept:application/json", "Content-Type:application/json;charset=utf-8");

        //数据
        $time = time();
        $data['appid']=$this->appid;
        $data['version']=$version;
        $data['token']=md5(md5($this->appsecret).$time).$time;

        $data['username']=$username;
        $data['password']=md5($password);
        $data['role']=$role;
        $data['app']=$this->app;
        $data['type']=1;

        // 发送请求
        $result = $this->curl_post($url, $data, $header, 0);
        $this->showlog("response body = " . $result . "\r\n");
        $datas = json_decode($result, true);

        return $datas;
    }

}
