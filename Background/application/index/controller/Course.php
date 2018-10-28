<?php
namespace app\index\controller;

use \think\Controller;
use \think\File;
use \think\Request;
use \think\Session;
use \think\Db;

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
        $request = Request::instance();
        $courseId = intval($request->param('course_id'));
        // 调用公共函数
        $courseData = getCourseById($courseId, false);
        if ($courseData || count($courseData) != 0) {
            $courseData = $courseData;
        } else {
            $courseData = null;
        }
        $subject = new Subject;
        $subjectData = $subject->field('subject_id,subject_name')->select();
        $this->assign('subjectData', $subjectData);
        // dump($courseData);die;
        $this->assign('courseData', $courseData);
        return $this->fetch();
    }

    /**
     * 修改课程信息
     * @return ary 修改结果
     */
    public function editCourse()
    {
        $request = Request::instance();
        $update['course_id'] = intval($request->param('course_id'));
        $update['course_name'] = htmlspecialchars($request->param('course_name'));
        $update['course_brief'] = htmlspecialchars($request->param('course_brief'));
        $update['course_price'] = $request->param('course_price');
        $update['course_period'] = intval($request->param('course_period'));
        $update['course_times'] = intval($request->param('course_times'));
        $update['status'] = intval($request->param('course_active'));
        $update['created_at'] = time();
        $update['update_at'] = time();
        $update['sort'] = intval($request->param('course_sort'));
        $update['subject_id'] = intval($request->param('subject_id'));
        $picSrc = $request->param('picsrc');
        $imgUrl = $request->param('img_url');
        // 是否为空
        if (!empty($picSrc)) {
            $source = $picSrc;
            // 处理图片路径
            $temp = explode(',', $picSrc);
            $srcAry = [];
            foreach ($temp as &$desc) {
                $temp2 = explode(':', $desc);
                $srcAry[] = $temp2[0];
            }
            // dump($srcAry);die;
            $src = '';
            // 遍历数组移动目录图片
            foreach ($srcAry as $key => $value) {
                $word = DS . 'course';
                // 新的路径
                $strTemp = substr_replace($value, $word, 10, 4);
                // 创建文件夹
                $str3 = substr($strTemp, 0, 26);
                if (!is_dir(PUBLIC_PATH . $str3)) {
                    mkdir(PUBLIC_PATH . $str3);
                }
                // 框架应用根目录/public/course/目录
                $destination = PUBLIC_PATH . $strTemp;
                $sou = PUBLIC_PATH . $value;
                // 拷贝文件到指定目录
                $res = copy($sou, $destination);
            }
            // 新文件路径
            $courseDesc = '';
            foreach ($temp as $k => $v) {
                $word = DS . 'course';
                $courseDesc .= DS . substr_replace($v, $word, 10, 4) . ',';
            }
            $update['course_desc'] = $imgUrl . rtrim($courseDesc, ",");
        } else {
            $update['course_desc'] = rtrim($imgUrl, ",");
        }
        // 调用公共函数，参数true为更新
        $new = saveData('course', $update, true);
        if ($new) {
            return objReturn(0, '修改成功');
        } else {
            return objReturn(400, '修改失败');
        }
    }

    /**
     * 更改展示状态为启用
     * @param  Request $request 参数
     * @return ary           返回结果
     */
    public function startCourse(Request $request)
    {
        $where['course_id'] = $request->param('id');
        $where['status'] = 1;
        $where['update_at'] = time();
        // 调用公共函数，参数true为更新
        $update = saveData('course', $where, true);
        if ($update) {
            return objReturn(0, '展示成功');
        } else {
            return objReturn(400, '展示失败');
        }
    }

    /**
     * 更改展示状态为不启用
     * @param  Request $request 参数
     * @return ary           返回结果
     */
    public function stopCourse(Request $request)
    {
        $where['course_id'] = $request->param('id');
        $where['status'] = 2;
        $where['update_at'] = time();
        // 调用公共函数，参数true为更新
        $update = saveData('course', $where, true);
        if ($update) {
            return objReturn(0, '停用成功');
        } else {
            return objReturn(400, '停用失败');
        }
    }

    /**
     * 删除课程功能
     * @param  Request $request 参数
     * @return ary           返回结果
     */
    public function delCourse(Request $request)
    {
        $courseId = $request->param('id');
        $del['status'] = 3;
        $del['update_at'] = time();
        $class['status'] = 3;

        $cu['update_at'] = time();
        $cu['update_by'] = Session::get('admin_id');
        $cu['status'] = 3;
        $course = new CourseDb;
        $classes = new Classes;
        $classIdArr = $classes->field('class_id')->where('course_id', $courseId)->where('status', '<>', 3)->select();
        if (!$classIdArr) {
            $delCourse = $course->where('course_id', $courseId)->update($del);
            if ($delCourse) {
                return objReturn(0, '删除成功!');
            } else {
                return objReturn(400, '删除失败!');
            }
        } 
        //先构造一维数组
        // $idArr = [];
        foreach ($classIdArr as &$id) {
            $idArr[] = $id['class_id'];
        }
        // 开启事务
        Db::startTrans();
        // 事务
        try {
            $res1 = Db::name('course')->where('course_id', $courseId)->update($del); // 课程信息
            $res2 = Db::name('classes')->where('course_id', $courseId)->update($class); // 课程对应的班级信息
            $res3 = Db::name('classes_user')->where('class_id', 'in', $idArr)->update($cu); //学生班级课程信息
            // 提交事务
            if (!$res1 || !$res2 || !$res3) {
                throw new \Exception("Data Not Insert");
            }
            // 执行提交操作
            Db::commit();
            return objReturn(0, '删除成功！');
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return objReturn(400, '删除失败123！');
        }
    }

    /**
     * 课程对应的班级信息
     *
     * @return ary 返回值
     */
    public function coursedetail()
    {
        $request = Request::instance();
        $courseId = intval($request->param('course_id'));
        $class = new Classes;
        $courseClassData = $class->alias('a')->join('teacher t', 'a.teacher_id = t.teacher_id', 'LEFT')->field('a.class_id,a.class_name,a.class_time,a.class_day,a.status as class_status,t.teacher_name,t.status as teacher_status,t.avatar_url')->where('a.course_id', $courseId)->where('a.status', '<>', 3)->select();
        // 非空判断
        if ($courseClassData && count($courseClassData) != 0) {
            $courseClassData = collection($courseClassData)->toArray();
            $classes_user = new Classes_user;
            // 上课时间与班级人数
            foreach ($courseClassData as &$class) {
                $class['class_day'] = convertDay($class['class_day']);
                $class['class_stu_num'] = $classes_user->where('class_id', $class['class_id'])->where('status', 1)->count('uid');
            }
        }
        // dump($courseClassData);die;
        $this->assign('courseClassData', $courseClassData);
        return $this->fetch();
    }

}
