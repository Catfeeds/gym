<?php

/**
 * 吸铁石美术小程序 打卡有关方法
 * @author Locked
 * createtime 2018-05-03
 */

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;


class Clock extends Controller
{

    public function getClocklist()
    {
        $pageNum = intval(request()->param('pageNum'));
        $userType = intval(request()->param('userType'));
        $uid = intval(request()->param('uid'));
        $clockList = getClockList($uid, $userType, $pageNum);
        $clockList = !empty($clockList) ? $clockList : [];
        return objReturn(0, 'success', $clockList);
    }

    public function startClock()
    {
        $timeStamp = intval(request()->param('timeStamp'));
        $curTime = time();
        // 如果timeStamp时间与服务器时间相差5s 则不允许打卡
        if ($timeStamp - $curTime > 5) {
            return objReturn(401, 'clock Overtime');
        }
        $uid = intval(request()->param('uid'));
        $userType = intval(request()->param('userType'));
        $courseId = intval(request()->param('courseId'));
        $location = request()->param('location');
        // $formId = request()->param('formId');
        $success = makeClock($uid, $userType, $courseId, $curTime, $location);
        // Db::name('formid')->insert(['uid' => $uid, 'course_id' => $courseId, 'formid' => $formId, 'created_at' => $time]);
        if ($success) {
            return objReturn(0, 'success');
        } else {
            return objReturn(400, 'failed');
        }
    }

    /**
     * 用户结束打卡
     *
     * @return void
     */
    public function finishClock()
    {
        $timeStamp = intval(request()->param('timeStamp'));
        $curTime = time();
        // 如果timeStamp时间与服务器时间相差5s 则不允许打卡
        if ($timeStamp - $curTime > 5) {
            return objReturn(401, 'clock Overtime');
        }
        $uid = intval(request()->param('uid'));
        $clockId = intval(request()->param('clockId'));
        $location = request()->param('location');

        $success = endClock($clockId, $timeStamp, $location);

        if ($success) {
            return objReturn(0, 'success');
        } else {
            return objReturn(400, 'failed');
        }
    }

}