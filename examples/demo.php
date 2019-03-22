<?php
use Kewo\wechat;

        $appID = config('common.wepay.appID');
        $mchID = config('common.wepay.mchId');
        $key = config('common.wepay.key');
        $payInstance = wechat::payInstance($appID,$mchID,$key);
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
        $input['attach'] = 123;
        $input['fee_type'] = 'CNY';
        $input['spbill_create_ip'] = '192.168.1.1';

        //NATIVE 支付
        $input['trade_type'] = 'NATIVE';
        $result = $payInstance->GetPayUrl($input);

        //
        if($result&&isset($result['result_code'])&&$result['result_code']=='SUCCESS'){
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
        $key = config('common.wepay.key');
        $notifyInstance = kewoWechat::notifyInstance($key);
        
        //异步通知
        $notifyInstance->notify(array($this, 'NotifyCallBack'));
    }

    //回调处理函数 当所有验证成功后回调
    public function NotifyCallBack($result)
	{
        //异步通知有返回值
        if($result&&$result['return_code']=='SUCCESS'&&$result['result_code']=='SUCCESS') {
            //创建订单
            $prepay = Prepay::where('code',$result['out_trade_no'])->first();
            $order = Order::firstOrCreate(['number'=>$result['out_trade_no']]); 
            if($order){
                $orderData['paid_at'] = time();
                $orderData['status'] = 1;
                
                $orderData['trade_type'] = $result['trade_type'];
                $orderData['tranid'] = $result['transaction_id'];
                $orderData['app_number'] = $result['attach'];
                $orderData['fee_type'] = $result['fee_type'];
                $orderData['real_fee'] = $result['total_fee'];
                $orderData['usernum'] = $result['openid'];

                $order->update($orderData);

                //异步通知子应用订单状态，加入通知队列
                if($prepay){
                    $orderData['app'] = $prepay['app'];
                    $orderData['body'] = $prepay['body'];
                    $orderData['created_at'] = $prepay['created_at'];
                    $orderData['total_fee'] = $prepay['total_fee'];
                    $orderData['product_id'] = $prepay['product_id'];
                    $orderData['detail'] = $prepay['detail'];

                    $notify = Notify::firstOrCreate(['number'=>$result['out_trade_no']]); 
                    $notify -> update(['notify_url'=>$prepay['notify_url'],'redirect_url'=>$prepay['redirect_url']]);
                }
                $order->update($orderData);
                $prepay && $prepay->delete();
            }
        }
    }

?> 