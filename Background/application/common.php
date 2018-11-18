<?php

use \think\Request;
use \think\Cache;
use think\Db;

use app\index\model\User;
use app\index\model\Banner;
use app\index\model\Feedback;
use app\index\model\Admin;
use app\index\model\Coach;
use app\index\model\Clock;
use app\index\model\Clock_count;
use app\index\model\User_course;
use app\index\model\Project;
use app\index\model\Course;

/**
 * 构造返回数据
 *
 * @param int $code 返回码
 * @param string $msg 返回信息
 * @param array $data 返回的数据
 * @return json $data
 */
function objReturn($code, $msg, $data = null)
{
    if (!is_int($code) || !is_string($msg)) return 'Invaild Param';
    $res['code'] = $code;
    $res['msg'] = $msg;
    if ($data) $res['data'] = $data;
    return json($res);
}

/**
 * 获取banner信息
 *
 * @param boolean $isAll 是否需要查询全部的banner
 * @return void
 */
function getBanner($isAll = true)
{
    $field = 'banner_id, banner_img, nav_type, nav_id, status, sort';
    $status = $isAll ? [1, 2] : [1];
    $banner = new Banner;
    $bannerList = $banner->where('status', 'in', $status)->field($field)->order('created_at desc')->select();
    if (!$bannerList || count($bannerList) == 0) return null;
    $bannerList = collection($bannerList)->toArray();

    return $bannerList;
}

/**
 * 获取项目
 *
 * @param string $field 需要查询的字段
 * @param boolean $isAll 是否需要查询所有项目
 * @param int $pageNum 需要获取的页码
 * @return void
 */
function getProject($field = null, $isAll = false, $pageNum = null)
{
    $field = $field ? $field : 'project_id, project_name, project_img';
    $status = $isAll ? [1, 2] : [1];
    $limit = isset($pageNum) ? $pageNum . ', 10' : '';
    $project = new Project;
    $projectList = $project->where('status', 'in', $status)->field($field)->order('sort desc')->limit($limit)->select();
    if (!$projectList || count($projectList) == 0) {
        return null;
    }
    $projectList = collection($projectList)->toArray();

    return $projectList;
}

/**
 * 获取指定的项目
 *
 * @param int $projectId 项目ID
 * @param boolean $isVaild 当前项目是否正常展示
 * @return void
 */
function getProjectById($projectId, $field = null, $isVaild = true)
{
    $field = $field ? $field : "project_id, project_name, project_img, project_video, project_desc, created_at, status, sort";
    $status = $isVaild ? [1] : [1, 2];
    $project = new Project;
    $projectInfo = $project->where('status', 'in', $status)->field($field)->select();
    if (!$projectInfo || count($projectInfo) == 0) {
        return null;
    }
    $projectInfo = collection($projectInfo)->toArray();
    $projectInfo = $projectInfo[0];
    if (!empty($projectInfo['project_video'])) {
        $projectInfo['project_video'] = config('SITEROOT') . $projectInfo['project_video'];
    }
    $projectInfo['project_img'] = config('SITEROOT') . $projectInfo['project_img'];
    // 处理desc
    if (!empty($projectInfo['project_desc'])) {
        $descTemp = $projectInfo['project_desc'];
        $descTempArr = explode(',', $aboutUs['about_us']);
        $descSort = [];
        $descArr = [];
        foreach ($descTempArr as $k => $v) {
            $temp = explode(':', $v);
            $descArr[] = $v[0];
            $descSort[] = $v[1];
        }
        if (count($descSort) > 1) {
            array_multisort($descSort, SORT_ASC, SORT_NUMERIC, $descArr);
        }
        $projectInfo['project_desc'] = $descArr;
    }
    return $projectInfo;
}

/**
 * 获取打卡列表
 * 或通过uid获取用户的所有打卡记录
 *
 * @param int $uid 用户ID
 * @param int $userType 用户类别 1 会员 2 教练
 * @param int $status 打卡记录的状态 1 正常 2 删除
 * @return void
 */
function getClockList($uid, $pageNum = null)
{
    $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : '';
    $clockList = Db::name('coach_clock')->where('uid', $uid)->field('clock_id, uid, clock_start_at, clock_end_at, status')->limit($limit)->order('clock_start_at desc')->select();
    if (!$clockList || count($clockList) == 0) {
        return null;
    }
    foreach ($clockList as &$info) {
        if (!empty($info['clock_end_at'])) {
            $fitTimeSec = $info['clock_end_at'] - $info['clock_start_at'];
            $fitTimeMin = floor($fitTimeSec / 60);
            $fitTimeSec = $fitTimeSec % 60;
            $fitTime = $fitTimeMin . '分' . $fitTimeSec . '秒';
            $info['clock_end_at'] = date('Y-m-d H:i:s', $info['clock_end_at']);
        } else {
            $info['clock_end_at'] = '暂未结束';
            $fitTime = '暂未结束';
        }
        $info['fit_time'] = $fitTime;
        $info['clock_start_at'] = date('Y-m-d H:i:s', $info['clock_start_at']);
    }
    return $clockList;
}


/**
 * 通过用户ID获取用户相关信息
 *
 * @param int $uid 用户id
 * @return array 用户信息详情
 */
function getUserInfoById($uid)
{
    // 获取用户信息
    $user = new User;
    $field = "uid, openid, user_name, user_gender, user_mobile, user_birth";
    $userProfile = $user->field($field)->where('uid', $uid)->find();
    // 数据简单处理
    $userProfile['user_gender'] = $userProfile['user_gender'] == 1 ? '男' : '女';

    return $userProfile;
}

/**
 * 获取用户的课程
 *
 * @param int $uid 用户id
 * @param int $pageNum 页码
 * @return void
 */
function getUserCourse($uid, $pageNum = null)
{
    $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : '';
    $user_course = new User_course;
    $userCourseList = $user_course->alias('uc')->join('gym_course c', 'uc.course_id = c.course_id', 'LEFT')->where('uc.uid', $uid)->where('uc.status', '<>', 4)->field('uc.course_id, uc.course_left_times, uc.start_at, uc.end_at, uc.status, c.course_name, c.course_period')->limit($limit)->order('uc.created_at desc')->select();
    if (!$userCourseList || count($userCourseList) == 0) {
        return null;
    }
    $userCourseList = collection($userCourseList)->toArray();
    foreach ($userCourseList as &$info) {
        $info['start_at_conv'] = date('Y-m-d H:i:s', $info['start_at']);
        $info['end_at_conv'] = date('Y-m-d H:i:s', $info['end_at']);
        switch ($info['status']) {
            case 1:
                $info['status_conv'] = '正常';
                break;
            case 2:
                $info['status_conv'] = '结束';
                break;
            case 3:
                $info['status_conv'] = '超时结束';
                break;
            default:
                $info['status_conv'] = '异常';
                break;
        }
    }
    return $userCourseList;
}

/**
 * 获取用户打卡信息
 * 1. 获取用户总共打卡记录
 * 2. 获取用户累计打卡记录
 *
 * @param int $uid
 * @param integer $userType
 * @return void
 */
function getUserClockInfo($uid, $userType)
{
    $curTime = time();
    // 获取用户连续不间断打卡次数
    $nonStopCount = Db::name('clock_count')->where('uid', $uid)->where('user_type', $userType)->where('status', 2)->value('non_stop_count');
    $res['nonStopClockCount'] = $nonStopCount ? $nonStopCount : 0;
    // 获取用户总打卡次数
    $res['clockCount'] = Db::name('coach_clock')->where('uid', $uid)->field('clock_id')->count();
    // 如果查询的人是教练，那么就需要去检查一下是否有未结束打卡的课程（下班打卡）
    if ($userType == 2) {
        $unFinish = checkWorkClock($uid);
        $res['unFinishClock'] = $unFinish ? $unFinish : false;
        // 获取教练上班时间
        $workTime = Db::name('mini_setting')->where('setting_id', 1)->field('coach_work_start_at, coach_work_end_at')->find();
        $today = strtotime('today');
        $temp['coach_work_start_at_conv'] = date('H:i:s', $today + $workTime['coach_work_start_at']);
        $temp['coach_work_start_at'] = $today + $workTime['coach_work_start_at'];
        $temp['coach_work_end_at_conv'] = date('H:i:s', $today + $workTime['coach_work_end_at']);
        $temp['coach_work_end_at'] = $today + $workTime['coach_work_end_at'];
        $res['coach_work_time'] = $temp;
    }
    return $res;
}

/**
 * 检查教练是否有未处理的下班打卡
 *
 * @param int uid 教练ID
 * @return void
 */
function checkWorkClock($uid)
{
    // 是否有未结束打卡的记录
    $hasUnfinishClock = false;
    $unfinishClockInfo = [];
    Db::startTrans();
    try {
        // 1. 判断是否有未结束打卡的记录
        // 如果有未打卡记录要判断此记录是否需要更新
        $unfinishClock = Db::name('coach_clock')->where('uid', $uid)->where('status', 1)->field('clock_id, clock_start_at')->find();
        if ($unfinishClock) {
            $hasUnfinishClock = true;
            // 判断是否需要自动结束打卡 
            // 获取教练下班时间
            $finishAt = Db::name('mini_setting')->where('setting_id', 1)->value('coach_work_end_at');
            $finishAt = strtotime('today') + $finishAt;
            // 判断是否需要自动结束打卡
            // if ($finishAt < $curTime) {
            //     $hasUnfinishClock = false;
            //     $updateUserClock = Db::name('coach_clock')->where('clock_id', $unfinishClock['clock_id'])->update(['clock_end_at' => $finishAt]);
            //     if (!$updateUserClock) {
            //         throw new \Exception('Update User Clock Failed');
            //     }
            // } else {
            //     // 整理返回数据
            //     $unfinishClockInfo['clock_id'] = $unfinishClock['clock_id'];
            //     $unfinishClockInfo['clock_start_at'] = date('Y-m-d H:i:s', $unfinishClock['clock_start_at']);
            // }
            // 整理返回数据
            $unfinishClockInfo['clock_id'] = $unfinishClock['clock_id'];
            $unfinishClockInfo['clock_start_at'] = date('Y-m-d H:i:s', $unfinishClock['clock_start_at']);
        }
    // 提交事务
        Db::commit();
    } catch (\Exception $e) {
    // 回滚事务
        Db::rollback();
        return $e->getMessage();
    }
    return $unfinishClockInfo;
}

/**
 * 获取课程列表
 *
 * @param boolean $isAll 是否获取所有课程
 * @param int $pageNum 页码
 * @return void
 */
function getCourse($isAll = false, $pageNum = null)
{
    $status = $isAll ? [1, 2] : [1];
    $limit = $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : '';
    $field = "course_id, course_name, course_price, course_period, status, created_at";
    $course = new Course;
    $courseList = $course->where('status', 'in', $status)->field($field)->limit($limit)->order('created_at desc')->select();
    if (!$courseList || count($courseList) == 0) {
        return null;
    }
    $courseList = collection($courseList)->toArray();
    return $courseList;
}

/**
 * 用户打卡公共方法
 * 根据不同的用户类型进行打卡操作
 *
 * @param int $uid 用户id
 * @param int $timeStamp 打卡的时间戳
 * @param string $location 打卡的地点
 * @return boolean $result 是否打卡成功
 */
function makeClock($uid, $timeStamp, $location)
{
    // 启动事务
    Db::startTrans();
    try {
        // 1 写入打卡记录
        $insertClock = Db::name('coach_clock')->insert(['uid' => $uid, 'clock_start_at' => $timeStamp, 'clock_start_location' => $location, 'created_at' => time()]);
        if (!$insertClock) {
            throw new \Exception('Insert User Clock Failed');
        }
        // 4 更新用户连续打卡次数及打卡总次数
        $userClock = Db::name('clock_count')->where('uid', $uid)->where('user_type', 2)->where('status', 1)->field('idx, non_stop_count, last_clock_at, status')->find();
        if (!$userClock) {
            // 如果没有 就插入
            $insertUserClock = Db::name('clock_count')->insert(['uid' => $uid, 'user_type' => 2, 'last_clock_at' => $timeStamp]);
            if (!$insertUserClock) {
                throw new \Exception('Insert User Clock Count Info Failed');
            }
        } else {
            // 如果有 就去判断是否需要更新
            if ($userClock['last_clock_at'] - $timeStamp <= 86400) {
                $userClock['non_stop_count']++;
            } else if ($userClock['last_clock_at'] - $timeStamp > 86400) {
                $userClock['non_stop_count'] = 0;
            }
            $updateUserClock = Db::name('clock_count')->where('idx', $userClock['idx'])->update(['non_stop_count' => $userClock['non_stop_count']]);
            if (!$updateUserClock) {
                throw new \Exception('Insert User Clock Count Info Failed');
            }
        }
        // 提交事务
        Db::commit();
    } catch (\Exception $e) {
    // 回滚事务
        Db::rollback();
        return false;
    }
    return true;
}

/**
 * 用户打卡公共方法
 * 用户进行结束打卡操作
 *
 * @param int $clockId 打卡的id
 * @param int $timeStamp 打卡的时间戳
 * @param string $location 打卡的地点
 * @return boolean $result 是否打卡成功
 */
function endClock($clockId, $timeStamp, $location)
{
    // 启动事务
    Db::startTrans();
    try {
        // 1 更新打卡记录
        $updateClock = Db::name('coach_clock')->where('clock_id', $clockId)->update(['clock_end_location' => $location, 'clock_end_at' => $timeStamp, 'status' => 2]);
        if (!$updateClock) {
            throw new \Exception('Find User Clock Log Failed');
        }
        // 提交事务
        Db::commit();
    } catch (\Exception $e) {
        // 回滚事务
        Db::rollback();
        return false;
    }
    return true;
}

/**
 * 获取用户反馈
 *
 * @param boolean $isAll 是否获取全部状态的反馈
 * @param int $uid 用户ID
 * @param int $pageNum 需要查询的页码
 * @return void
 */
function getFeedBack($uid = null, $userType = 1, $pageNum = null)
{
    $field = "idx, uid, content, img, reply, created_at, reply_at, reply_by, status, user_type";
    $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : '';
    // 如果有传用户ID 则查询指定用户的反馈记录
    $feedback = new Feedback;
    if (isset($uid)) {
        $feedbackList = $feedback->where('uid', $uid)->where('status', 'in', [1, 2])->field($field)->limit($limit)->order('created_at desc')->select();
    } else {
        $feedbackList = $feedback->alias('f')->join('gym_user u', 'f.uid = u.uid and f.user_type = u.user_type', 'LEFT')->where('f.status', 'in', [1, 2])->field("f.idx, f.uid, f.content, f.img, f.reply, f.created_at, f.reply_at, f.reply_by, f.status, f.user_type, u.user_name, u.user_avatar_url")->order('created_at desc')->select();
    }
    if (!$feedbackList || count($feedbackList) == 0) return null;
    $feedbackList = collection($feedbackList)->toArray();
    foreach ($feedbackList as &$info) {
        if (!empty($info['reply_at'])) $info['reply_at'] = date('Y-m-d H:i:s', $info['reply_at']);
        $info['created_at'] = date('Y-m-d H:i:s', $info['created_at']);
        $info['status_conv'] = $info['status'] == 1 ? '待回复' : '已回复';
    }
    return $feedbackList;
}

/**
 * 获取小程序AccessToken
 *
 * @return string $accessToken
 */
function getAccessToken()
{
    $accessToken = Cache::get('accessToken');
    if (!$accessToken) {
        $appid = "wx5556a337614ec0f6";
        $appsecret = "6febbeb04af3f26ab65c086ef847df4b";
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
        $info = file_get_contents($url);
        $info = json_decode($info);
        $info = get_object_vars($info);
        $accessToken = $info['access_token'];
        // $expirs_in = $info['expires_in'] - 100;
        // 将accessToken的有效期设置为3600s（一般情况下有效期7200s）
        Cache::set('accessToken', $accessToken, 6800);
    }

    return $accessToken;
}