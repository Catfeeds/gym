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
function getClockList($uid, $userType = 1, $pageNum = null)
{
    $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : '';
    $clock = new Clock;
    $clockList = $clock->alias('c')->join('gym_course gc', 'c.course_id = gc.course_id', 'LEFT')->where('c.user_type', $userType)->where('c.uid', $uid)->where('c.status', '<>', 3)->field('c.clock_id, c.clock_start_at, c.clock_start_location, c.clock_end_at, c.course_id, gc.course_name, gc.course_period, c.status')->limit($limit)->order('c.created_at desc')->select();
    if (!$clockList || count($clockList) == 0) return null;
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
    foreach ($userCourseList as &$info) {
        $info['start_at'] = date('Y-m-d H:i:s', $info['start_at']);
        $info['end_at'] = date('Y-m-d H:i:s', $info['end_at']);
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
 * @param [type] $uid
 * @param integer $userType
 * @return void
 */
function getUserClockInfo($uid, $userType)
{
    $curTime = time();
    // 初始化data值
    $res['nonStopClockCount'] = 0;
    $clock_count = new Clock_count;
    $clockCount = $clock_count->where('uid', $uid)->where('user_type', $userType)->field('non_stop_count')->where('status', 2)->find();
    // 有打卡记录
    if ($clockCount) {
        $clockCount = collection($clockCount)->toArray();
        $res['nonStopClockCount'] = $clockCount['non_stop_count'];
    }
    $res['clockCount'] = Db::name('clock')->where('uid', $uid)->where('user_type', $userType)->where('status', 2)->field('clock_id')->count();
    // 是否有未结束打卡的记录
    $isHaveUnfinishClock = false;
    $unfinishClockInfo = [];
    Db::startTrans();
    try {
        // 1. 判断是否有未结束打卡的记录
        // 如果有未打卡记录要判断此记录是否需要更新
        $unfinishClock = Db::name('clock')->where('uid', $uid)->where('user_type', $userType)->where('status', 1)->field('clock_id, course_id, clock_start_at, clock_start_at')->find();
        if ($unfinishClock) {
            $isHaveUnfinishClock = true;
            // 查询当前课程的周期，判断是否需要自动结束打卡
            $courseInfo = Db::name('course')->where('course_id', $unfinishClock['course_id'])->field('course_name, course_period')->find();
            $courseFinishAt = $unfinishClock['clock_start_at'] + $courseInfo['course_period'] * 60;
            if ($courseFinishAt < $curTime) {
                $isHaveUnfinishClock = false;
                $updateUserClock = Db::name('clock')->where('clock_id', $unfinishClock['clock_id'])->update(['clock_end_at' => $courseFinishAt, 'clock_end_at' => $unfinishClock['clock_start_at'], 'status' => 2]);
                if (!$updateUserClock) {
                    throw new \Exception('Update User Clock Failed');
                }
            } else {
                // 整理返回数据
                $unfinishClockInfo['clock_id'] = $unfinishClock['clock_id'];
                $unfinishClockInfo['course_id'] = $unfinishClock['course_id'];
                $unfinishClockInfo['clock_start_at'] = date('Y-m-d H:i:s', $unfinishClock['clock_start_at']);
                $unfinishClockInfo['course_name'] = $courseInfo['course_name'];
                $unfinishClockInfo['course_period'] = $courseInfo['course_period'];
            }
        }
        // 提交事务
        Db::commit();
    } catch (\Exception $e) {
        // 回滚事务
        Db::rollback();
        return $e->getMessage();
    }
    $res['isHaveUnfinishClock'] = $isHaveUnfinishClock;
    $res['unfinishClock'] = $unfinishClockInfo;
    return $res;
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
 * @param int $userType 用户类别 1 会员 2 教练
 * @param int $courseId 需要打卡的课程id
 * @param int $timeStamp 打卡的时间戳
 * @param string $location 打卡的地点
 * @return boolean $result 是否打卡成功
 */
function makeClock($uid, $userType, $courseId, $timeStamp, $location)
{
    // 启动事务
    Db::startTrans();
    try {
        // 1 写入打卡记录
        $insertClock = Db::name('clock')->insert(['uid' => $uid, 'user_type' => $userType, 'clock_start_at' => $timeStamp, 'clock_start_location' => $location, 'created_at' => time()]);
        if (!$insertClock) {
            throw new \Exception('Insert User Clock Failed');
        }
        // 只有当用户身份为 会员时 才去更新 课程相关
        if ($userType == 1) {
            // 2 判断用户是否有该课程 并且该课程 课时剩余量大于零
            $userCourse = Db::name('user_course')->where('uid', $uid)->where('course_id', $courseId)->where('course_left_times', '>', 0)->where('status', 1)->where('end_at', '<', $timeStamp)->field('idx, end_at, course_left_times, status')->find();
            if (!$userCourse) {
                throw new \Exception('Invaild User Course');
            }
            // 3 课程相应减少 若课程剩余量为0则改变课程状态
            $userCourse['course_left_times']--;
            if ($userCourse['course_left_times'] == 0) {
                $userCourse['status'] = 2;
            } else if ($userCourse['end_at'] < time() && $userCourse['status'] == 1) {
                $userCourse['status'] = 3;
            }
            $updateUserCourse = Db::name('user_course')->where('idx', $userCourse['idx'])->update($userCourse);
            if (!$updateUserCourse) {
                throw new \Exception('Update User Course Failed');
            }
        }
        // 4 更新用户连续打卡次数及打卡总次数
        $userClock = Db::name('clock_count')->where('uid', $uid)->where('user_type', $userType)->where('status', 1)->field('idx, non_stop_count, last_clock_at, status')->find();
        if (!$userClock) {
            // 如果没有 就插入
            $insertUserClock = Db::name('clock_count')->insert(['uid' => $uid, 'user_type' => $userType, 'last_clock_at' => $timeStamp]);
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
        $updateClock = Db::name('clock')->where('clock_id', $clockId)->update(['clock_end_location' => $location, 'clock_end_at' => $timeStamp, 'status' => 2]);
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