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


class Clock extends Controller{

    public function getUserClock(Request $request){

        $pageNum = intval($request -> param('pageNum'));
        $uid = intval($request -> param('uid'));
        $clockList = getClockList($uid, null, $pageNum);
        $isHaveMore = true;
        if (count($clockList) < 10) {
            $isHaveMore = false;
        }
        $res['list'] = isset($clockList) ? $clockList : [];
        $res['isHaveMore'] = $isHaveMore;
        return objReturn(0, 'success', $res);
    }

    public function userClock(Request $request){
        $time = intval($request -> param('time'));
        $uid = intval($request -> param('uid'));
        $courseId = intval($request -> param('courseId'));
        $formId = $request -> param('formId');
        if (empty($time) || empty($uid) || empty($courseId)) {
            return objReturn(402, "Invaild Param");
        }
        if (time() - $time > 10) {
            return objReturn(401, 'NetWork Over Time');
        }
        $success = makeClock($uid, $courseId, $time);
        Db::name('formid') -> insert(['uid' => $uid, 'course_id' => $courseId, 'formid' => $formId, 'created_at' => $time]);
        if (empty($success)) {
            return objReturn(0, 'success');
        }else if($success == "Already Clocked"){
            return objReturn(403, 'failed', $success);
        }else {
            return objReturn(400, 'failed', $success);
        }
    }

    public function test(){
        $ordersn = "201807251026792";
        $prepayCache = Cache::get('prepayCache');
        // $cachedata = Cache::get('data');
        // $cachedata = unset($cachedata['cache_fee']);
        dump($prepayCache);die;
    }

}