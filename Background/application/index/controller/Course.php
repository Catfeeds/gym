<?php
namespace app\index\controller;

use \think\Controller;
use \think\File;
use \think\Request;
use \think\Session;
use \think\Db;
use app\index\model\Admin;
use app\index\model\Classes;
use app\index\model\Classes_user;
use app\index\model\Course as CourseDb;
use app\index\model\Subject;
use app\index\model\Teacher;
use app\index\model\User;

class Course extends Controller
{
    /**
     * 课程列表信息
     * @return ary 返回值
     */
    public function courselist()
    {
        $course = new CourseDb;
        $courseData = $course->field('course_id, course_name, course_brief, course_price, course_period, course_times, created_at, status, sort')->where('status', '<>', 3)->select();
        // dump($courseData);die;
        if (!$courseData || count($courseData) == 0) {
            $courseData = null;
        }
        $this->assign('courseData', $courseData);
        return $this->fetch();
    }

    /**
     * 课程添加页面
     *
     * @return html
     */
    public function courseadd()
    {
        $subject = new Subject;
        $subjectData = $subject->field('subject_id,subject_name')->select();
        $this->assign('subjectData', $subjectData);
        return $this->fetch();
    }

    /**
     * 上传图片(15张)
     *
     * @param Request $request 参数
     * @return string  图片路径+文件名
     */
    public function addMultiPic(Request $request)
    {
        $file = request()->file('file');
        // 移动到框架应用根目录/static/imgTemp/目录下
        $info = $file->move(PUBLIC_PATH . DS . 'static' . DS . 'imgTemp');
        if ($info) {
            $str = $info->getSaveName();
            $src = 'static' . DS . 'imgTemp' . DS . $str;
            $getInfo = $info->getInfo();
            //获取图片的原名称
            $name = $getInfo['name'];
            $name = substr($name, 0, -4);
            // 拼接图片顺序
            $picSrc = $src . ':' . $name;
            // return json($picSrc);
            // 判断文件名是否数字
            if (is_numeric($name)) {
                return json($picSrc);
            } else {
                return 400;
            }
        }
    }

    /**
     * 添加课程功能
     *
     * @return ary  添加结果
     */
    public function addCourse()
    {
        $request = Request::instance();
        $add['course_name'] = htmlspecialchars($request->param('course_name'));
        $add['course_brief'] = htmlspecialchars($request->param('course_brief'));
        $add['course_price'] = $request->param('course_price');
        $add['course_period'] = intval($request->param('course_period'));
        $add['course_times'] = intval($request->param('course_times'));
        $add['status'] = intval($request->param('course_active'));
        $add['subject_id'] = intval($request->param('subject_id'));
        $add['sort'] = intval($request->param('course_sort'));
        $add['created_at'] = time();
        $picSrc = $request->param('picsrc');
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
            $courseDesc = '';
            // 新文件路径
            foreach ($temp as $k => $v) {
                $word = DS . 'course';
                $courseDesc .= DS . substr_replace($v, $word, 10, 4) . ',';
            }
            $add['course_desc'] = rtrim($courseDesc, ",");
            // 调用公共函数，参数false为新增
            $insert = saveData('course', $add, false);
            if ($insert) {
                return objReturn(0, '保存成功');
            } else {
                return objReturn(400, '保存失败');
            }
        } else {
            return objReturn(400, '保存失败,请上传图片！');
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

    /**
     * 正常打卡功能
     *
     * @param  Request $request 参数
     * @return ary              返回值
     */
    public function clockIn(Request $request)
    {
        $where['class_id'] = intval($request->param('classId'));
        $uid = $request->param('uid');
        $where['clock_by'] = Session::get('admin_id');
        $where['clock_at'] = strtotime($request->param('clockAt'));
        $where['clock_type'] = intval($request->param('clockType'));

        $type = intval($request->param('type'));
        // type 0为单个打卡
        if ($type == 0) {
            $where['uid'] = $uid;
            //用户打卡的数组 其中包含uid, clock_by, clock_at, class_id, clock_type
            $clockArr[] = $where;
        }
        // type 1为批量打卡
        if ($type == 1) {
            $uid = substr($uid, 0, strlen($uid) - 1);
            $uidArr = explode("*", $uid);
            // 用户打卡的数组 其中包含uid, clock_by, clock_at, class_id, clock_type
            foreach ($uidArr as $key => $value) {
                $temp = [];
                $temp['class_id'] = $where['class_id'];
                $temp['clock_by'] = $where['clock_by'];
                $temp['clock_at'] = $where['clock_at'];
                $temp['clock_type'] = $where['clock_type'];
                $temp['uid'] = intval($value);
                $clockArr[] = $temp;
            }
        }
        // dump($clockArr);die;
        // 调用公共函数
        $update = makeClock($clockArr);
        if (is_bool($update)) {
            return objReturn(0, '打卡成功！');
        } else {
            if (is_int($update)) {
                if ($update == 603) {
                    $msg = '今日不是打卡时间';
                } else if ($update == 601) {
                    $msg = '今日已打卡';
                }
            }else{
                $msg = '打卡失败，请检查';
            }
            return objReturn(400, $msg);
        }
    }

    /**
     * 上传excel文件
     * @param  Request $request 参数
     * @return ary           返回信息
     */
    public function uploadExcel(Request $request)
    {
        $file = request()->file('file');
        // 是否存在session
        if (Session::has('excelPath')) {
            // 删除session信息
            Session::delete('excelPath');
        }
        // 移动到框架应用根目录/static/excel/目录下
        $path = 'static' . DS . 'excel' . DS . 'import' . DS;
        $info = $file->move(PUBLIC_PATH . $path);
        if ($info) {
            $str = $info->getSaveName();
            $src = $path . $str;
            // 存路径名到session
            Session::set('excelPath', $src);
            return objReturn(0, '上传成功！', $src);
        }
        return objReturn(400, '上传失败！');
    }

    /**
     * 导入excel文件 调用Excel.php的getExcelData函数
     * @param  Request $request 参数
     * @return ary           导入结果
     */
    public function importExcel(Request $request)
    {
        $type = intval($request->param('type'));
        // 判断是否上传了excel文件
        if (Session::has('excelPath')) {
            // 获取excel文件路径
            $path = Session::get('excelPath');
            $filename = PUBLIC_PATH . $path;
            // 文件格式
            $exts = 'xlsx';
            $excel = new Excel;
            // type为1时为导出用户信息 2为教师信息
            $res = $excel->getExcelData($filename, $exts, $type);
            return $res;
        } else {
            return objReturn(400, '导入失败！');
        }
    }

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
// ***************************

    /**
     * 教师列表
     * @return array
     */
    public function teacher()
    {
        $teacher_db = new Teacher;
        $teacherData = $teacher_db->field('teacher_id,teacher_name,teacher_phone,teacher_gender,teacher_birth,created_at,status')->where('status', '<>', 4)->select();
        // 非空判断
        if (!$teacherData || count($teacherData) == 0) {
            $teacherData = [];
        } else {
            $class = new Classes;
            $teacherData = collection($teacherData)->toArray();
            foreach ($teacherData as $k => $v) {
                $teacherClass = $class->where('teacher_id', $v['teacher_id'])->field('class_name, class_time, class_day')->select();
                if (!$teacherClass || count($teacherClass) == 0) {
                    $teacherData[$k]['teacher_class'] = [];
                    continue;
                }
                $teacherClass = collection($teacherClass)->toArray();
                $teacherClassStr = '';
                foreach ($teacherClass as $ke => $va) {
                    $day = convertDay($va['class_day']);
                    $teacherClassStr .= $va['class_name'] . ' ' . $day . ' ' . $va['class_time'] . '*';
                }
                $teacherClassStr = substr($teacherClassStr, 0, strlen($teacherClassStr) - 1);
                // 处理课程信息
                $teacherData[$k]['teacher_class'] = explode('*', $teacherClassStr);
            }
        }
        // dump($teacherData);die;
        $this->assign('data', $teacherData);
        return $this->fetch();
    }

    /**
     * 教师添加
     * @param   string  $teacher_name
     * @param   int     $teacher_phone
     * @param   string  $teacher_birth
     * @param   int     $teacher_gender
     * @return  JSON   $result 添加结果
     */
    public function addteacher(Request $request)
    {
        if ($request->isPost()) {
            $teacher_db = new Teacher;
            $data = $request->post();
            $data['created_at'] = time();
            // 默认新增未认证
            $data['status'] = 1;
            $result = $teacher_db->insert($data);
            if ($result) {
                return objReturn(0, '添加教师成功!');
            } else {
                return objReturn(400, '添加教师失败!');
            }
        } else {
            $this->assign('date', date('Y-m-d', time()));
            return $this->fetch();
        }
    }

    /**
     * 教师添加
     * @param   int     $teacher_id
     * @param   string  $teacher_name
     * @param   int     $teacher_phone
     * @param   string  $teacher_birth
     * @param   int     $teacher_gender
     * @return  JSON   $result 修改结果
     */
    public function editteacher(Request $request)
    {
        $teacher_db = new Teacher;
        $teacher_id = $request->param('teacher_id');
        if ($request->isPost()) {
            $data = $request->except(['teacher_id'], 'post');
            $result = $teacher_db->where(['teacher_id' => $teacher_id])->update($data);
            if ($result) {
                return objReturn(0, '修改教师信息成功!');
            } else {
                return objReturn(400, '修改教师信息失败!');
            }
        } else {
            $teacher_data = $teacher_db->where(['teacher_id' => $teacher_id])->find();
            $this->assign('data', $teacher_data);
            $this->assign('date', date('Y-m-d', time()));
            return $this->fetch();
        }
    }

    /**
     * 教师在职改为离职
     * @param   int     $teacher_id
     * @return  JSON            结果
     */
    public function stopTeacher(Request $request)
    {
        $update['teacher_id'] = intval($request->param('teacher_id'));
        $update['status'] = 3;
        // 调用公共函数，参数true为更新
        $new = saveData('teacher', $update, true);
        if ($new) {
            return objReturn(0, '更改成功!');
        } else {
            return objReturn(400, '更改失败!');
        }
    }

    /**
     * 教师删除
     * @param   int     $teacher_id
     * @return  JSON   $result 删除结果
     */
    public function deleteTeacher(Request $request)
    {
        $update['teacher_id'] = intval($request->param('teacher_id'));
        $update['status'] = 4;
        // 调用公共函数，参数true为更新
        $new = saveData('teacher', $update, true);
        if ($new) {
            return objReturn(0, '删除成功!');
        } else {
            return objReturn(400, '删除失败!');
        }
    }

    /**
     * 将离职改为在职
     * @param  Request $request 参数
     * @return ary              结果
     */
    public function restartTeacher(Request $request)
    {
        $update['teacher_id'] = intval($request->param('teacher_id'));
        $update['status'] = 2;
        // 调用公共函数，参数true为更新
        $new = saveData('teacher', $update, true);
        if ($new) {
            return objReturn(0, '更改成功!');
        } else {
            return objReturn(400, '更改失败!');
        }
    }

    /**
     * 教师对应的班级信息
     * @return ary      返回值
     */
    public function teacherdetail()
    {
        $request = Request::instance();
        $teacherId = intval($request->param('teacher_id'));
        $class = new Classes;
        $classTeacherData = $class->alias('a')->join('course c', 'a.course_id = c.course_id', 'LEFT')->field('a.class_id,a.class_name,a.class_time,a.class_day,a.status as class_status,c.course_name,c.course_times,c.course_period,course_brief,c.status')->where('a.teacher_id', $teacherId)->select();
        if ($classTeacherData && count($classTeacherData) != 0) {
            $classTeacherData = collection($classTeacherData)->toArray();
            // 对日期做处理
            foreach ($classTeacherData as &$info) {
                $info['class_day'] = convertDay($info['class_day']);
            }
        }
        $this->assign('classTeacherData', $classTeacherData);
        return $this->fetch();
    }

    /**
     * 科目列表信息
     * @return  array
     */
    public function subject()
    {
        $subjectDb = new Subject;
        $subjectData = $subjectDb->where('status', '<>', 3)->select();
        $this->assign('data', $subjectData);
        return $this->fetch();
    }

    /**
     * 添加科目
     * @param   subject_name 科目名称
     * @return  result 添加结果
     */
    public function addsubject(Request $request)
    {
        if ($request->isPost()) {
            $subjectDb = new Subject;
            $data['subject_name'] = $request->param('subject_name');
            $data['created_at'] = time();
            $result = $subjectDb->insert($data);
            if ($result) {
                return objReturn(0, '科目添加成功!');
            } else {
                return objReturn(400, '科目添加失败!');
            }
        } else {
            return $this->fetch();
        }
    }

    /**
     * 编辑科目
     * @param   subject_id   科目ID
     * @param   subject_name 科目名称
     * @return  result 编辑结果
     */
    public function editsubject(Request $request)
    {
        $subjectDb = new Subject;
        $subject_id = $request->param('subject_id');
        if ($request->isPost()) {
            $data['subject_name'] = $request->param('subject_name');
            $data['update_at'] = time();
            $result = $subjectDb->where(['subject_id' => $subject_id])->update($data);
            if ($result) {
                return objReturn(0, '科目修改成功!');
            } else {
                return objReturn(400, '科目修改失败!');
            }
        } else {
            $subjectData = $subjectDb->where(['subject_id' => $subject_id])->find();
            $this->assign('data', $subjectData);
            return $this->fetch();
        }
    }

    /**
     * 删除科目
     * @param   subject_id   科目ID
     * @return  result 删除结果
     */
    public function deleteSubject(Request $request)
    {
        $subject = new Subject;
        $course = new CourseDb;
        $classes = new Classes;
        $subjectId = $request->param('subject_id');
        $del['status'] = 3;
        $courseIdArr = $course->field('course_id')->where('subject_id', $subjectId)->select();
        // 没有课程id
        if (!$courseIdArr) {
            $result = $subject->where('subject_id', $subjectId)->update($del);
            if ($result) {
                return objReturn(0, '删除成功!');
            } else {
                return objReturn(400, '删除失败1!');
            }
        } else {
            $courseIdArr = collection($courseIdArr)->toArray();
            foreach ($courseIdArr as &$course) {
                $courseIds[] = $course['course_id'];
            }
            $classIdArr = $classes->field('class_id')->where('course_id', 'in', $courseIds)->where('status', '<>', 3)->select();
            if ($classIdArr) {
                $classIdArr = collection($classIdArr)->toArray();
                // 有班级id 先构造一维数组
                foreach ($classIdArr as &$id) {
                    $idArr[] = $id['class_id'];
                }
            }
        }
        $class['status'] = 3;

        $cu['update_at'] = time();
        $cu['update_by'] = Session::get('admin_id');
        $cu['status'] = 3;
        // 没有班级id
        if (!$classIdArr) {
            // 开启事务
            Db::startTrans();
            // 事务
            try {
                $res1 = Db::name('subject')->where('subject_id', $subjectId)->update($del); // 科目信息
                $res2 = Db::name('course')->where('course_id', $courseId)->update($del); // 课程信息
                // 提交事务
                if (!$res1 || !$res2) {
                    throw new \Exception("数据未更新！");
                }
                // 执行提交操作
                Db::commit();
                return objReturn(0, '删除成功！');
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return objReturn(400, '删除失败2！');
            }
        } else {
            // $res2 = Db::name('classes')->where('course_id', 'in',$courseIds)->update($class);
            // dump($res2);die;
            // 开启事务
            Db::startTrans();
            // 事务
            try {
                $res4 = Db::name('subject')->where('subject_id', $subjectId)->update($del); // 科目信息
                $res1 = Db::name('course')->where('course_id', 'in', $courseIds)->update($del); // 课程信息
                $res2 = Db::name('classes')->where('course_id', 'in', $courseIds)->update($class); // 课程对应的班级信息
                $res3 = Db::name('classes_user')->where('class_id', 'in', $idArr)->update($cu); //班级学生课程信息
             
                // 提交事务
                if (!$res1 || !$res2 || !$res3 || !$res4) {
                    throw new \Exception("Data Not Insert");
                }
                // 执行提交操作
                Db::commit();
                return objReturn(0, '删除成功！');
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return objReturn(400, '删除失败3！');
            }
        }
    }

    /**
     * 教师对应的班级信息列表
     * @return ary 班级信息
     */
    public function classlist()
    {
        // 教师权限-只看对应的班级信息
        // $class     = new Classes;
        // $classData = $class->alias('a')->join('course c', 'a.course_id = c.course_id', 'LEFT')->join('teacher t', 'a.teacher_id = t.teacher_id', 'LEFT')->field('a.class_id,a.class_name,a.status,a.class_time,a.class_day,t.teacher_id,t.teacher_name,c.course_name')->where('a.status', '<>', 3)->select();

        $adminId = Session::get('admin_id');
        $admin = new Admin;
        $teacherId = $admin->where('id', $adminId)->value('admin_teacher');
        // 教师对应的班级信息
        $teacher = new Teacher;
        if ($teacherId != 0) {
            $classData = $teacher->alias('a')->join('classes b', 'a.teacher_id = b.teacher_id', 'RIGHT')->join('course c', 'b.course_id = c.course_id', 'LEFT')->join('subject d', 'c.subject_id = d.subject_id')->field('a.teacher_id,a.teacher_name,b.class_id,b.class_name,b.status,b.class_time,b.class_day,c.course_name,d.subject_id,d.subject_name')->where('b.status', '<>', 3)->where('a.teacher_id', $teacherId)->select();
        } else {
            $classData = $teacher->alias('a')->join('classes b', 'a.teacher_id = b.teacher_id', 'RIGHT')->join('course c', 'b.course_id = c.course_id', 'LEFT')->join('subject d', 'c.subject_id = d.subject_id')->field('a.teacher_id,a.teacher_name,b.class_id,b.class_name,b.status,b.class_time,b.class_day,c.course_name,d.subject_id,d.subject_name')->where('b.status', '<>', 3)->select();
        }
        // dump($classData);die;
        $this->assign('classData', $classData);
        return $this->fetch();
    }

    /**
     * 添加班级信息
     * @return ary 返回数据
     */
    public function classadd()
    {
        // 教师与课程信息
        $teacher = new Teacher;
        $teacherData = $teacher->field('teacher_id,teacher_name')->where('status', '<>', 4)->select();
        $this->assign('teacherData', $teacherData);
        $subject = new Subject;
        $subjectData = $subject->field('subject_id,subject_name')->select();
        $this->assign('subjectData', $subjectData);
        return $this->fetch();
    }

    /**
     * 添加班级信息功能
     * @return ary 返回数据
     */
    public function addClasses(Request $request)
    {
        $add['class_name'] = htmlspecialchars($request->param('class_name'));
        $add['teacher_id'] = intval($request->param('teacher_id'));
        $add['course_id'] = intval($request->param('course_id'));
        $add['status'] = intval($request->param('class_active'));
        $add['created_at'] = time();
        $add['class_day'] = $request->param('class_day');
        $start = $request->param('countTimestart');
        $end = $request->param('countTimeend');
        $add['class_time'] = $start . '-' . $end;
        // 先判断班级名是否重复
        $class = new Classes;
        $exist = $class->field('class_id')->where('class_name', $add['class_name'])->find();
        if ($exist) {
            return objReturn(400, '添加失败！班级名称重复！');
            exit;
        }
        // 调用公共函数，参数false为新增
        $insert = saveData('classes', $add, false);
        if ($insert) {
            return objReturn(0, '添加成功！');
        } else {
            return objReturn(400, '添加失败！');
        }
    }

    /**
     * 编辑班级信息
     * @return ary 返回数据
     */
    public function classedit()
    {
        $request = Request::instance();
        $classId = intval($request->param('class_id'));
        $class = new Classes;
        // 连表查询
        $classData = $class->alias('a')->join('course b', 'a.course_id = b.course_id', 'LEFT')->join('subject c', 'b.subject_id = c.subject_id', 'LEFT')->field('a.class_id,a.teacher_id,a.course_id,a.class_name,a.status,a.class_day,a.class_time,b.course_name,c.subject_id')->where('class_id', $classId)->where('a.status', '<>', 3)->select();
        if ($classData && count($classData) != 0) {
            $classData = collection($classData)->toArray();
            // 上课时间
            foreach ($classData as &$class) {
                $temp = explode('-', $class['class_time']);
                $class['time1'] = $temp[0];
                $class['time2'] = $temp[1];
            }
            $classData = $classData[0];
        }
        $this->assign('classData', $classData);
        // 教师与课目信息
        $teacher = new Teacher;
        $teacherData = $teacher->field('teacher_id,teacher_name')->where('status', '<>', 3)->select();
        $this->assign('teacherData', $teacherData);
        $subject = new Subject;
        $subjectData = $subject->field('subject_id,subject_name')->select();
        $this->assign('subjectData', $subjectData);
        return $this->fetch();
    }

    /**
     * 编辑班级
     * @param  Request $request 参数
     * @return ary              数组
     */
    public function editClasses(Request $request)
    {
        $update['class_id'] = intval($request->param('class_id'));
        $update['class_name'] = htmlspecialchars($request->param('class_name'));
        $update['teacher_id'] = intval($request->param('teacher_id'));
        $update['course_id'] = intval($request->param('course_id'));
        $update['status'] = intval($request->param('class_active'));
        $update['class_day'] = $request->param('class_day');
        $start = $request->param('countTimestart');
        $end = $request->param('countTimeend');
        $update['class_time'] = $start . '-' . $end;
        // 先判断班级名是否重复
        $class = new Classes;
        $exist = $class->field('class_id')->where('class_name', $update['class_name'])->where('class_name', '<>', $update['class_name'])->find();
        if ($exist) {
            return objReturn(400, '修改失败！班级名称重复！');
            exit;
        }
        // 调用公共函数，参数true为更新
        $new = saveData('classes', $update, true);
        if ($new) {
            return objReturn(0, '修改成功！');
        } else {
            return objReturn(400, '修改失败！');
        }
    }

    /**
     * 班级对应的学生详情
     * @return ary              数组
     */
    public function classdetail()
    {
        $request = Request::instance();
        $classId = intval($request->param('class_id'));
        $this->assign('classId', $classId);
        // $username     = new User;
        // $userData = $user->field('uid,username,user_gender,stu_no,grade,birth')->where('class_id', $classId)->where('status', '<>', 3)->select();

        $classes_user = new Classes_user;
        $userData = $classes_user->alias('a')->join('user u', 'a.uid = u.uid', 'LEFT')->field('a.uid, u.username, u.user_gender, u.stu_no, u.grade, u.birth, a.renew_times, a.course_left_times')->where('a.class_id', $classId)->where('a.status', '<>', 3)->select();
        // 学生当前课程的旷课统计
        $userData = collection($userData)->toArray();
        foreach ($userData as &$info) {
            $info['kuangke'] = Db::name('user_clock')->where('uid', $info['uid'])->where('clock_type', 3)->where('class_id', $classId)->count();
            $info['daka'] = Db::name('user_clock')->where('uid', $info['uid'])->where('class_id', $classId)->count();
        }
        $this->assign('userData', $userData);
        return $this->fetch();
    }

    /**
     * 删除班级中的学生功能
     * @param  Request $request 参数
     * @return ary           返回结果
     */
    public function delUser(Request $request)
    {
        $classes_user = new Classes_user;
        $uid = $request->param('id');
        $del['status'] = 3;
        $del['update_at'] = time();
        $del['update_by'] = Session::get('admin_id');
        $delete = $classes_user->where('uid', $uid)->update($del);
        if ($delete) {
            return objReturn(0, '删除成功！');
        } else {
            return objReturn(400, '删除失败！');
        }
    }

    /**
     * 更改展示状态为启用
     * @param  Request $request 参数
     * @return ary           返回结果
     */
    public function startClasses(Request $request)
    {
        $where['class_id'] = $request->param('id');
        $where['status'] = 2;
        // $where['update_at'] = time();
        // 调用公共函数，参数true为更新
        $update = saveData('classes', $where, true);
        if ($update) {
            return objReturn(0, '开班成功！');
        } else {
            return objReturn(400, '开班失败！');
        }
    }

    /**
     * 更改展示状态为不启用
     * @param  Request $request 参数
     * @return ary           返回结果
     */
    public function stopClasses(Request $request)
    {
        $where['class_id'] = $request->param('id');
        $where['status'] = 1;
        // $where['update_at'] = time();
        // 调用公共函数，参数true为更新
        $update = saveData('classes', $where, true);
        if ($update) {
            return objReturn(0, '停用成功！');
        } else {
            return objReturn(400, '停用失败！');
        }
    }

    /**
     * 删除班级功能
     * @param  Request $request 参数
     * @return ary           返回结果
     */
    public function delClasses(Request $request)
    {
        $classId = $request->param('id');
        $del['status'] = 3;

        $cu['update_at'] = time();
        $cu['update_by'] = Session::get('admin_id');
        $cu['status'] = 3;
        // 开启事务
        Db::startTrans();
        // 事务
        try {
            $res1 = Db::name('classes')->where('class_id', $classId)->update($del); //班级信息
            $res2 = Db::name('classes_user')->where('class_id', $classId)->update($cu); //学生班级课程信息
            // 提交事务
            if (!$res1 || !$res2) {
                throw new \Exception("Data Not Insert");
            }
            // 执行提交操作
            Db::commit();
            return objReturn(0, '删除成功！');
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return objReturn(400, '删除失败！');
        }
        // 调用公共函数，参数true为更新
        // $delete = saveData('classes', $del, true);
        // if ($delete) {
        //     return objReturn(0, '删除成功！');
        // } else {
        //     return objReturn(400, '删除失败！');
        // }
    }
}
