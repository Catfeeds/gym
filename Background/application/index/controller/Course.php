<?php
namespace app\index\controller;

use \think\Controller;
use \think\File;
use \think\Request;
use \think\Session;
use \think\Db;
use app\index\model\User;

class Course extends Controller
{
    /**
     * 课程列表信息
     * @return ary 返回值
     */
    public function courselist()
    {
        $courseList = Db::name('course')->field('course_id, course_name, course_price, course_period, created_at, status, sort')->where('status', '<>', 3)->select();
        if ($courseList) {
            foreach ($courseList as &$info) {
                $info['course_mem'] = Db::name('user_course')->where('course_id', $info['course_id'])->where('status', '<>', 4)->field('status')->count();
            }
        } else {
            $courseList = [];
        }
        $this->assign('courseList', $courseList);
        return $this->fetch();
    }

    /**
     * 课程添加页面
     *
     * @return html
     */
    public function courseadd()
    {
        return $this->fetch();
    }

    /**
     * 添加课程
     *
     * @return void
     */
    public function addCourse()
    {
        $course['course_name'] = htmlspecialchars(request()->param('course_name'));
        $course['course_price'] = request()->param('course_price');
        $course['course_period'] = intval(request()->param('course_period'));
        $course['status'] = intval(request()->param('course_active'));
        $course['sort'] = intval(request()->param('course_sort'));
        $course['created_at'] = time();
        $insert = Db::name('course')->insert($course);
        if ($insert) {
            return objReturn(0, '新增课程成功');
        } else {
            return objReturn(400, '新增课程失败');
        }
    }

    /**
     * 课程修改页面
     * @return html 页面
     */
    public function courseedit()
    {
        $courseId = intval(request()->param('cid'));
        $courseInfo = Db::name('course')->where('course_id', $courseId)->where('status', '<>', 3)->field('course_name, course_price, course_period, sort, status')->find();
        $this->assign('courseId', $courseId);
        $this->assign('courseInfo', $courseInfo);
        return $this->fetch();
    }

    /**
     * 修改课程信息
     * @return ary 修改结果
     */
    public function editCourse()
    {
        $courseId = intval(request()->param('course_id'));
        $course['course_name'] = htmlspecialchars(request()->param('course_name'));
        $course['course_price'] = request()->param('course_price');
        $course['course_period'] = intval(request()->param('course_period'));
        $course['status'] = intval(request()->param('course_active'));
        $course['sort'] = intval(request()->param('course_sort'));
        $course['update_by'] = Session::get('adminId');
        $course['update_at'] = time();
        $insert = Db::name('course')->where('course_id', $courseId)->update($course);
        if ($insert) {
            return objReturn(0, '课程修改成功');
        } else {
            return objReturn(400, '课程修改失败');
        }
    }

    /**
     * 更改课程展示状态为启用
     * 
     * @return void
     */
    public function activeCourse()
    {
        $courseId = intval(request()->param('courseId'));
        $where['status'] = 1;
        $where['update_at'] = time();
        $where['update_by'] = Session::get('adminId');
        // 调用公共函数，参数true为更新
        $update = Db::name('course')->where('course_id', $courseId)->update($where);
        if ($update) {
            return objReturn(0, '课程状态变更成功');
        } else {
            return objReturn(400, '课程状态变更失败');
        }
    }

    /**
     * 更改课程展示状态为不启用
     * 
     * @return void
     */
    public function closeCourse()
    {
        $courseId = intval(request()->param('courseId'));
        $where['status'] = 2;
        $where['update_at'] = time();
        $where['update_by'] = Session::get('adminId');
        // 调用公共函数，参数true为更新
        $update = Db::name('course')->where('course_id', $courseId)->update($where);
        if ($update) {
            return objReturn(0, '课程状态变更成功');
        } else {
            return objReturn(400, '课程状态变更失败');
        }
    }

    /**
     * 删除课程功能
     * 
     * @return void
     */
    public function delCourse()
    {
        $courseId = request()->param('cid');
        $update = Db::name('course')->where('course_id', $courseId)->update(['status' => 3, 'update_at' => time()]);
        if ($update) {
            return objReturn(0, '课程删除成功!');
        } else {
            return objReturn(400, '课程删除失败!');
        }
    }

    /**
     * 课程包含的人员信息
     *
     * @return html
     */
    public function coursedetail()
    {
        $courseId = intval(request()->param('cid'));
        $courseName = Db::name('course')->where('course_id', $courseId)->value('course_name');
        $user = new User;
        $field = "u.uid, u.user_name, u.user_gender, u.user_nickname, u.user_avatar_url, u.user_city, u.user_province, u.user_country, u.user_mobile, u.user_birth, u.status, uc.course_left_times, uc.start_at, uc.end_at, uc.status as uc_status";
        $courseUserList = $user->alias('u')->join('gym_user_course uc', 'u.uid = uc.uid', 'RIGHT')->where('u.status', '<>', 3)->where('u.user_type', 1)->where('uc.course_id', $courseId)->where('uc.status', '<>', 4)->field($field)->select();
        $courseUserList = $courseUserList ? collection($courseUserList)->toArray() : [];
        $this->assign('courseUserList', $courseUserList);
        $this->assign('courseId', $courseId);
        $this->assign('courseName', $courseName);
        return $this->fetch();
    }

    /**
     * 删除课程会员
     *
     * @return void
     */
    public function delCourseMember()
    {
        $courseId = intval(request()->param('courseId'));
        $uid = intval(request()->param('uid'));
        $update = Db::name('user_course')->where('course_id', $courseId)->where('uid', $uid)->update(['status' => 4, 'update_at' => time(), 'update_by' => Session::get('adminId')]);
        if ($update) {
            return objReturn(0, '课程成员删除成功');
        } else {
            return objReturn(400, '课程成员删除失败');
        }
    }

}
