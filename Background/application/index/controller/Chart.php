<?php
namespace app\index\controller;

use \think\Controller;
use \think\Request;
use app\index\model\Order;
use app\index\model\User;
use think\Db;

class Chart extends Controller
{
    /**
     * 会员打卡记录查询界面
     *
     * @return html
     */
    public function memberchart()
    {
        $memberList = Db::name('user')->where('user_type', 1)->where('status', '<>', 3)->field('user_name, uid')->select();
        $this->assign('memberList', $memberList ? $memberList : []);
        return $this->fetch();
    }

    /**
     * 获取指定课程中的会员
     *
     * @return void
     */
    public function getCourseUser()
    {
        $courseId = intval(request()->param('cid'));
        $user = new User;
        $field = "u.uid, u.user_name";
        $courseUserList = $user->alias('u')->join('gym_user_course uc', 'u.uid = uc.uid', 'RIGHT')->where('u.status', '<>', 3)->where('u.user_type', 1)->where('uc.course_id', $courseId)->where('uc.status', '<>', 4)->field($field)->select();
        $courseUserList = $courseUserList ? collection($courseUserList)->toArray() : [];
        return objReturn(0, 'success', $courseUserList);
    }

    /**
     * 获取用户打卡日期的折线图数据
     *
     * @return void
     */
    public function getClockData()
    {
        $uid = intval(request()->param('uid'));
        $userType = intval(request()->param('userType'));
        $timeStart = strtotime(request()->param('timeStart'));
        $timeEnd = strtotime(request()->param('timeEnd'));
        $timeEnd = $timeStart == $timeEnd ? $timeEnd + 86399 : $timeEnd + 86400;
        // 1 获取用户指定日期、指定课程的打卡记录
        $clockData = Db::name('clock')->where('uid', $uid)->where('user_type', $userType)->where('created_at', 'between', [$timeStart, $timeEnd])->where('status', '<>', 3)->field('clock_start_at, status, course_id')->select();
        if (!$clockData) {
            return objReturn(400, '该会员当前时间段内暂无打卡记录');
        }
        // 2 构造xData 日期列表
        $xData = [];
        // 3 构造seriesData 打卡次数 打卡时长
        $courseData = [];
        $courseIds = [];
        // 先做一次循环 计算其中的课程数量
        foreach ($clockData as $k => $v) {
            $temp = [];
            $temp['course_id'] = $v['course_id'];
            $temp['data'] = [];
            $courseData[] = $temp;
            $courseIds[] = $v['course_id'];
        }

        $count = 0; // 计数标记
        for ($i = $timeStart; $i < $timeEnd; $i += 86400) {
            $xData[] = date('Y-m-d', $i);
            // 数据初始化
            foreach ($courseData as $k => $v) {
                $courseData[$k]['data'][$count] = 0;
            }
            // 数据统计
            foreach ($clockData as $ke => $va) {
                if (($va['clock_start_at'] >= $i) && ($va['clock_start_at'] < $i + 86400)) {
                    foreach ($courseData as $k => $v) {
                        if ($v['course_id'] == $va['course_id']) {
                            $courseData[$k]['data'][$count] += 1;
                            break 1;
                        }
                    }
                }
            }
            $count++;
        }
        // 查找课程名称
        $courseNames = Db::name('course')->where('course_id', 'in', $courseIds)->field('course_name, course_id')->select();
        // 组合seriesData
        $seriseData = [];
        foreach ($courseData as $k => $v) {
            $seriseLine = [];
            // 1 匹配课程名称
            foreach ($courseNames as $ke => $va) {
                if ($v['course_id'] == $va['course_id']) {
                    $seriseLine['name'] = $va['course_name'];
                    break 1;
                }
            }
            // 2 设置其类别
            $seriseLine['type'] = "line";
            // 3 填充数据
            $seriseLine['data'] = $v['data'];
            $seriseData[] = $seriseLine;
        }
        // 构造返回值res
        $res['serise'] = $seriseData;
        $res['xdata'] = $xData;
        return objReturn(0, 'success', $res);
    }

    /**
     * 教练打卡图表界面
     *
     * @return html
     */
    public function coachchart()
    {
        $coachList = Db::name('user')->where('user_type', 2)->where('status', '<>', 3)->field('user_name, uid')->select();
        $this->assign('coachList', $coachList ? $coachList : []);
        return $this->fetch();
    }
}
