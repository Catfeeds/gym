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
 * 获取管理员发送的消息
 * 如果有传用户的uid则为查找系统消息以及该用户的相关信息
 * 
 * @param string $msgType 需要查询的信息分类 0公告 1对指定用户发送
 * @param string $field 需要查询的字段
 * @param boolean $isAll 是否查看全部的消息
 * @param int $uid 用户的uid
 * @param int $pageNum 需要查看的页码
 * @return void
 */
function getMessage($msgType = 0, $field = null, $isAll = true, $uid = null, $pageNum = null)
{
    // if ($msgType == 1 && !$uid) {
    //     return "Invaild Param";
    // }
    $field = $field ? $field : 'msg_id, msg_type, msg_content, msg_img, target_uid, target_openid, send_at';
    $status = $isAll ? [1, 2] : [2];
    $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : "";

    $msg = new Msg;

    if ($msgType == 0) {
        $msgList = $msg->where('msg_type', $msgType)->where('status', 'in', $status)->field($field)->limit($limit)->order('created_at desc')->select();
    } else if ($msgType == 1) {
        if (isset($uid)) {
            $msgList = $msg->where('msg_type', $msgType)->where('target_uid', $uid)->where('status', 'in', $status)->field($field)->select();
        } else {
            $msgList = $msg->where('msg_type', $msgType)->where('status', 'in', $status)->field($field)->limit($limit)->order('created_at desc')->select();
        }
    }

    if (!$msgList || count($msgList) == 0) return null;
    $msgList = collection($msgList)->toArray();

    if (is_array($msgList) && count($msgList) > 0) {
        foreach ($msgList as &$info) {
            $info['msg_img'] = config('SITEROOT') . $info['msg_img'];
            $info['send_at'] = isset($info['send_at']) && !empty($info['send_at']) ? date('Y-m-d H:i', $info['send_at']) : '';
        }
    }

    return $msgList;
}

/**
 * 获取指定的MSG
 *
 * @param int $msgId
 * @param string $field
 * @param boolean $isInUse
 * @return void
 */
function getMsgById($msgId, $isAll = true)
{
    $status = $isAll ? [2] : [1, 2];
    $msg = new Msg;
    $msgInfo = $msg->alias('m')->join('art_admin a', 'm.send_by = a.id', 'LEFT')->join('art_classes c', 'm.class_id = c.class_id', 'LEFT')->where('m.msg_id', $msgId)->where('m.status', 'in', $status)->field('m.msg_id, m.msg_content, m.msg_img, m.class_id, m.send_at, m.send_by, m.status, a.name as send_by_name, c.class_name, c.class_id, c.class_time, c.class_day')->select();
    if (!$msgInfo || count($msgInfo) == 0) return null;
    $msgInfo = collection($msgInfo)->toArray();
    $msgInfo = $msgInfo[0];
    $msgInfo['msg_img'] = config('SITEROOT') . $msgInfo['msg_img'];
    $msgInfo['send_at'] = date('Y-m-d H:i', $msgInfo['send_at']);
    $msgInfo['class_day_conv'] = convertDay($msgInfo['class_day']);
    return $msgInfo;
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
    foreach ($bannerList as &$info) {
        $info['img'] = config('SITEROOT') . $info['img'];
    }
    return $bannerList;
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
    $clockList = $clock->alias('c')->join('gym_course gc', 'c.course_id = gc.course_id', 'LEFT')->where('c.user_type', $userType)->where('c.uid', $uid)->where('c.status', '<>', 3)->field('c.clock_id, c.clock_start_at, c.clock_start_location, c.clock_end_at, c.course_id, gc.course_name, gc.course_period')->limit($limit)->order('c.created_at desc')->select();
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
    $clockCount = $clock_count->where('uid', $uid)->where('user_type', $userType)->field('non_stop_count')->where('status', 1)->find();
    // 有打卡记录
    if ($clockCount) {
        $clockCount = collection($clockCount)->toArray();
        $res['nonStopClockCount'] = $clockCount['non_stop_count'];
    }
    $res['clockCount'] = Db::name('clock')->where('uid', $uid)->where('user_type', $userType)->where('status', 1)->field('clock_id')->count();
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
 * 获取用户的课程
 *
 * @param int $uid 用户id
 * @param boolean $isAll 是否需要查询所有（包括删除）
 * @return void
 */
function getUserCourse($uid, $isAll = false)
{
    $field = 'uc.idx, uc.uid, uc.course_id, uc.course_left_times, uc.start_at, uc.end_at, uc.created_at, uc.status, c.course_name, c.course_period';
    $status = $isAll ? [1, 2, 3, 4, 5] : [1];
    $user_course = new User_course;
    $userCourseList = $user_course->alias('uc')->join('course c', 'uc.course_id = c.course_id', 'LEFT')->where('uc.uid', $uid)->where('uc.status', 'in', $status)->field($field)->order('uc.idx asc')->select();
    if (!$userCourseList || count($userCourseList) == 0) {
        return null;
    }
    $userCourseList = collection($userCourseList)->toArray();
    // 如果有课程 判断每个课程是否都有效
    $validCourse = [];
    $updateArr = [];
    $validSort = [];
    foreach ($userCourseList as $k => $v) {
        // 超时需要更新状态
        if ($v['status'] == 1 && $v['end_at'] < time()) {
            $v['status'] = 3;
            $v['updated_at'] = time();
            $updateArr[] = $v;
            continue;
        }
        // 当未到打卡时间时，此表示暂存状态 不可点击 但是同样展示 显示开始时间
        if ($v['status'] == 1 && $v['start_at'] > time()) {
            $v['status'] = 5;
            $v['start_at_conv'] = date('Y-m-d', $v['start_at']);
        }
        // 正常情况
        $validSort[] = $v['status'];
        $validCourse[] = $v;
    }
    // 如果有需要update的data 则update
    if (count($updateArr) > 0) {
        $user_course->isUpdate()->saveAll($updateArr);
    }
    // 如果有效课程存在 将所有不展示的课程放在最后
    if (count($validCourse) > 1) {
        array_multisort($validSort, SORT_ASC, SORT_NUMERIC, $validCourse);
    }
    // 返回正常的课程字段
    return $validCourse;
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
    $field = "idx, uid, content, img, reply, created_at, reply_at, reply_by, status";
    $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : '';
    // 如果有传用户ID 则查询指定用户的反馈记录
    $feedback = new Feedback;
    if (isset($uid)) {
        $feedbackList = $feedback->where('uid', $uid)->where('status', 'in', [1, 2])->field($field)->limit($limit)->order('created_at desc')->select();
    } else {
        $feedbackList = $feedback->where('status', 'in', [1, 2])->field($field)->limit($limit)->order('created_at desc')->select();
    }
    if (!$feedbackList || count($feedbackList) == 0) return null;
    $feedbackList = collection($feedbackList)->toArray();
    foreach ($feedbackList as &$info) {
        if (!empty($info['reply_at'])) $info['reply_at'] = date('Y-m-d H:i:s', $info['reply_at']);
        if (!empty($info['img'])) $info['img'] = config('SITEROOT') . $info['img'];
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

/**
 * 获取用户的模板消息
 *
 * @param int $uid 用户id
 * @param int $courseId 课程id
 * @return string fromid
 */
function getFormId($uid)
{
    $formid = new Formid;
    $formID = $formid->where('uid', $uid)->where('is_active', 0)->field('idx, formid')->limit(1)->select();
    if (!$formID || count($formID) == 0) {
        return null;
    }
    $formID = collection($formID)->toArray();
    return $formID[0];
}

/**
 * 发送模板消息
 *
 * @param array $msgType 需要发送的 消息类型
 * @param array $msgId 需要发送的msgId
 * @param string $formId 模板消息ID
 * @param string $openid 用户的openid
 * @param string $content 发送的消息内容
 * 
 * @param json $msg 发送的消息内容
 * @return void
 */
function sendTemplateMessage($msg)
{
    $accessToken = getAccessToken();
    // $access_token = "4_Jy79EbZz8z04qBNdILIs6ZdAWWN1dAs0Dz7BJrLpDCUgBaNiaoL9o2ulH5Ki89Zx01BvYzRMvS4-ArKMgm4eAaMmNXlrWCWNhC8zyoi7BATKxplujaf_wW1Az2hnv9geHWgCL6P5psNtRr9ECYTjACASOJ";
    $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=" . $accessToken;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    // 这句话很重要 因为是SSL加密协议
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $msg);
    $output = curl_exec($ch);
    curl_close($ch);
    $info = json_decode($output);
    $info = get_object_vars($info);
    return $info['errcode'];
}