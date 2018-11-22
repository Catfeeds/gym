<?php

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use think\File;
use app\index\model\User_course;

class User extends Controller
{
    private $uid = null;

    function __construct()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
    }

    /**
     * 获取用户的课程列表
     *
     * @return void
     */
    public function getCourse()
    {
        $pageNum = intval(request()->param('pageNum'));
        $uid = intval(request()->param('uid'));
        $courseList = getUserCourse($uid, $pageNum);
        $courseList = empty($courseList) ? [] : $courseList;
        return objReturn(0, 'success', $courseList);
    }

    /**
     * 用户提交反馈
     *
     * @return void
     */
    public function submitFeedback()
    {
        // 判断是否有文件(图片)上传
        $file = request()->file('file');
        if ($file) {
            $targetDir = ROOT_PATH . 'public' . DS . 'static' . DS . 'feedback';
            $save = $file->move($targetDir);
            if (!$save) return objReturn(400, 'System Error', $save);
        }

        // 数据上传
        $feedback['content'] = htmlspecialchars(request()->param('message'));
        $feedback['uid'] = request()->param('uid');
        $feedback['created_at'] = time();
        $feedback['img'] = $file ? DS . 'feedback' . DS . $save->getSaveName() : '';
        $feedback['user_type'] = request()->param('userType');

        $insert = Db::name('feedback')->insert($feedback);
        if (!$insert) return objReturn(400, 'Insert Failed', $insert);
        return objReturn(0, 'Success', $insert);
    }

    /**
     *  获取用户的反馈信息
     *
     * @return void
     */
    public function getUserFeedback()
    {
        $pageNum = intval(request()->param('pageNum'));
        $uid = intval(request()->param('uid'));
        $userType = request()->param('userType');
        $feedbackList = getFeedBack($uid, $userType, $pageNum);
        if ($feedbackList) {
            foreach ($feedbackList as &$info) {
                if (!empty($info['img'])) $info['img'] = config('SITEROOT') . '/static' . $info['img'];
            }
        }
        return objReturn(0, 'success', $feedbackList);
    }

    /**
     * 获取指定用户信息
     *
     * @return void
     */
    public function getClockInfo()
    {
        $uid = intval(request()->param('uid'));
        $userType = intval(request()->param('userType'));
        $userClockInfo = getUserClockInfo($uid, $userType);
        $res['clockInfo'] = $userClockInfo;
        return objReturn(0, 'success', $res);
    }

    /**
     * 获取用户信息
     * @param array userInfo
     * @param int $uid 用户uid
     * @return json 是否插入成功成功
     */
    public function setUserInfo()
    {
        $openid = request()->param('openid');
        if (empty($openid)) return objReturn(400, 'Invaild Param');
        $userInfo = request()->param('userInfo/a');
 
        // 数据构造
        $user['user_nickname'] = $userInfo['nickName'];
        $user['user_avatar_url'] = $userInfo['avatarUrl'];
        $user['user_city'] = $userInfo['city'];
        $user['user_province'] = $userInfo['province'];
        $user['user_language'] = $userInfo['language'];
        $user['user_country'] = $userInfo['country'];
        $user['update_at'] = time();
        $user['auth_at'] = time();
        $user['status'] = 2;
        $user['auth_name'] = htmlspecialchars(request()->param('authname'));
        $user['openid'] = $openid;

        $update = Db::name('user')->where('user_mobile', request()->param('mobile'))->update($user);

        if (!$update) return objReturn(403, 'failed', $update);
        return objReturn(0, 'success');
    }

    /**
     * 获取用户信息
     * 这里需要用户进行信息查看 所以要区分用户TYPE
     *
     * @return void
     */
    public function getUserInfo()
    {
        $uid = intval(request()->param('uid'));
        if (empty($uid)) return objReturn(400, 'Invaild Param');
        $userType = request()->param('userType');
        $userInfo = getUserInfoById($uid, false, false);
        return objReturn(0, 'success', $userInfo);
    }

    /**
     * 获取用户的打卡记录
     *
     * @return void
     */
    public function getUserClock()
    {
        $uid = intval(request()->param('uid'));
        if (empty($uid)) return objReturn(400, 'Invaild Param');

        $pageNum = intval(request()->param('pageNum'));

        $clockList = getClockList($uid, null, $pageNum);

        return objReturn(0, 'success', $clockList);
    }

    /**
     * 获取用户课程相关信息
     *
     * @return void
     */
    public function getCourseInfo()
    {
        $uid = intval(request()->param('uid'));
        if (empty($uid)) return objReturn(400, 'Invaild Param');

        $classList = getUserCourseList($uid);

        if (!$classList) return objReturn(400, 'No Course');
        return objReturn(0, 'success', $classList);
    }

    /**
     * 用户手机号修改
     *
     * @return void
     */
    public function changePhone()
    {
        $uid = intval(request()->param('uid'));
        if (empty($uid)) {
            return objReturn(400, 'Invaild Param');
        }

        $mobile = request()->param('mobile');
        $userType = request()->param('usertype');
        // 1 会员 2 教练
        $update = Db::name('user')->where('user_type', $userType)->where('uid', $uid)->update(['user_mobile' => $mobile]);
        if ($update) return objReturn(0, 'success');
        return objReturn(400, 'failed');
    }

}