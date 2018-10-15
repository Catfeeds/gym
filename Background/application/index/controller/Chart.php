<?php
namespace app\index\controller;

use \think\Controller;
use \think\Request;
use app\index\model\Order;
use app\index\model\User_clock;
use app\index\model\Subject;

class Chart extends Controller
{

    /**
     * 下载excel模板
     * @param  Request $request 参数
     * @return ary           下载的结果
     */
    public function downTemplate(Request $request)
    {
        $type = intval($request->param('type'));
        // 调用Excel控制器的template方法
        $excel = new Excel;
        // type为1时为导出用户信息 2为教师信息
        $res = $excel->template($type);
        if ($res) {
            return objReturn(0, '生成模板成功！请点击右侧下载...', $res);
        // header('Content-Type: application/vnd.ms-excel');
            // header('Cache-Control: max-age=0');
            // Header("Accept-Ranges:bytes");
            // return $res;
        } else {
            return objReturn(400, '下载模板失败！');
        }
    }

    /**
     * 课程购买统计
     * @return html 页面
     */
    public function coursechart()
    {
        return $this->fetch();
    }

    /**
     * 获取课程购买统计数据
     * @param  Request $request 参数
     * @return ary              返回数据
     */
    public function getData(Request $request)
    {
        $select = intval($request->param('select'));
        if ($select == 7) {
            // 今天0点时间戳
            $todayEndTime = strtotime(date("Y-m-d 23:59:59"));
            $beginTime = $todayEndTime - 7 * 86400 + 1;
            $xData = array();
            for ($i = 0; $i < 7; $i++) {
                $xData[] = date('Y-m-d', $beginTime + $i * 86400); //每隔一天赋值给数组
            }
            // dump($xData);die;
            // 查询数据
            $order = new Order;
            $data = $order->field('order_id, pay_at')->where('pay_at', 'between', [$beginTime, $todayEndTime])->where('status', 1)->select();
            if (!empty($data)) {
                // 数组里判断时间
                foreach ($xData as $k => $v) {
                    $isAll[$k] = 0;
                    $begin = strtotime($v);
                    $end = strtotime($v) + 86400;
                    foreach ($data as $ke => $va) {
                        if ($va['pay_at'] >= $begin && $va['pay_at'] < $end) {
                            $isAll[$k] += 1;
                        }
                    }
                }
            } else {
                // 无数据时
                $isAll = [];
                foreach ($xData as $k => $v) {
                    $isAll[$k] = 0;
                }
            }
        }
        if ($select == 15) {
            // 今天0点时间戳
            $todayEndTime = strtotime(date("Y-m-d 23:59:59"));
            $beginTime = $todayEndTime - 15 * 86400 + 1;
            $xData = array();
            for ($i = 0; $i < 15; $i++) {
                $xData[] = date('Y-m-d', $beginTime + $i * 86400); //每隔一天赋值给数组
            }
            // dump($xData);die;
            // 查询数据
            $order = new Order;
            $data = $order->field('order_id,pay_at')->where('pay_at', 'between', [$beginTime, $todayEndTime])->where('status', 1)->select();
            if (!empty($data)) {
                // 数组里判断时间
                foreach ($xData as $k => $v) {
                    $isAll[$k] = 0;
                    $begin = strtotime($v);
                    $end = strtotime($v) + 86400;
                    foreach ($data as $ke => $va) {
                        if ($va['pay_at'] >= $begin && $va['pay_at'] < $end) {
                            $isAll[$k] += 1;
                        }
                    }
                }
            } else {
                // 无数据时
                $isAll = [];
                foreach ($xData as $k => $v) {
                    $isAll[$k] = 0;
                }
            }
        }
        // y轴数据
        $data = array(
            'data' => $isAll,
            'name' => '购买总量',
        );
        $seriesData = array();
        array_push($seriesData, $data);
        $title = '课程购买统计';
        $courseData = array(
            'seriesData' => $seriesData,
            'title' => $title,
            'xData' => $xData,
        );
        return json($courseData);
    }

    /**
     * 课程搜索统计功能
     * @param  Request $request 参数
     * @return ary              返回数组
     */
    public function getSearchData(Request $request)
    {
        $startTime = strtotime($request->param('startTime'));
        $endTime = strtotime($request->param('endTime'));
        $xData = array();
        // x轴数据
        for ($i = $startTime; $i <= $endTime; $i += 86400) {
            $xData[] = date('Y-m-d', $i); //每隔1天赋值给数组
        }
        // dump($xData);die;
        $endTime = $endTime + 86400; //加一天
        // 查询数据
        $order = new Order;
        $data = $order->field('order_id, pay_at')->where('pay_at', 'between', [$startTime, $endTime])->where('status', 1)->select();
        if (!empty($data)) {
            // 数组里判断时间
            foreach ($xData as $k => $v) {
                $isAll[$k] = 0;
                $begin = strtotime($v);
                $end = strtotime($v) + 86400;
                foreach ($data as $ke => $va) {
                    if ($va['pay_at'] >= $begin && $va['pay_at'] < $end) {
                        $isAll[$k] += 1;
                    }
                }
            }
        } else {
            // 无数据时
            $isAll = [];
            foreach ($xData as $k => $v) {
                $isAll[$k] = 0;
            }
        }
        // y轴数据
        $data = array(
            'data' => $isAll,
            'name' => '购买总量',
        );
        $seriesData = array();
        array_push($seriesData, $data);
        $title = '课程购买统计';
        $courseData = array(
            'seriesData' => $seriesData,
            'title' => $title,
            'xData' => $xData,
        );
        return json($courseData);
    }

    /**
     * 收入统计
     * @return html 页面
     */
    public function incomechart()
    {
        return $this->fetch();
    }

    /**
     * 获取收入统计数据
     * @param  Request $request 参数
     * @return ary              返回数据
     */
    public function getIncomeData(Request $request)
    {
        $select = intval($request->param('select'));
        if ($select == 7) {
            // 今天0点时间戳
            $todayEndTime = strtotime('today') + 86400;
            $beginTime = $todayEndTime - 7 * 86400;
            $xData = [];
            for ($i = 0; $i < 7; $i++) {
                $xData[] = date('Y-m-d', $beginTime + $i * 86400); //每隔一天赋值给数组
            }
            // dump($xData);
            // dump($todayEndTime);die;
            // 查询数据
            $order = new Order;
            $data = $order->field('order_id, pay_at, fee')->where('pay_at', 'between', [$beginTime, $todayEndTime])->where('status', 1)->select();
            if ($data && count($data) > 0) {
                $data = collection($data)->toArray();
                // 数组里判断时间
                foreach ($xData as $k => $v) {
                    $isAll[$k] = 0;
                    $begin = strtotime($v);
                    $end = strtotime($v) + 86400;
                    foreach ($data as $ke => $va) {
                        if ($va['pay_at'] >= $begin && $va['pay_at'] < $end) {
                            $isAll[$k] += $va['fee'];
                        }
                    }
                }
            } else {
                // 无数据时
                $isAll = [];
                foreach ($xData as $k => $v) {
                    $isAll[$k] = 0;
                }
            }
        }
        if ($select == 15) {
            // 今天0点时间戳
            $todayEndTime = strtotime('today') + 86400;
            $beginTime = $todayEndTime - 15 * 86400;
            $xData = array();
            for ($i = 0; $i < 15; $i++) {
                $xData[] = date('Y-m-d', $beginTime + $i * 86400); //每隔一天赋值给数组
            }
            // dump($xData);die;
            // 查询数据
            $order = new Order;
            $data = $order->field('order_id,pay_at,fee')->where('pay_at', 'between', [$beginTime, $todayEndTime])->where('status', 1)->select();
            if (!empty($data)) {
                // 数组里判断时间
                foreach ($xData as $k => $v) {
                    $isAll[$k] = 0;
                    $begin = strtotime($v);
                    $end = strtotime($v) + 86400;
                    foreach ($data as $ke => $va) {
                        if ($va['pay_at'] >= $begin && $va['pay_at'] < $end) {
                            $isAll[$k] += $va['fee'];
                        }
                    }
                }
            } else {
                // 无数据时
                $isAll = [];
                foreach ($xData as $k => $v) {
                    $isAll[$k] = 0;
                }
            }
        }
        // y轴数据
        $data = array(
            'data' => $isAll,
            'name' => '收入统计',
        );
        $seriesData = array();
        array_push($seriesData, $data);
        $title = '收入统计';
        $incomeData = array(
            'seriesData' => $seriesData,
            'title' => $title,
            'xData' => $xData,
        );
        return json($incomeData);
    }

    /**
     * 课程搜索统计功能
     * @param  Request $request 参数
     * @return ary              返回数组
     */
    public function getSearchData2(Request $request)
    {
        $startTime = strtotime($request->param('startTime'));
        $endTime = strtotime($request->param('endTime'));
        $xData = array();
        // x轴数据
        for ($i = $startTime; $i <= $endTime; $i += 86400) {
            $xData[] = date('Y-m-d', $i); //每隔1天赋值给数组
        }
        // dump($xData);die;
        $endTime = $endTime + 86400; //加一天
        // 查询数据
        $order = new Order;
        $data = $order->field('order_id, pay_at, fee')->where('pay_at', 'between', [$startTime, $endTime])->where('status', 1)->select();
        if (!empty($data)) {
            // 数组里判断时间
            foreach ($xData as $k => $v) {
                $isAll[$k] = 0;
                $begin = strtotime($v);
                $end = strtotime($v) + 86400;
                foreach ($data as $ke => $va) {
                    if ($va['pay_at'] >= $begin && $va['pay_at'] < $end) {
                        $isAll[$k] += $va['fee'];
                    }
                }
            }
        } else {
            // 无数据时
            $isAll = [];
            foreach ($xData as $k => $v) {
                $isAll[$k] = 0;
            }
        }
        // y轴数据
        $data = array(
            'data' => $isAll,
            'name' => '收入统计',
        );
        $seriesData = array();
        array_push($seriesData, $data);
        $title = '收入统计';
        $courseData = array(
            'seriesData' => $seriesData,
            'title' => $title,
            'xData' => $xData,
        );
        return json($courseData);
    }

    // 课程结束时间 - 课程周期 = 课程起始时间
    // 一周只有一天有课
    // 构造单学生的课程的打卡时间为x轴
    // 一个学生的打卡情况为y轴

    // 学生打卡情况
    public function clockchart()
    {
        // 调用公用函数，获取所有课程
        $field = 'course_id, course_name';
        $courseData = getCourse($field, false, null);
        $this->assign('courseData', $courseData);
        $subject = new Subject;
        $subjectData = $subject->field('subject_id,subject_name')->select();
        $this->assign('subjectData', $subjectData);
        return $this->fetch();
    }

    /**
     * 学生单人打卡情况统计
     * @param  Request $request 参数
     * @return ary              返回数组
     */
    public function userClockChart(Request $request)
    {
        $uid = intval($request->param('uid'));
        $classId = intval($request->param('classId'));
        // 查询数据 打卡数据
        $user_clock = new User_clock;
        $data = $user_clock->field('uid, clock_at, clock_type')->where('uid', $uid)->where('class_id', $classId)->select();
        $xData = [];
        // 非空判断
        if ($data && count($data) != 0) {
            foreach ($data as $k => $v) {
                $xData[] = date('Y-m-d', $v['clock_at']);
                $isNot[$k] = 0;
                $isAll[$k] = 0;
                $isLate[$k] = 0;
                if ($v['clock_type'] == 1) {
                    $isAll[$k] = 1;
                } else if ($v['clock_type'] == 3) {
                    $isNot[$k] = 1;
                } else if ($v['clock_type'] == 2) {
                    $isLate[$k] = 1;
                }
            }
        } else {
            // 当所有数据为空，显示空数据
            // 构造数据 y轴商品名称 x轴三个数据
            $isNot = [];
            $isAll = [];
            $isLate = [];
            foreach ($data as $k => $v) {
                $isNot[$k] = 0;
                $isAll[$k] = 0;
                $isLate[$k] = 0;
            }
        }
        // dump($data);die;
        // y轴数据
        $data = array(
            'data' => $isAll,
            'name' => '正常打卡',
        );
        $data2 = array(
            'data' => $isNot,
            'name' => '旷课打卡',
        );
        $data3 = array(
            'data' => $isLate,
            'name' => '迟到打卡',
        );
        $seriesData = array();
        array_push($seriesData, $data);
        array_push($seriesData, $data2);
        array_push($seriesData, $data3);
        $title = '打卡情况';
        $clockData = array(
            'seriesData' => $seriesData,
            'title' => $title,
            'xData' => $xData,
        );
        return json($clockData);
    }
}
