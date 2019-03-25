<?php
use Kewo\Alipay\Alipay;

        $app_id = config('common.alipay.app_id');
        $merchant_private_key = config('common.alipay.merchant_private_key');
        $notify_url = config('common.alipay.notify_url');
        $return_url = config('common.alipay.return_url');
        $request->has('data.redirect_url')  && $return_url = $input['data']['redirect_url'];
        $alipay_public_key = config('common.alipay.alipay_public_key');
        $payInstance = Alipay::payInstance($app_id,$merchant_private_key,$alipay_public_key,$notify_url,$return_url,$app);
        /** 生成直接支付url，支付url有效期为2小时,模式二
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
        */
        //业务必须传入数值 body total_fee
        $input['body'] = 123;
        $input['total_fee'] = 1;
        $input['product_id'] = 123;
        $input['notify_url'] = 123;
        $input['out_trade_no'] = 123;
        //业务选择传入数值 detail total_fee
        $input['detail'] = 123;
        $input['spbill_create_ip'] = '192.168.1.1';

        //NATIVE 支付
        $pc = $type=='PC'?true:false;
        $pay = $payInstance->GetPay($paydata,$pc);
        //
        if($pay){
            //成功
        } else {
            //返回获取失败，重新发起请求
        }



        //异步通知
        /**
     * 微信异步通知
     *
     * @param Request $request
     * @return Response
     * @author MelonChild
     */
    public function notify()
    {
        $arr=$_POST;
        
        $app_id = config('common.alipay.app_id');
        $merchant_private_key = config('common.alipay.merchant_private_key');
        $notify_url = config('common.alipay.notify_url');
        $return_url = config('common.alipay.return_url');
        $alipay_public_key = config('common.alipay.alipay_public_key');
        $payInstance = Alipay::payInstance($app_id,$merchant_private_key,$alipay_public_key,$notify_url,$return_url,1);
        $result = $payInstance->notify($arr);

        if($result) {//验证成功
    
    }

?> 