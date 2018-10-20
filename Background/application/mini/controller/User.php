<?php

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use think\File;

class User extends Controller
{
    private $uid = null;

    function __construct()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
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
            $targetDir = ROOT_PATH . 'public' . DS . 'feedback';
            $save = $file->move($targetDir);
            if (!$save) return objReturn(400, 'System Error', $save);
        }

        // 数据上传
        $feedback['content'] = htmlspecialchars(request()->param('message'));
        $feedback['uid'] = request()->param('uid');
        $feedback['created_at'] = time();
        $feedback['img'] = $file ? DS . 'feedback' . DS . $save->getSaveName() : '';
        $feedback['user_type'] = request()->param('userType') ? 1 : 2;

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
        // 获取用户拥有的课程列表及剩余打卡课时
        $userCourse = getUserCourse($uid);
        $res['course'] = $userCourse;
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

        if (!$update) return objReturn(402, 'failed', $update);
        return objReturn(0, 'success', $update);
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

        $classList = getUserCourse($uid);

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
        if (empty($uid)) return objReturn(400, 'Invaild Param');

        $mobile = request()->param('mobile');
        $userType = request()->param('usertype');
        // 0 学生 1 老师
        if ($userType == 1) {
            $update = Db::name('teacher')->where('teacher_id', $uid)->update(['teacher_phone' => $mobile]);
        } else if ($userType == 0) {
            $update = Db::name('user')->where('uid', $uid)->update(['phone' => $mobile, 'update_at' => time()]);
        } else {
            return objReturn(400, 'Invaild Param');
        }

        if ($update) return objReturn(0, 'success');
        return objReturn(400, 'failed');
    }

}