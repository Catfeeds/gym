<?php
namespace app\index\controller;

use \think\Controller;
use \think\Request;
use app\index\model\Order;
use app\index\model\User;
use think\Db;
use app\index\model\Coach_clock;
use \app\index\controller\Excel as Excel;

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
    public function getCoachClockChart()
    {
        $cids = request()->param('cids');
        // 切分当前uid
        $cidsArr = explode(',', $cids);
        $timeStart = strtotime(request()->param('timeStart'));
        $timeEnd = strtotime(request()->param('timeEnd'));
        $timeEnd = $timeStart == $timeEnd ? $timeEnd + 86399 : $timeEnd + 86400;
        // 1 获取用户指定日期、指定课程的打卡记录
        $clockData = Db::name('coach_clock')->where('uid', 'in', $cidsArr)->where('created_at', 'between', [$timeStart, $timeEnd])->where('status', '<>', 3)->field('clock_start_at, status, uid')->select();
        if (!$clockData) {
            return objReturn(400, '所选教练当前时间段内暂无打卡记录');
        }
        $clockData = collection($clockData)->toArray();
        // 2 构造xData 日期列表
        $xData = [];
        // 3 构造seriesData 打卡次数
        $coachClockData = [];
        // 数据初始化
        foreach ($cidsArr as $k => $v) {
            $temp = [];
            $temp['uid'] = $v;
            $temp['data'] = [];
            $coachClockData[] = $temp;
        }
        $count = 0;
        for ($i = $timeStart; $i < $timeEnd; $i += 86400) {
            $xData[] = date('Y-m-d', $i);

            // 为每个用户今日打卡次数清零
            foreach ($coachClockData as $k => $v) {
                $coachClockData[$k]['data'][$count] = 0;
            }
            // 数据统计
            foreach ($clockData as $ke => $va) {
                if (($va['clock_start_at'] >= $i) && ($va['clock_start_at'] < $i + 86400)) {
                    foreach ($coachClockData as $k => $v) {
                        if ($va['uid'] == $v['uid']) {
                            $coachClockData[$k]['data'][$count] += 1;
                        }
                    }
                }
            }
            $count++;
        }
        // 获取教练名字
        $userNames = Db::name('user')->where('uid', 'in', $cidsArr)->where('user_type', 2)->field('user_name, uid')->select();
        // 组合seriesData
        $seriseData = [];
        foreach ($coachClockData as $k => $v) {
            $seriseLine = [];
            // 1 匹配课程名称
            foreach ($userNames as $ke => $va) {
                if ($v['uid'] == $va['uid']) {
                    $seriseLine['name'] = $va['user_name'];
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
        if($coachList){
            $coachList = collection($coachList)->toArray();
        }else{
            $coachList = [];
        }
        $this->assign('coachList', $coachList);
        return $this->fetch();
    }

    /**
     * 导出教练打卡数据
     *
     * @return void
     */
    public function exportCoachClockData()
    {
        $cids = request()->param('cids');
        // 切分当前uid
        $cidsArr = explode(',', $cids);
        $timeStart = strtotime(request()->param('timeStart'));
        $timeEnd = strtotime(request()->param('timeEnd'));
        $timeEnd = $timeStart == $timeEnd ? $timeEnd + 86399 : $timeEnd + 86400;
        // 获取用户指定日期、指定课程的打卡记录
        $coach_clock = new Coach_clock;
        $clockData = $coach_clock->alias('cc')->join('user u', 'cc.uid = u.uid', 'LEFT')->where('cc.uid', 'in', $cidsArr)->where('cc.created_at', 'between', [$timeStart, $timeEnd])->where('cc.status', '<>', 3)->field('cc.clock_start_at, cc.status, cc.uid, cc.clock_end_at, u.user_name, u.user_mobile')->select();
        if (!$clockData) {
            return objReturn(400, '所选教练当前时间段内暂无打卡记录');
        }
        // 数据处理
        $clockData = collection($clockData)->toArray();
        foreach ($clockData as &$info) {
            $info['status'] == 1 ? '打卡中' : '打卡完成';
            $info['clock_start_at_conv'] = date('Y-m-d H:i:s', $info['clock_start_at']);
            if (!empty($info['clock_end_at'])) {
                $info['clock_end_at_conv'] = date('Y-m-d H:i:s', $info['clock_end_at']);
            }
        }
        // Excel导出相关数据
        $excel = new Excel();
        $saveName = request()->param('timeStart') . '-' . request()->param('timeEnd');
        $fileheader = array(
            '教练名称',
            '教练联系方式',
            '打卡开始时间',
            '打卡结束时间',
            '打卡时长',
            '打卡状态'
        );
        $path = $excel->exportExcel($clockData, $saveName, $fileheader, '打卡记录');
        return objReturn(0, 'success', ['url' => $path]);
    }
}
