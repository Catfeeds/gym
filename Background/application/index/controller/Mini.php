<?php

/**
 * 方特小程序微信数据相关
 * createtime 2018-05-03
 */

namespace app\index\controller;

use \think\Controller;
use \think\Request;
use \think\Cache;
use \think\Db;
use \think\Session;

use app\index\model\Usercount;
use app\index\model\Userinfo;
use app\index\model\Invite_code;
use app\index\model\System_setting;

class Mini extends Controller
{

    // 小程序APPID
    const APPID = "wx57beee95d7c48bbe";
    // 小程序APPSECRET
    const APPSECRET = "774e5f55826cce1d828ab7faf14c3e09";
    const DS = DIRECTORY_SEPARATOR;

    /**
     * 获取用户信息
     * @param array userInfo
     * @param string openid
     * @return json 是否插入成功成功
     */
    public function setUserInfo(Request $request)
    {
        $userOpenid = $request->param('openid');
    	// 有一个Openid 的缓存array，如果已经将该用户数据插入过，在缓存中就会体现
    	// 判断缓存库中是否有该openid
    	// 获取用户信息并入库
        $userinfo = new Userinfo;
        $userInfo = $request->param('userInfo/a');
        $userInfoBack = $userInfo;
        $userInfo['nickname'] = $userInfo['nickName'];
        $userInfo['avatar_url'] = $userInfo['avatarUrl'];
        // $userInfo['language'] = $userInfo['zh_Cn'];
        unset($userInfo['nickName']);
        unset($userInfo['avatarUrl']);

        $insert = $userinfo->where('user_openid', $userOpenid)->update($userInfo);

        $userInfo['nickName'] = $userInfo['nickname'];
        $userInfo['avatarUrl'] = $userInfo['avatar_url'];
        unset($userInfo['nickname']);
        unset($userInfo['avatar_url']);

        if ($insert) {
            // 更新用户信息到缓存
            $userAccountInfo = Cache::get('userAccountInfo');
            foreach ($userAccountInfo as $k => $v) {
                if ($v['user_openid'] == $userOpenid) {
                    $userAccountInfo[$k]['userInfo'] = $userInfo;
                    break 1;
                }
            }
            Cache::set('userAccountInfo', $userAccountInfo, 0);
            $res['code'] = "200";
            $res['msg'] = "Info Insert Success";
        } else {
            $res['code'] = "300";
            $res['msg'] = "Info Insert Falied";
        }
        return json_encode($res);
    }

    /**
     * 获取用户openID
     * @param string code
     * @return json 用户openid
     */
    public function getUserOpenid($code)
    {
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . self::APPID . "&secret=" . self::APPSECRET . "&js_code=" . $code . "&grant_type=authorization_code";
        $info = file_get_contents($url);
        $info = json_decode($info);
        $info = get_object_vars($info);
        $res = array();
        $res['openid'] = $info['openid'];

        // 判断当前用户是否在数据库中
        // 防止用户删除小程序之后重获取导致的数据不匹配
        $userAccountInfo = Cache::get('userAccountInfo');
        if ($userAccountInfo && sizeof($userAccountInfo) > 0) {
            foreach ($userAccountInfo as $k => $v) {
                if ($v['user_openid'] == $res['openid']) {
                    return $v;
                }
            }
        }

        // 每个账号的登录态有效期为3天
        // $res['expire_time'] = time() + 259200;
        // 将用户信息入库，记录用户进入小程序信息
        // $usercount = new Usercount;
        // $usercount -> insert(['user_openid' => $res['openid'], 'create_time' => date('Y-m-d H:i:s', time())]);
        // 将用户信息入库
        // $userinfo = new Userinfo;
        $userID = Db::name('userinfo')->insertGetId(['user_openid' => $res['openid'], 'create_time' => time()]);

        // 将新用户的信息放入缓存
        $userAccountInfo = Cache::get('userAccountInfo');
        if (!$userAccountInfo) {
            $userAccountInfo = array();
        }
        $currentUser = array();
        $currentUser['user_openid'] = $res['openid'];
        $currentUser['isAuth'] = false;
        $currentUser['isAdmin'] = false;
        $currentUser['userInfo'] = null;
        $currentUser['rebate'] = 0;         //用户的返佣
        $currentUser['inviteCode'] = null;     //用户使用的邀请码
        $currentUser['userID'] = $userID;     //用户ID
        $currentUser['userName'] = null;     //用户实名认证的名字
        $userAccountInfo[] = $currentUser;

        Cache::set('userAccountInfo', $userAccountInfo, 0);

        return $currentUser;
    }

    /**
     * 将用户登陆信息插入数据库中
     *
     * @param Request $request
     * @return void
     */
    public function setUserCount(Request $request)
    {
        $usercount = new Usercount;
        $logs = $request->param('logs/a');
        $logArr = array();
        arsort($logs);
        foreach ($logs as $k => $v) {
            $array['create_time'] = date('Y-m-d H:i:s', intval($v / 1000));
            $array['user_openid'] = $request->param('openid');
            $logArr[] = $array;
        }
        $usercount->saveAll($logArr);
    }

    /**
     * 判断该用户是否为管理员
     * AccountType 0 用户 1 票券核销员 2 系统管理员 3 总管理员
     *
     * @param Request $request
     * @return void
     */
    public function getUserAccountState(Request $request)
    {
        $userOpenid = $request->param('openid');
        $code = $request->param('code');
        if (empty($userOpenid)) {
            $userInfo = $this->getUserOpenid($code);
            $userInfo['rebate'] = number_format($userInfo['rebate'], 2);
            $res['data'] = $userInfo;
            $res['code'] = 200;
            $res['msg'] = "success";
        } else {
            // 用户信息缓存
            $userAccountInfo = Cache::get('userAccountInfo');
            if ($userAccountInfo) {
                foreach ($userAccountInfo as $k => $v) {
                    if ($v['user_openid'] == $userOpenid) {
                        // 判断用户是否实名认证
                        $v['rebate'] = number_format($v['rebate'], 2);
                        $res['data'] = $v;
                        $res['code'] = "200";
                        break 1;
                    }
                }
            }
        }
        // 管理员信息缓存
        // $adminAccountInfo = Cache::get('adminAccountInfo');
        
        // if (!$adminAccountInfo && !$userAccountInfo) {
        //     $res['code'] = "400";
        //     $res['message'] = "No Info Exist";
        //     return json_encode($res);
        // }
        // $res = null;
        // if ($adminAccountInfo) {
        //     foreach ($adminAccountInfo as $k => $v) {
        //         if ($v['user_openid'] == $userOpenid) {
        //             $res = $v;
        //             $res['isAdmin'] = true;
        //             $res['accountType'] = $v['accountType'];
        //             $res['code'] = "200";
        //             break 1;
        //         }
        //     }
        // }

        return json_encode($res);
    }

    /**
     * 获取系统设置
     *
     * @return void
     */
    public function getSystemSetting()
    {
        // 3 获取系统设置
        $system_setting = new System_setting;
        $systemSetting = $system_setting->where('idx', 1)->field('mini_name, mini_color, logi_fee, logi_free_fee, user_rebate_min, share_text')->select();
        $systemSetting = collection($systemSetting)->toArray();

        $res['setting'] = $systemSetting[0];
        $res['msg'] = 'success';
        $res['code'] = "200";

        return json_encode($res);
    }

    public function test()
    {
        // dump(Cache::get('prepayCache
        // Cache::rm('accessToken');
        // $inviteCodeArr = Db::name('invite_code')->field('invite_code, code_id')->select();

        // $updateArr = [];
        $userInfo = Cache::get('userAccountInfo');
        // $userinfo = new Userinfo;
        foreach ($userInfo as $k => $v) {
            if (isset($v['nickname'])) {
                $userInfo[$k]['nickName'] = $v['nickname'];
                unset($v['nickname']);
            }
            if (isset($v['avatar_url'])) {
                $userInfo[$k]['avatarUrl'] = $v['avatar_url'];
                unset($v['avatar_url']);
            }
        }
        // dump($updateArr);
        // $updateArr = array_flip($updateArr);
        // $updateArr = array_flip($updateArr);
        // dump($updateArr);
        // Db::name('userinfo') -> -> insertAll($updateArr);
        
        // $saveAll = $userinfo->isUpdate(false)->saveAll($updateArr);
        // dump($saveAll);
        Cache::set('userAccountInfo', $userInfo, 0);
        // dump($updateArr);
        // $inviteCodeArr = Cache::get('inviteCodeArr');
        // dump($inviteCodeArr);
    }

    /**
     * 用户注册验证登陆
     *
     * @param Request $request
     * @return void
     */
    public function checkInviteCode(Request $request)
    {
        $inviteCodeArr = Cache::get('inviteCodeArr');
        if (!$inviteCodeArr) {
            $invite_code = new Invite_code;
            $inviteCodeArr = $invite_code->where('is_active', 1)->field('code_id, invite_code, code_active_num, code_total_num')->select();
            if (!$inviteCodeArr || count($inviteCodeArr) == 0) {
                $res['code'] = "201";
                return json_encode($res);
            }
            $inviteCodeArr = collection($inviteCodeArr)->toArray();
        }
        
        // 接收用户传递过来的数据
        $inviteCode = htmlspecialchars($request->param('code'));
        $userOpenid = $request->param('openid');

        $isCheckSuccess = false;
        $isCanUpdate = false;
        foreach ($inviteCodeArr as $k => $v) {
            if ($v['invite_code'] == $inviteCode && $v['code_active_num'] < $v['code_total_num']) {
                $isCheckSuccess = true;
                $inviteCodeArr[$k]['code_active_num']++;
                // 更新用户邀请码信息
                $userinfo = new Userinfo;
                $userinfo->where('user_openid', $userOpenid)->update(['invite_code_id' => $v['code_id'], 'invite_check_time' => time()]);
                if ($v['code_active_num'] == $v['code_total_num']) {
                    $isCanUpdate = true;
                }
            }
        }
        if ($isCheckSuccess) {
            $res['code'] = "200";
            $res['msg'] = "success";

            $userAccountInfo = Cache::get('userAccountInfo');
            foreach ($userAccountInfo as $k => $v) {
                if ($v['user_openid'] == $userOpenid) {
                    $userAccountInfo[$k]['inviteCode'] = $inviteCode;
                    break 1;
                }
            }
            // dump($userAccountInfo);die;
            Cache::set('userAccountInfo', $userAccountInfo, 0);
        } else {
            $res['code'] = "201";
            $res['msg'] = "success";
        }
        if ($isCanUpdate) {
            $save = $invite_code->saveAll($inviteCodeArr);
            if ($save) {
                Cache::rm('inviteCodeArr');
            }
        } else {
            Cache::set('inviteCodeArr', $inviteCodeArr, 0);
        }
        return json_encode($res);

    }

}