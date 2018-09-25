<?php

/**
 * 发送短信
 * createtime 2018-05-07
 */
namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use think\Session;
use think\File;

class Sms extends Controller
{
    // 腾讯云短信验证相关
    const APPID = "1400138297";
    const APPKEY = "deb7a501a6e413a6a73afccd64e29123";
    const NUMBER_MIXED = 1;
    const NUMBER_PURE = 2;

    public function sendSingleSms()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $openid = request()->param('openid');
        if (empty($openid)) return objReturn(400, 'Invaild Param');
        $mobile = request()->param('telnum');
        // 手机号正则匹配
        if (!preg_match("/^1[3-9]\d{9}$/", $mobile)) return objReturn(402, 'Invaild Telnum', $mobile);

        // 去数据库查询是否有该手机号
        $isCheck = request()->param('check');

        if ($isCheck != 0) {
            $userType = intval(request()->param('usertype'));
            $isUserTelExist = Db::name('user')->where('phone', $mobile)->count();
            if ($isUserTelExist != 1) return objReturn(401, 'This Mobile NOT EXIST');
        }

        // 测试环境直接返回
        return objReturn(0, 'success', '123123');

        $random = rand(100000, 999999);
        $url = "https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid=" . self::APPID . "&random=" . $random;
        // 构造短信dataObj
        // 生成当前时间
        $curTime = time();
        // 生成验证码Code
        $code = $this->getRandomNum(6, self::NUMBER_PURE);
        $content = "尊敬的用户，您的短信验证码为" . $code;
        // 短信签名
        $dataObj = array();
        $dataObj['sig'] = hash("sha256", "appkey=" . self::APPKEY . "&random=" . $random . "&time=" . $curTime . "&mobile=" . $mobile, false);
        $dataObj['ext'] = "";
        $dataObj['extend'] = "";
        $dataObj['msg'] = "【吸铁石兄弟少儿美术中心】" . $content;
        $dataObj['tel'] = array("mobile" => $mobile, "nationcode" => "86");
        $dataObj['time'] = intval($curTime);
        $dataObj['type'] = 0; 
        // 使用curl来发送短信
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataObj));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec($curl);
        $res['code'] = "200";
        if (false == $ret) {
            // curl_exec failed
            curl_close($curl);
            return objReturn(400, 'failed', curl_error());
        } else {
            $rsp = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                curl_close($curl);
                return objReturn(400, 'failed', curl_error());
            }
        }
        curl_close($curl);
        
        // 发送成功 将数据插入数据库
        $smsLog['openid'] = $openid;
        $smsLog['mobile'] = $mobile;
        $smsLog['created_at'] = time();
        $smsLog['code'] = $code;
        Db::name('sms_log')->insert($smsLog);

        return objReturn(0, 'success', $code);
    }
    /**
     * 获取指定类型和长度的随机数
     *
     * @param int $length
     * @param int $type
     * @return int 随机数
     */
    public function getRandomNum($length, $type)
    {
        if ($type == self::NUMBER_MIXED) {
            $pool = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        } else if ($type == self::NUMBER_PURE) {
            $pool = "0123456789";
        }
        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }
}