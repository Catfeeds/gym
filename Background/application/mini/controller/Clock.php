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
        $clockList = getClockList($uid, $pageNum);
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
        $location = request()->param('location');
        $today = strtotime('today');
        // 判断教练今天是否已经打过卡
        $todayClock = Db::name('coach_clock')->where('uid', $uid)->where('clock_start_at', 'between', [$today, $today + 86399])->count();
        if ($todayClock > 0) {
            return objReturn(405, 'today already clock');
        }
        // 判断当前是否到了教练上班打卡的时间
        $wordTime = Db::name('mini_setting')->where('setting_id', 1)->field('coach_work_start_at, coach_work_end_at')->find();
        $workStartAt = $today + $wordTime['coach_work_start_at'];
        $wordEndAt = $today + $wordTime['coach_work_end_at'];
        if ($curTime < $workStartAt || $curTime > $wordEndAt) {
            return objReturn(402, '未到上班时间');
        }

        $success = makeClock($uid, $curTime, $location);
        if ($success) {
            return objReturn(0, 'success');
        }
        return objReturn(400, 'failed');
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
        // 判断当前是否是下班时间
        $workEndAt = Db::name('mini_setting')->where('setting_id', 1)->value('coach_work_end_at');
        $today = strtotime('today');
        $workEndAt = $workEndAt + $today;
        if ($curTime < $workEndAt) {
            return objReturn(402, '未到下班时间');
        }
        $uid = intval(request()->param('uid'));
        $clockId = intval(request()->param('clockId'));
        $location = request()->param('location');
        $success = endClock($clockId, $timeStamp, $location);
        if ($success) {
            return objReturn(0, 'success');
        }
        return objReturn(400, 'failed');
    }

}