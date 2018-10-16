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
 * 更细数据库相关信息
 *
 * @param int $table 需要更新的表名
 * @param array $where 更新的字段
 * @param int $isUpdate 是更新还是新增
 * @return int $isSuccess 是否更新成功
 */
function saveData($table, $where, $isUpdate = true)
{
    if (!$table || !is_string($table) || !$where || !is_array($where) || $isUpdate && !is_bool($isUpdate)) return 'Invaild Table';

    // 表名
    $tableName = null;
    switch ($table) {
        case 'user':
            $tableName = new User;
            break;
        case 'msg':
            $tableName = new Msg;
            break;
        case 'banner':
            $tableName = new Banner;
            break;
        case 'admin':
            $tableName = new Admin;
            break;
        case 'coach':
            $tableName = new Coach;
            break;
    }
    // 判断数据长度
    $isSuccess = $tableName->isUpdate($isUpdate)->save($where);
    // 结果返回
    return $isSuccess;
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
function getClockList($uid, $userType = 1, $pageNum = null, $status = 1)
{
    $limit = isset($pageNum) ? $pageNum * 10 . ', 10' : '';
    $status = !is_int($status) ? 1 : $status;
    $clock = new Clock;
    $clockList = $clock->where('user_type', $userType)->where('uid', $uid)->where('status', $status)->field('clock_id, clock_start_at, clock_start_location')->limit($limit)->select();
    if (!$clockList || count($clockList) == 0) return null;
    foreach ($clockList as &$info) {
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
function getUserClockInfo($uid, $userType = 1)
{
    // 初始化data值
    $res['clockCount'] = 0;
    $res['nonStopClockCount'] = 0;
    $clock_count = new Clock_count;
    $clockCountList = $clock_count->where('uid', $uid)->where('user_type', $userType)->select();
    // 没有打卡记录
    if (!$clockCountList || count($clockCountList) == 0) {
        return $res;
    }
    // 有打卡记录
    $clockCountList = collection($clockCountList)->toArray();
    foreach ($clockCountList as $k => $v) {
        $res['clockCount'] += $v['non_stop_count'];
        if ($v['status'] == 1) {
            $res['nonStopClockCount'] = $v['non_stop_count'];
        }
    }
    return $res;
}

/**
 * 给用户打卡操作
 *
 * @param array $clockArr 用户打卡的数组 其中包含uid, clock_by, clock_at, class_id, clock_type
 * @return boolean 是否打卡成功
 */
function makeClock($clockArr)
{
    if (!is_array($clockArr)) return false;
    $classIds = [];
    $uids = [];
    foreach ($clockArr as &$info) {
        $info['created_at'] = time();
        $classIds[] = $info['class_id'];
        $uids[] = $info['uid'];
    }

    // 1 获取用户原有课程
    $classUser = Db::name('classes_user')->where('class_id', 'in', $classIds)->where('uid', 'in', $uids)->field('idx, uid, class_id, course_left_times, course_end_at')->select();
    if (!$classUser) return false;
    // dump($classUser);
    // dump($clockArr);die;
    // 2 用户打卡后课程的处理
    foreach ($classUser as $k => $v) {
        foreach ($clockArr as $ke => $va) {
            if ($v['uid'] == $va['uid'] && $v['class_id'] == $va['class_id']) {
                if ($v['course_end_at'] > time() && $v['course_left_times'] > 0) {
                    $classUser[$k]['course_left_times'] -= 1;
                } else {
                    $va['courseOutOfTime'] = $v['course_end_at'] < time() ? true : false;
                    $va['noCourseLeftTimes'] = $v['course_left_times'] == 0 ? true : false;
                    $va['course_end_at'] = date('Y-m-d', $v['course_end_at']);
                    $va['course_left_times'] = $v['course_left_times'];
                    $notClockArr[] = $va;
                    unset($clockArr[$k]);
                }
                break 1;
            }
        }
    }
    // 事务处理
    $notClockArr = [];
    Db::startTrans();
    try {
        // 3 插入打卡记录
        $insert = Db::name('user_clock')->insertAll($clockArr);
        // dump($insert);die;
        // 4 更新课时记录
        $classes_user = new Classes_user;
        $update = $classes_user->isUpdate()->saveAll($classUser);
        // 提交事务
        Db::commit();
        if (!$classUser || !$insert || !$update) throw new \Exception('Update Failed');
    } catch (\Exception $e) {
        // 回滚事务
        Db::rollback();
        return false;
    }
    if (count($notClockArr) > 0) return $notClockArr;
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