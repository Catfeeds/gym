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
        // 查找所有课程并返回
        $courseList = getCourse(true);
        $this->assign('courseList', $courseList);
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
    public function getMemClockData()
    {
        $uid = intval(request()->param('uid'));
        $cid = intval(request()->param('cid'));
        $timeStart = strtotime(request()->param('timeStart'));
        $timeEnd = strtotime(request()->param('timeEnd'));
        $timeEnd = $timeStart == $timeEnd ? $timeEnd + 86399 : $timeEnd + 86400;
        // 1 获取用户指定日期、指定课程的打卡记录
        $clockData = Db::name('clock')->where('uid', $uid)->where('user_type', 1)->where('course_id', $cid)->where('created_at', 'between', [$timeStart, $timeEnd])->where('status', '<>', 3)->field('clock_start_at, clock_end_at, status')->select();
        if (!$clockData) {
            return objReturn(400, '该会员当前时间段内暂无打卡记录');
        }
        // 2 构造xData 日期列表
        $xData = [];
        // 3 构造seriesData 打卡次数 打卡时长
        $seriesClockCount = [];
        $seriseClockPeriod = [];
        $count = 0; // 计数标记
        for ($i = $timeStart; $i < $timeEnd; $i += 86400) {
            $xData[] = date('Y-m-d', $i);
            // 数据初始化
            $seriesClockCount[$count] = 0;
            $seriseClockPeriod[$count] = 0;
            foreach ($clockData as $ke => $va) {
                if (($va['clock_start_at'] >= $i) && ($va['clock_start_at'] < $i + 86400)) {
                    $seriesClockCount[$count] += 1;
                    if ($va['status'] == 1) {
                        $seriseClockPeriod[$count] += 45;
                    } else {
                        $seriseClockPeriod[$count] += $va['clock_end_at'] - $va['clock_start_at'];
                    }
                }
            }
            $count++;
        }
        // 固定series的line marker 折线图的圆圈样式
        $marker['lineWidth'] = 2;
        $marker['lineColor'] = '#cab9d7';
        $marker['fillColor'] = 'white';
        // 组合seriesData
        $seriseColumn['type'] = "column";
        $seriseColumn['name'] = "打卡时长/分钟";
        $seriseColumn['data'] = $seriseClockPeriod;
        $seriseLine['type'] = "line";
        $seriseLine['name'] = "打卡次数/次";
        $seriseLine['data'] = $seriesClockCount;
        $seriseLine['marker'] = $marker;
        $seriseData[] = $seriseColumn;
        $seriseData[] = $seriseLine;

        // 构造返回值res
        $res['serise'] = $seriseData;
        $res['xdata'] = $xData;
        return objReturn(0, 'success', $res);
    }
}
