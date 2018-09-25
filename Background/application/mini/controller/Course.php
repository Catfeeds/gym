<?php

/**
 * 吸铁石美术小程序 课程有关方法
 * @author Locked
 * createtime 2018-05-03
 */

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;

use app\index\model\Course_user;
use app\index\model\Course_change;


class Course extends Controller{

    public function getCourseList(Request $request){

        $pageNum = intval($request -> param('pageNum'));
        $uid = intval($request -> param('uid'));

        $courseList = getAllCourse(null, false, $pageNum);
        $isHaveMore = true;
        
        if (!$courseList || count($courseList) == 0) {
            $isHaveMore = false;
            $courseList = [];
        }else if (count($courseList) < 10) {
            $isHaveMore = false;
        }

        $res['isHaveMore'] = $isHaveMore;
        $res['list'] = $courseList;

        return objReturn(0, 'success', $res);
        
    }

    public function getCourseInfo(Request $request){
        $uid = intval($request -> param('uid'));
        $courseId = intval($request -> param('courseId'));
        $courseInfo = getCourseById($courseId, null, true);
        $courseInfo['isPay'] = false;
        // 判断当前用户是否已购买此课程
        if (isset($uid)) {
            $course_user = new Course_user;
            $isCurUserAddCourse =  $course_user -> where('course_id', $courseId) -> where('uid', $uid) -> count();
            if ($isCurUserAddCourse == 1) {
                $courseInfo['isPay'] = true;
            }
        }
        return objReturn(0, 'success', $courseInfo);
    }

    public function getUserCourse(Request $request){
        $pageNum = intval($request -> param('pageNum'));
        $uid = intval($request -> param('uid'));
        $course_user = new Course_user;
        $userCourse = $course_user -> where('uid', $uid) -> select();
        if (!$userCourse || count($userCourse) == 0) {
            return objReturn(400, "failed", $userCourse);
        }
        $allCourse = getAllCourse(null, true);
        if (!$allCourse || count($allCourse) == 0) {
            return objReturn(401, 'failed', $allCourse);
        }
        $allCourse = collection($allCourse) -> toArray();
        $userCourse = collection($userCourse) -> toArray();
        $res = [];
        foreach ($userCourse as $k => $v) {
            foreach ($allCourse as $ke => $va) {
                if ($v['course_id'] == $va['course_id']) {
                    $va['in_time'] = date('Y-m-d H:i', $v['created_at']);
                    $res['list'] []= $va;
                    break 1;
                }
            }
        }
        return objReturn(0, 'success', $res);
    }

    /**
     * 获取用户调课记录
     *
     * @param Request $request
     * @return void
     */
    public function getUserCourseChange(Request $request){
        $uid = intval($request -> param('uid'));
        $pageNum = intval($request -> param('pageNum'));
        $course_change = new Course_change;
        $courseChangeList = $course_change -> alias('a') -> join('art_course c', 'a.course_id = c.course_id', 'LEFT') -> field('a.idx, a.uid, a.course_id, a.ori_course_at, a.new_course_at, a.created_at, c.course_name, a.reason') -> where('a.uid', $uid) -> limit($pageNum * 10, 10) -> select();
        $isHaveMore = false;
        if ($courseChangeList && count($courseChangeList) > 0) {
            $courseChangeList = collection($courseChangeList) -> toArray();
            if (count($courseChangeList) > 10) {
                $isHaveMore = true;
            }
            // 简单处理时间
            foreach ($courseChangeList as &$info) {
                $info['ori_course_at'] = date('Y-m-d H:i:s', $info['ori_course_at']);
                $info['new_course_at'] = date('Y-m-d H:i:s', $info['new_course_at']);
                $info['created_at'] = date('Y-m-d H:i:s', $info['created_at']);
            }
        }
        $res['isHaveMore'] = $isHaveMore;
        $res['list'] = isset($courseChangeList) ? $courseChangeList : [];
        return objReturn(0, 'success', $res);
    }

    /**
     * 获取用户可打卡的课程
     *
     * @param Request $request
     * @return void
     */
    public function getNeedClockList(Request $request){
        $uid = intval($request -> param('uid'));
        $curTime = time();
        // 先查询购买的未结束课程
        $course_user = new Course_user;
        $notExpireCourseList = $course_user -> where('uid', $uid) -> field('course_id') -> select();
        if (!$notExpireCourseList || count($notExpireCourseList) == 0) {
            return objReturn(201, 'no Course', $notExpireCourseList);
        }
        $notExpireCourseList = collection($notExpireCourseList) -> toArray();
        // 简单数据处理
        $today = date('Y-m-d', time());
        foreach ($notExpireCourseList as $k => $v) {
            $courseInfo = getUserCourseDays($uid, $v['course_id']);
            $notExpireCourseList[$k] = array_merge($courseInfo, $notExpireCourseList[$k]);
            // 判断当天是否可以打卡
            $notExpireCourseList[$k]['isCanClock'] = in_array($today, $notExpireCourseList[$k]['course_day']);
            $notExpireCourseList[$k]['isClock'] = false;
            if (isset($notExpireCourseList[$k]['clock_day'])) {
                $notExpireCourseList[$k]['isClock'] = in_array($today, $notExpireCourseList[$k]['clock_day']);
            }
            $notExpireCourseList[$k]['start_at_conv'] = date('H:i', $notExpireCourseList[$k]['start_at']);
            $notExpireCourseList[$k]['end_at_conv'] = date('H:i', $notExpireCourseList[$k]['end_at']);
        }
        $res['today'] = strtotime("today");
        $res['list'] = $notExpireCourseList;
        return objReturn(0, 'success', $res);
    }

}