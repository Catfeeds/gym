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
use app\index\model\Classes_user;
use app\index\model\Classes;

class Teacher extends Controller
{

    /**
     * 获取教师所带班级数据
     *
     * @return void
     */
    public function getTearcherClass()
    {
        // 这里的uid 代表了教师的uid
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $tid = intval(request()->param('tid'));
        if (empty($tid)) return objReturn(400, 'Invaild Param');

        // 数据处理
        $isDetail = request()->param('isdetail');
        if ($isDetail == 1) {
            $classes = new Classes;
            $classList = $classes->alias('c')->join('art_course cse', 'c.course_id = cse.course_id', 'LEFT')->join('art_subject sub', 'cse.subject_id = sub.subject_id', 'LEFT')->where('c.teacher_id', $tid)->where('c.status', 2)->field('c.class_id, c.class_name, c.class_day, c.class_time, cse.course_name, cse.course_times, sub.subject_name')->select();
            if (!$classList) return objReturn(0, 'success');
            $classList = collection($classList)->toArray();
            foreach ($classList as &$info) {
                $info['class_day_conv'] = convertDay($info['class_day']);
                // 获取班级人数
                $info['class_stu_num'] = Db::name('classes_user')->where('class_id', $info['class_id'])->count();
            }
        } else {
            $classList = getClasses(false, $tid);
            if (!$classList) return objReturn(0, 'success');
        }
        return objReturn(0, 'success', $classList);
    }

    /**
     * 获取该班级所有的学生数据
     *
     * @return void
     */
    public function getClassStudent()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $tid = intval(request()->param('tid'));
        if (empty($tid)) return objReturn(400, 'Invaild Param');

        $classId = intval(request()->param('classid'));
        // 是否获取学生的详细信息 在学生详情界面会使用
        $isDetail = request()->param('isdetail');
        // 是否需要检测学生当前课程有没有打卡记录 在打卡界面会使用
        $isCheck = request()->param('ischeck');
        if ($isDetail == 1) {
            $classes_user = new Classes_user;
            $classUser = $classes_user->alias('cu')->join('art_classes c', 'cu.class_id = c.class_id', 'LEFT')->join('art_course cse', 'c.course_id = cse.course_id', 'LEFT')->join('art_user u', 'cu.uid = u.uid', 'LEFT')->where('cu.class_id', $classId)->where('cu.status', 1)->field('cu.class_id, cu.uid, cu.course_end_at, cu.course_left_times, cu.course_end_at, cse.course_times, u.username, u.user_gender, u.grade, u.birth, u.stu_no, u.auth_name, u.phone, u.nickname, u.avatar_url')->select();
            if (!$classUser) return objReturn(402, 'No Student');
            $classUser = collection($classUser)->toArray();
            foreach ($classUser as &$info) {
                $info['course_end_at'] = date('Y-m-d', $info['course_end_at']);
            }
            return objReturn(0, 'success', $classUser);
        } else {
            $stuList = getClassesStudent($classId, false);
            if (!$stuList) return objReturn(402, 'No Student');
            // 打卡检测
            if ($isCheck == 1) {
                // 获取时间范围在今日的 指定class的打卡记录
                $clockList = Db::name('user_clock')->where('class_id', $classId)->where('clock_at', 'between', [strtotime('today'), strtotime('+1day')])->field('uid, clock_type')->select();
                // 简单构造stuList 并将未打卡的用户排序放在前面
                $notClock = [];
                $alreadyClock = [];
                foreach ($stuList as $k => $v) {
                    $stuList[$k]['select'] = false;
                    $stuList[$k]['clockType'] = 0;
                    $stuList[$k]['isClock'] = false;
                    foreach ($clockList as $ke => $va) {
                        if ($v['uid'] == $va['uid']) {
                            $stuList[$k]['isClock'] = true;
                            $stuList[$k]['clockType'] = $va['clock_type'];
                            break;
                        }
                    }
                    if ($stuList[$k]['isClock']) {
                        $alreadyClock[] = $stuList[$k];
                    } else {
                        $notClock[] = $stuList[$k];
                    }
                }
                $res['already_clock'] = $alreadyClock;
                $res['not_clock'] = $notClock;
                $stuList = $res;
            }
        }
        return objReturn(0, 'success', $stuList);
    }

    /**
     * 获取教师的指定日期的课程
     *
     * @return void
     */
    public function getSchedule()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $tid = intval(request()->param('tid'));
        if (empty($tid)) return objReturn(400, 'Invaild Param');
        $date = request()->param('date');
        $time = strtotime($date);
        $week = date('w', $time);
        $classes = new Classes;
        $classList = $classes->alias('c')->join('art_course cse', 'c.course_id = cse.course_id', 'LEFT')->join('art_subject sub', 'cse.subject_id = sub.subject_id', 'LEFT')->where('c.teacher_id', $tid)->where('c.status', 2)->where('c.class_day', $week)->field('c.class_id, c.class_name, c.class_day, c.class_time, cse.course_name, cse.course_times, sub.subject_name')->select();
        if ($classList || count($classList) > 0) {
            $classList = collection($classList)->toArray();
            foreach ($classList as &$info) {
                $info['class_day_conv'] = convertDay($info['class_day']);
                // 获取班级人数
                $info['class_stu_num'] = Db::name('classes_user')->where('class_id', $info['class_id'])->count();
            }
        }
        return objReturn(0, 'success', $classList);
    }

    /**
     * 教师提交打卡信息
     *
     * @return void
     */
    public function submitClock()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $tid = intval(request()->param('tid'));
        if (empty($tid)) return objReturn(400, 'Invaild Param');
        // 请求超时
        $timestamp = request()->param('timestamp');
        if (time() - $timestamp > 6) return objReturn(400, 'Overtime');
        $classId = request()->param('classid');
        // 构造学生打卡信息 clockType 1 正常打卡 2 旷课 3 迟到
        $stuIds = request()->param('stuids');
        $stuIds = explode('#', substr($stuIds, 0, strlen($stuIds) - 1));
        $clockArr = [];
        foreach ($stuIds as $k => $v) {
            $clockInfo = explode('*', $v);
            $info = [];
            $info['uid'] = $clockInfo[0];
            $info['clock_by'] = $tid;
            $info['class_id'] = $classId;
            $info['clock_at'] = time();
            $info['clock_type'] = $clockInfo[1] + 1;
            $clockArr[] = $info;
        }
        // 1 打卡 2 减少用户对应课时信息
        $res = makeClock($clockArr);
        if(is_array($res)) return objReturn(403, 'failed', $res);
        if(!$res) return objReturn(401, 'failed', $res);
        return objReturn(0, 'success', $res);
    }

    /**
     * 教师在手机端向同学家长发送消息
     *
     * @return void
     */
    public function sendMessage()
    {
        if (!request()->isPost()) return objReturn(400, 'Invaild Method');
        $tid = intval(request()->param('tid'));
        if (empty($tid)) return objReturn(400, 'Invaild Param');
        
        // 判断是否有文件上传
        $file = request()->file('file');
        if ($file) {
            $targetDir = ROOT_PATH . 'public' . DS . 'message';
            // dump($targetDir);die;
            $save = $file->move($targetDir);
            if (!$save) return objReturn(400, 'System Error', $save);
        }

        // 接收获取接收消息的用户ID
        $stuList = request()->param('stulist/a');
        $msg = [];
        foreach ($stuList as $k => $v) {
            if ($v['select']) {
                $temp = [];
                $temp['msg_type'] = 1;
                $temp['msg_content'] = request()->param('message');
                $temp['msg_img'] = $file ? DS . 'message' . DS . $save->getSaveName() : '';
                $temp['target_uid'] = $v['uid'];
                $temp['class_id'] = intval(request()->param('classid'));
                $temp['send_by'] = $tid;
                $temp['send_at'] = time();
                $temp['created_at'] = time();
                $msg[] = $temp;
            }
        }

        $insert = Db::name('msg')->insertAll($msg);
        if (!$insert) return objReturn(400, 'Insert Failed', $insert);
        return objReturn(0, 'Success', $insert);
    }

}