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
use app\index\model\User as userDB;

class User extends Controller
{
    /**
     * 会员界面展示
     *
     * @return html
     */
    public function memberlist()
    {
        $user = new userDB;
        $memberList = $user->where('user_type', 1)->field('uid, user_name, user_gender, user_nickname, user_avatar_url, user_city, user_province, user_country, user_mobile, user_birth, auth_name, created_at, auth_at, status')->where('status', '<>', 3)->select();
        if ($memberList) {
            $usermemberListList = collection($memberList)->toArray();
        } else {
            $memberList = [];
        }
        $this->assign('memberList', $memberList);
        return $this->fetch();
    }

    /**
     * 新增会员界面
     *
     * @return html
     */
    public function memberadd()
    {
        return $this->fetch();
    }

    /**
     * 新增会员
     *
     * @return void
     */
    public function addMember()
    {
        $user = new userDB;
        $mobile = request()->param('mobile');
        // 手机号不能重复
        $isMobileExist = $user->where('user_mobile', $mobile)->value('uid');
        if ($isMobileExist) {
            return objReturn(401, '当前手机号已存在');
        }
        $member['user_name'] = htmlspecialchars(request()->param('name'));
        $member['user_mobile'] = $mobile;
        $member['user_gender'] = request()->param('gender');
        $member['user_birth'] = request()->param('birth');
        $member['user_type'] = 1;
        $member['created_at'] = time();

        $insert = $user->save($member);
        if ($insert) {
            return objReturn(0, '会员添加成功');
        } else {
            return objReturn(400, '会员添加失败');
        }
    }

    /**
     * 删除会员信息
     *
     * @return void
     */
    public function delMember()
    {
        $uid = request()->param('uid');
        if (empty($uid)) {
            return objReturn(403, '参数错误');
        }
        $update = Db::name('user')->where('uid', $uid)->update(['status' => 3, 'update_at' => time(), 'update_by' => Session::get('adminId')]);
        if ($update) {
            return objReturn(0, '会员删除成功');
        } else {
            return objReturn(400, '会员删除失败');
        }
    }

    /**
     * 修改会员信息
     *
     * @return html
     */
    public function memberedit()
    {
        $uid = request()->param('uid');
        $memInfo = Db::name('user')->where('uid', $uid)->field('user_name, user_gender, user_mobile, user_birth')->find();
        $this->assign('memInfo', $memInfo);
        $this->assign('uid', $uid);
        return $this->fetch();
    }

    /**
     * 更新会员信息
     *
     * @return void
     */
    public function updateMember()
    {
        $uid = request()->param('uid');
        $member['user_name'] = htmlspecialchars(request()->param('name'));
        $member['user_mobile'] = request()->param('mobile');
        $member['user_gender'] = request()->param('gender');
        $member['user_birth'] = request()->param('birth');
        $member['user_type'] = 1;
        $member['update_at'] = time();
        $member['update_by'] = Session::get('adminId');
        $update = Db::name('user')->where('uid', $uid)->update($member);
        if ($update) {
            return objReturn(0, '会员信息更新成功');
        } else {
            return objReturn(400, '会员信息更新失败');
        }
    }

    /**
     * 会员课程详情界面
     *
     * @return html
     */
    public function membercourse()
    {
        $uid = request()->param('uid');
        $userName = Db::name('user')->where('uid', $uid)->value('user_name');
        // 获取用户课程
        $userCourseList = getUserCourse($uid, false);
        // 获取所有课程
        $courseList = getCourse(false, null);
        $this->assign('courseList', $courseList ? $courseList : []);
        $this->assign('userCourseList', $userCourseList ? $userCourseList : []);
        $this->assign('userName', $userName);
        $this->assign('uid', $uid);
        return $this->fetch();
    }

    /**
     * 删除用户课程
     *
     * @return void
     */
    public function delUserCourse()
    {
        $uid = request()->param('uid');
        $courseId = request()->param('courseId');
        $update = Db::name('user_course')->where('uid', $uid)->where('course_id', $courseId)->update(['status' => 4, 'update_at' => time(), 'update_by' => Session::get('adminId')]);
        if ($update) {
            return objReturn(0, '会员课程删除成功');
        } else {
            return objReturn(400, '会员课程删除失败');
        }
    }

    public function addMemberCourse()
    {
        $courseId = request()->param('courseId');
        $uid = request()->param('uid');
        // 判断当前用户是否有该课程
        $isUserCourseExist = Db::name('user_course')->where('uid', $uid)->where('course_id', $courseId)->count();
        if ($isUserCourseExist) {
            return objReturn(405, '当前用户已拥有当前课程');
        }
        $memCourse['course_id'] = $courseId;
        $memCourse['uid'] = $uid;
        $memCourse['course_left_times'] = intval(request()->param('courseTimes'));
        $memCourse['start_at'] = strtotime(request()->param('courseStart'));
        $memCourse['end_at'] = strtotime(request()->param('courseEnd'));
        if ($memCourse['end_at'] == $memCourse['start_at']) {
            $memCourse['end_at'] = $memCourse['start_at'] + 86399;
        }
        $insert = Db::name('user_course')->insert($memCourse);
        if ($insert) {
            return objReturn(0, '会员课程增加成功');
        } else {
            return objReturn(400, '会员课程增加失败');
        }
    }

    /**
     * 会员打卡记录界面
     *
     * @return html
     */
    public function memberclock()
    {
        $uid = request()->param('uid');
        $memClockInfo = getUserClockInfo($uid, 1);
        $memClockList = getClockList($uid, 1);
        $userName = Db::name('user')->where('uid', $uid)->value('user_name');
        $this->assign('uid', $uid);
        $this->assign('userName', $userName);
        $this->assign('memClockInfo', $memClockInfo);
        $this->assign('memClockList', $memClockList);
        return $this->fetch();
    }

    /**
     * 删除会员打卡记录
     *
     * @return void
     */
    public function delMemClock()
    {
        $uid = request()->param('uid');
        $clockId = request()->param('clockId');
        $update = Db::name('clock')->where('uid', $uid)->update(['status' => 3, 'update_at' => time(), 'update_by' => Session::get('adminId')]);
        if ($update) {
            return objReturn(0, '会员打卡记录删除成功');
        } else {
            return objReturn(400, '会员打卡记录删除失败');
        }
    }

    /***************************/
    /********  教练相关  ********/
    /***************************/

    /**
     * 教练列表展示
     *
     * @return html
     */
    public function coachlist()
    {
        $user = new userDB;
        $coachList = $user->where('user_type', 2)->field('uid, user_name, user_gender, user_nickname, user_avatar_url, user_city, user_province, user_country, user_mobile, user_birth, auth_name, created_at, auth_at, status')->where('status', '<>', 3)->select();
        $this->assign('coachList', $coachList);
        return $this->fetch();
    }

    /**
     * 新增教练界面
     *
     * @return html
     */
    public function coachadd()
    {
        return $this->fetch();
    }

    /**
     * 新增教练
     *
     * @return void
     */
    public function addCoach()
    {
        $user = new userDB;
        $mobile = request()->param('mobile');
        // 手机号不能重复
        $isMobileExist = $user->where('user_mobile', $mobile)->value('uid');
        if ($isMobileExist) {
            return objReturn(401, '当前手机号已存在');
        }
        $member['user_name'] = htmlspecialchars(request()->param('name'));
        $member['user_mobile'] = $mobile;
        $member['user_gender'] = request()->param('gender');
        $member['user_birth'] = request()->param('birth');
        $member['user_type'] = 2;
        $member['created_at'] = time();

        $insert = $user->save($member);
        if ($insert) {
            return objReturn(0, '教练添加成功');
        } else {
            return objReturn(400, '教练添加失败');
        }
    }

    /**
     * 修改教练信息界面
     *
     * @return html
     */
    public function coachedit()
    {
        $uid = request()->param('uid');
        $coachInfo = Db::name('user')->where('uid', $uid)->field('user_name, user_gender, user_mobile, user_birth')->find();
        $this->assign('coachInfo', $coachInfo);
        $this->assign('uid', $uid);
        return $this->fetch();
    }

    /**
     * 修改教练信息
     *
     * @return void
     */
    public function updateCoach()
    {
        $uid = request()->param('uid');
        $member['user_name'] = htmlspecialchars(request()->param('name'));
        $member['user_mobile'] = request()->param('mobile');
        $member['user_gender'] = request()->param('gender');
        $member['user_birth'] = request()->param('birth');
        $member['user_type'] = 2;
        $member['update_at'] = time();
        $member['update_by'] = Session::get('adminId');
        $update = Db::name('user')->where('uid', $uid)->update($member);
        if ($update) {
            return objReturn(0, '教练信息更新成功');
        } else {
            return objReturn(400, '教练信息更新失败');
        }
    }

    /**
     * 会员打卡记录界面
     *
     * @return html
     */
    public function coachclock()
    {
        $uid = request()->param('uid');
        $coachClockInfo = getUserClockInfo($uid, 2);
        $coachClockList = getClockList($uid, 2);
        $userName = Db::name('user')->where('uid', $uid)->value('user_name');
        $this->assign('uid', $uid);
        $this->assign('userName', $userName);
        $this->assign('coachClockInfo', $coachClockInfo);
        $this->assign('coachClockList', $coachClockList);
        return $this->fetch();
    }

    /**
     * 删除会员打卡记录
     *
     * @return void
     */
    public function delCoachClock()
    {
        $uid = request()->param('uid');
        $clockId = request()->param('clockId');
        $update = Db::name('clock')->where('uid', $uid)->update(['status' => 3, 'update_at' => time(), 'update_by' => Session::get('adminId')]);
        if ($update) {
            return objReturn(0, '教练打卡记录删除成功');
        } else {
            return objReturn(400, '教练打卡记录删除失败');
        }
    }

}