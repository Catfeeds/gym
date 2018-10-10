<?php

/**
 * 小程序商城
 * @author Locked
 * createtime 2018-05-03
 */

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use think\Session;

use app\index\model\User;
use app\index\model\Banner;
use app\index\model\Course;
use app\index\model\Clause;

class Minibase extends Controller
{

    public function __construct()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
    }
    /**
     * 获取用户openID
     * 
     * @param string code 登陆时wx.login返回的code
     * @return json 用户openid
     */
    public function getUserOpenid($code)
    {
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . config('APPID') . "&secret=" . config('APPSECRET') . "&js_code=" . $code . "&grant_type=authorization_code";
        $info = file_get_contents($url);
        $info = json_decode($info);
        $info = get_object_vars($info);
        return $info['openid'];
    }

    /**
     * 获取用户信息
     *
     * @param Request $request
     * @return void
     */
    public function getUserAccount()
    {
        $openid = request()->param('openid');
        $code = request()->param('code');
        // 如果没有openid 则需要先获取openid
        if (empty($openid)) $openid = $this->getUserOpenid($code);
        
        // 根据openid 去后台数据库取值
        // 教师和用户是两张表
        $userInfo = Db::name('user')->where('openid', $openid)->field('uid, status, openid')->find();
        // 如果没有在用户表找到
        if (!$userInfo) $teacherInfo = Db::name('teacher')->where('openid', $openid)->field('teacher_id as uid, openid, status')->find();
        
        // 如果查到用户存在，但是用户已被删除
        if ($userInfo && $userInfo['status'] == 3) return objReturn(403, 'User Deleted');

        if (isset($teacherInfo) && $teacherInfo['status'] == 4) return objReturn(403, 'Teacher Deleted');

        // 如果未查询到用户 则该用户为新用户
        if (!$userInfo && !$teacherInfo) {
            $userInfo = [];
            $userInfo['openid'] = $openid;
            $userInfo['isAuth'] = false;
            return objReturn(0, 'New User', $userInfo);
        }
        
        // 有查到用户要判断用户认证状态
        if ($userInfo) {
            $userInfo['isAuth'] = $userInfo['status'] == 2 ? true : false;
        } else if (isset($teacherInfo)) {
            $userInfo['isAuth'] = $teacherInfo['status'] == 2 ? true : false;
        }

        // 设置用户身份
        $userInfo['userType'] = $userInfo ? 1 : 2;
        return objReturn(0, 'Get UserInfo Success', $userInfo ? $userInfo : $teacherInfo);
    }


    /**
     * 获取当前系统的用户协议
     *
     * @return void
     */
    public function getCaluse()
    {
        $uid = intval(request()->param('uid'));
        if (empty($uid)) return objReturn(400, 'Invaild Param');
        $clause = new Clause;
        $clauseInfo = $clause->where('idx', 1)->value('clause');
        $clauseInfo = htmlspecialchars_decode($clauseInfo);
        return objReturn(0, 'success', $clauseInfo);
    }

    /**
     * 获取门店详情
     *
     * @return void
     */
    public function getStoreInfo()
    {
        $openid = request()->param('openid');
        if (empty($openid)) return objReturn(400, 'Invaild Param');

        $storeInfo = Db::name('mini_setting')->where('setting_id', 1)->value('store_info');
        return objReturn(0, 'success', $storeInfo);
    }

    /**
     * 获取小程序首页详情
     *
     * @param Request $request
     * @return void
     */
    public function getIndex()
    {
        $uid = intval(request()->param('uid'));
        if (empty($uid)) return objReturn(400, 'Invaild Param');

        $banner = new Banner;
        $bannerList = getBanner(false);
        if (!$bannerList) return objReturn(0, 'No banner');
        return objReturn(0, 'success', $bannerList);
    }

    /**
     * 获取系统设置
     *
     * @return void
     */
    public function getSystemSetting()
    {
        $openid = request()->param('openid');
        if (empty($openid)) return objReturn(400, 'Invaild Param');
        $setting = Db::name('mini_setting')->where('setting_id', 1)->field('mini_name, share_text, service_phone, location')->find();
        return objReturn(0, 'success', $setting);
    }



}