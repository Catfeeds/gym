<?php
namespace app\index\controller;

use app\index\model\Admin;
use app\index\model\Clause;
use app\index\model\Course;
use app\index\model\Feedback;
use app\index\model\User;

use \think\Controller;
use \think\Db;
use \think\File;
use \think\Request;
use \think\Session;
use app\index\model\Mini_setting;

class System extends Controller
{
    public function storeset()
    {
        return $this->fetch();
    }

    /**
     * courseset 课程设置
     * @return   ary  返回值
     */
    public function courseset()
    {
        $courseData = null;
        // dump($courseData);die;
        $this->assign('courseData', $courseData);
        return $this->fetch();
    }

    /**
     * @return 小程序-用户协议页面
     */
    public function clause()
    {
        $clause = new Clause();
        $info = $clause->where('idx', 1)->find();
        $this->assign('info', $info);
        return $this->fetch();
    }

    /**
     * 修改小程序-用户协议
     * @return json 修改结果
     */
    public function updateClause(Request $request)
    {
        $clause = new Clause();
        $content['idx'] = 1;
        $content['clause'] = htmlspecialchars($request->param('content'));
        $update = $clause->update($content);
        if ($update) {
            return objReturn(0, '修改成功');
        } else {
            return objReturn(400, '修改失败');
        }
        return json($res);
    }

    /**
     * 小程序设置编辑界面
     * @return  array  小程序基本信息数据
     */
    public function minisetting()
    {
        $mini_setting = new Mini_setting;
        $settingInfo = $mini_setting->where('setting_id', 1)->select();
        $settingInfo = collection($settingInfo)->toArray();
        $settingInfo = $settingInfo[0];
        $this->assign('setting', $settingInfo);
        return $this->fetch();
    }

    /**
     * 插入或更新小程序基本信息
     * @return  result              更新结果
     */
    public function editMiniSetting()
    {
        $mini_setting = new Mini_setting;
        $data['mini_name'] = htmlspecialchars(request()->param('mini_name'));
        $data['service_phone'] = request()->param('service_phone');
        $data['location'] = request()->param('location');
        $data['mini_notice'] = htmlspecialchars(request()->param('notice'));
        $data['share_text'] = htmlspecialchars(request()->param('share_text'));
        $data['store_info'] = htmlspecialchars(request()->param('store_info'));
        $data['update_at'] = time();
        $data['update_by'] = Session::get('adminId');
        $result = $mini_setting->where('setting_id', 1)->update($data);
        if ($result) {
            return objReturn(0, '保存成功!');
        } else {
            return objReturn(400, '保存失败!');
        }
    }

    /**
     * 用户反馈列表
     * @return  array
     */
    public function feedback()
    {
        $feedback = new Feedback;
        $feedbackData = $feedback->alias('a')->join('user n', 'a.uid = n.uid', 'LEFT')->field('a.uid, a.idx, a.message, a.img, a.reply, a.created_at, a.reply_at, a.reply_by, a.status, a.user_type, n.nickname, n.avatar_url, n.username, n.auth_name')->where('a.status', '<>', 3)->select();
        // 管理员信息
        $admin = new Admin;
        $adminData = $admin->field('id, name')->where('status', '<>', 3)->select();
        // 教师信息
        $teacher = new Teacher;
        $teacherList = $teacher->field('teacher_id, teacher_name, avatar_url, nickname')->select();
        $teacherList = $teacherList && count($teacherList) > 0 ? collection($teacherList)->toArray() : [];
        // 非空判断
        if ($feedbackData) {
            $feedbackData = collection($feedbackData)->toArray();
            foreach ($feedbackData as $key => $value) {
                $feedbackData[$key]['created_at'] = date('Y-m-d H:i:s', $value['created_at']);
                $feedbackData[$key]['reply_at'] = empty($value['reply_at']) ? '' : date('Y-m-d H:i:s', $value['reply_at']);
                if ($value['status'] == 2) {
                    foreach ($adminData as $k => $v) {
                        $feedbackData[$key]['name'] = '';
                        if ($value['reply_by'] != 0 && $value['reply_by'] == $v['id']) {
                            $feedbackData[$key]['name'] = $v['name'];
                            break 1;
                        }
                    }
                }
                if ($value['user_type'] == 2 && $teacherList) {
                    foreach ($teacherList as $ke => $va) {
                        if ($va['teacher_id'] == $value['uid']) {
                            $feedbackData[$key]['username'] = $va['teacher_name'];
                            $feedbackData[$key]['avatar_url'] = $va['avatar_url'];
                            $feedbackData[$key]['nickname'] = $va['nickname'];
                            break 1;
                        }
                    }
                }
            }
        } else {
            $feedbackData = [];
        }
        // dump($feedbackData);die;
        $this->assign('data', $feedbackData);
        return $this->fetch();
    }

    /**
     * 用户反馈回复
     * @param  int          ID
     * @param  string       回复内容
     * @return result       回复结果
     */
    public function replyfeedback(Request $request)
    {
        $feedback_db = new Feedback;
        $idx = $request->param('idx');
        $data['reply'] = $request->param('reply');
        // $status        = $request->param('status');
        // if ($status != 0) {
        //     return objReturn(0, '此反馈已处理!');
        // }
        $data['reply_at'] = time();
        $data['reply_by'] = Session::get('admin_id');
        $data['status'] = 2;
        $result = $feedback_db->where(['idx' => $idx])->update($data);
        if ($result) {
            return objReturn(0, '回复成功!');
        } else {
            return objReturn(400, '回复失败!');
        }
    }

    /**
     * 用户信息
     * @return html 页面
     */
    public function userprofile()
    {
        $user = new User;
        // 连表查询
        $userData = $user->field('uid, username, user_gender, stu_no, grade, birth, phone, status')->where('status', '<>', 3)->select();
        if ($userData && count($userData) != 0) {
            $userData = collection($userData)->toArray();
        }
        $this->assign('userData', $userData);
        return $this->fetch();
    }

    /**
     * 添加学生信息界面
     * @return ary 数据
     */
    public function useradd()
    {
        $class = new Classes;
        $classData = $class->field('class_id,class_name')->where('status', '<>', 3)->select();
        $this->assign('classData', $classData);
        $subject = new Subject;
        $subjectData = $subject->field('subject_id,subject_name')->select();
        $this->assign('subjectData', $subjectData);
        return $this->fetch();
    }

    /**
     * 获取科目对应的课程信息
     * @param  Request $request 参数
     * @return ary           返回值
     */
    public function getSubjectCourse(Request $request)
    {
        $subjectId = intval($request->param('subject_id'));
        $course = new Course;
        $courseData = $course->field('course_id,course_name')->where('subject_id', $subjectId)->where('status', '<>', 3)->select();
        if ($courseData) {
            return objReturn(0, 'success', $courseData);
        } else {
            return objReturn(400, '无科目对应的课程信息！');
        }
    }

    /**
     * 获取课程对应的班级信息
     * @param  Request $request 参数
     * @return ary           返回值
     */
    public function getCourseClass(Request $request)
    {
        $courseId = intval($request->param('course_id'));
        $course = new Course;
        // 连表查询
        $classData = $course->alias('a')->join('classes w', 'a.course_id = w.course_id', 'LEFT')->field('a.course_period,a.course_times,w.class_id,w.class_name')->where('a.status', '<>', 3)->where('a.course_id', $courseId)->where('w.course_id', $courseId)->select();
        if ($classData && count($classData) != 0) {
            $classData = collection($classData)->toArray();
            return objReturn(0, 'success', $classData);
        }
        return objReturn(400, '无班级信息！');
    }

    /**
     * 获取课程对应的打卡次数与结束时间
     * @param  Request $request 参数
     * @return ary           返回值
     */
    public function getCourseTimes(Request $request)
    {
        $courseId = intval($request->param('course_id'));
        $course = new Course;
        $timesData = $course->field('course_period,course_times')->where('status', '<>', 3)->where('course_id', $courseId)->select();
        if ($timesData && count($timesData) != 0) {
            $timesData = collection($timesData)->toArray();
            $timesData = $timesData[0];
            return objReturn(0, 'success', $timesData);
        }
        return objReturn(400, '无打卡次数与结束时间信息！');
    }

    /**
     * 添加学生信息功能
     * @return ary 返回数据
     */
    public function addUser(Request $request)
    {
        $add['username'] = htmlspecialchars($request->param('user_name'));
        $add['stu_no'] = $request->param('user_no');
        $add['phone'] = $request->param('user_phone');
        $add['class_id'] = intval($request->param('class_id'));
        $add['user_gender'] = intval($request->param('user_gender'));
        $add['grade'] = intval($request->param('user_grade'));
        $add['birth'] = $request->param('countTimestart');

        $where['class_id'] = $add['class_id'];
        $where['course_left_times'] = intval($request->param('course_left_times'));
        $where['course_end_at'] = strtotime($request->param('countTimeend'));
        $where['created_at'] = time();
        $where['status'] = 1;
        // 开启事务
        Db::startTrans();
        // 事务
        try {
            $res1 = Db::name('user')->insertGetId($add);
            $where['uid'] = $res1;
            $res2 = Db::name('classes_user')->insert($where);
            // 提交事务
            if (!$res1 || !$res2) {
                throw new \Exception("Data Not Insert");
            }
            // 执行提交操作
            Db::commit();
            return objReturn(0, '添加成功！');
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return objReturn(400, '添加失败！');
            // 获取提示信息
            // dump($e->getMessage());
        }
        // 调用公共函数，参数false为新增
        // $new = saveData('classes_user', $where, false);
        // if ($new) {
        //     // 调用公共函数，参数false为新增
        //     $insert = saveData('user', $add, false);
        //     if ($insert) {
        //         return objReturn(0, '添加成功！');
        //     } else {
        //         return objReturn(400, '添加失败！');
        //     }
        // } else {
        //     return objReturn(400, '添加失败！');
        // }
    }

    /**
     * 学生信息修改界面
     * @return ary 返回值
     */
    public function useredit()
    {
        $request = Request::instance();
        $uid = intval($request->param('uid'));
        $user = new User;
        // 连表查询
        $userData = $user->alias('a')->join('classes_user w', 'a.uid = w.uid', 'LEFT')->join('classes n', 'w.class_id = n.class_id', 'LEFT')->join('course c', 'n.course_id = c.course_id', 'LEFT')->join('subject s', 'c.subject_id = s.subject_id')->field('a.uid,a.username,a.user_gender,a.grade,a.stu_no,a.grade,a.birth,a.phone,a.status,w.course_left_times,w.course_end_at,n.class_id,n.class_name,c.course_id,c.course_name,s.subject_name,s.subject_id')->where('a.status', '<>', 3)->where('a.uid', $uid)->select();
        // 非空判断
        if ($userData && count($userData) != 0) {
            $userData = collection($userData)->toArray();
            $userData = $userData[0];
        } else {
            $userData = $user->alias('a')->join('classes_user w', 'a.uid = w.uid', 'LEFT')->field('a.uid,a.username,a.user_gender,a.grade,a.stu_no,a.grade,a.birth,a.phone,a.status,w.course_left_times,w.course_end_at')->where('a.status', '<>', 3)->where('w.status', '<>', 3)->where('a.uid', $uid)->select();
            if ($userData && count($userData) != 0) {
                $userData = collection($userData)->toArray();
                // 数组重组
                foreach ($userData as &$user) {
                    $user['class_id'] = '';
                    $user['class_name'] = '';
                    $user['course_id'] = '';
                    $user['course_name'] = '';
                    $user['subject_id'] = '';
                    $user['subject_name'] = '';
                }
                $userData = $userData[0];
            }
        }
        // dump($userData);die;
        $this->assign('userData', $userData);
        return $this->fetch();
    }

    /**
     * 修改学生信息功能
     * @return ary 返回数据
     */
    public function editUser(Request $request)
    {
        $update['uid'] = intval($request->param('uid'));
        $update['username'] = htmlspecialchars($request->param('user_name'));
        $update['stu_no'] = $request->param('user_no');
        $update['phone'] = $request->param('user_phone');
        $update['user_gender'] = intval($request->param('user_gender'));
        $update['grade'] = intval($request->param('user_grade'));
        $update['birth'] = $request->param('countTimestart');

        $uid = $update['uid'];
        $where['update_at'] = time();
        $where['status'] = 1;
        // 调用公共函数，参数true为更新
        $new = saveData('user', $update, true);
        if ($new) {
            return objReturn(0, '修改成功！');
        } else {
            return objReturn(400, '修改失败！');
        }
    }

    /**
     * 学生对应的家长详情
     * @return ary 返回值
     */
    public function userdetail()
    {
        $request = Request::instance();
        $uid = intval($request->param('uid'));

        $user = new User;
        $detailData = $user->field('uid, username, auth_name, auth_at, gender, nickname, avatar_url')->where('status', '<>', 3)->where('uid', $uid)->select();

        if ($detailData && count($detailData) != 0) {
            $detailData = collection($detailData)->toArray();
        }
        // 学生对应的班级信息
        $class_user = new Classes_user;
        $userClass = $class_user->alias('cu')->join('classes c', 'cu.class_id = c.class_id', 'LEFT')->join('teacher t', 'c.teacher_id = t.teacher_id', 'LEFT')->where('cu.uid', $uid)->where('cu.status', 1)->field('c.class_id, c.class_name, c.class_day, c.class_time, t.teacher_name, t.status as teacher_status')->select();
        if ($userClass && count($userClass) != 0) {
            $userClass = collection($userClass)->toArray();
            // 对日期做处理
            foreach ($userClass as &$info) {
                $info['class_day'] = convertDay($info['class_day']);
            }
        }
        // dump($userClass);die;
        // 用户打卡记录
        $userClock = getClockList($uid);
        if ($userClock && count($userClock) != 0) {
            $userClock = collection($userClock)->toArray();
        }
        // dump($userClock);die;
        $this->assign('userClock', $userClock);
        $this->assign('detailData', $detailData);
        $this->assign('userClass', $userClass);
        return $this->fetch();
    }

    /**
     * 学生对应的课程详情
     * @return ary 返回值
     */
    public function usercourse()
    {
        $request = Request::instance();
        $uid = intval($request->param('uid'));
        $this->assign('uid', $uid);
        // 调用公共函数
        $userCourse = getUserCourse($uid);
        $this->assign('userCourse', $userCourse);

        $classes = new Classes;
        $classData = $classes->field('class_id, class_name')->where('status', '<>', 3)->select();
        $this->assign('classData', $classData);
        $subject = new Subject;
        $subjectData = $subject->field('subject_id, subject_name')->select();
        $this->assign('subjectData', $subjectData);

        return $this->fetch();
    }

    /**
     * 课程续费
     * @param  Request $request 参数
     * @return ary              返回结果
     */
    public function courseRenew(Request $request)
    {
        $uid = intval($request->param('uid'));
        $classId = intval($request->param('classId'));
        $classes_user = new Classes_user;
        $info = $classes_user->alias('a')->join('classes w', 'a.class_id = w.class_id', 'LEFT')->join('course n', 'w.course_id = n.course_id', 'LEFT')->field('a.course_left_times, a.renew_times, a.course_end_at, n.course_id, n.course_price, n.course_period, n.course_times')->where('a.class_id', $classId)->where('uid', $uid)->where('a.status', '<>', 3)->select();
        // 非空判断
        if ($info && count($info) != 0) {
            $info = collection($info)->toArray();
            // dump($info);die;
            $info = $info[0];
            $where = [];
            $where['renew_times'] = $info['renew_times'] + 1;
            $where['course_left_times'] = $info['course_times'] + $info['course_left_times'];
            $where['course_end_at'] = $info['course_end_at'] + $info['course_period'] * 86400 - 1;
            $where['update_by'] = Session::get('admin_id');
            // 插入order表
            Db::name('order')->insert(['order_sn' => genOrderSn(), 'uid' => $uid, 'class_id' => $classId, 'course_id' => $info['course_id'], 'fee' => $info['course_price'], 'status' => 1, 'created_at' => time(), 'pay_at' => time() + 1]);
            // 更新classes_user表
            $update = $classes_user->where('class_id', $classId)->where('uid', $uid)->update($where);
            if ($update) {
                return objReturn(0, '续费成功！');
            } else {
                return objReturn(400, '续费失败！');
            }
        }
        return objReturn(400, '续费失败！');
    }

    /**
     * 更改展示状态为启用
     * @param  Request $request 参数
     * @return ary              返回结果
     */
    public function startUser(Request $request)
    {
        $where['uid'] = $request->param('id');
        $where['status'] = 2;
        $where['update_at'] = time();
        // 调用公共函数，参数true为更新
        $update = saveData('user', $where, true);
        if ($update) {
            return objReturn(0, '更改成功！');
        } else {
            return objReturn(400, '更改失败！');
        }
    }

    /**
     * 更改展示状态为不启用
     * @param  Request $request 参数
     * @return ary           返回结果
     */
    public function stopUser(Request $request)
    {
        $where['uid'] = $request->param('id');
        $where['status'] = 1;
        $where['update_at'] = time();
        // 调用公共函数，参数true为更新
        $update = saveData('user', $where, true);
        if ($update) {
            return objReturn(0, '更改成功！');
        } else {
            return objReturn(400, '更改失败！');
        }
    }

    /**
     * 删除班级功能
     * @param  Request $request 参数
     * @return ary           返回结果
     */
    public function delUser(Request $request)
    {
        $uid = intval($request->param('id'));
        $del['status'] = 3;
        $del['update_at'] = time();

        $cu['update_at'] = time();
        $cu['update_by'] = Session::get('admin_id');
        $cu['status'] = 3;
        // 开启事务
        Db::startTrans();
        // 事务
        try {
            $res1 = Db::name('user')->where('uid', $uid)->update($del); // 学生信息
            $res2 = Db::name('classes_user')->where('uid', $uid)->update($cu); //学生班级课程信息
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
        // $delete = saveData('user', $del, true);
        // if ($delete) {
        //     return objReturn(0, '删除成功！');
        // } else {
        //     return objReturn(400, '删除失败！');
        // }
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

    /**
     * 新增用户课程
     *
     * @return void
     */
    public function addUserCourse()
    {
        $uid = intval(request()->param('uid'));
        $subjectId = intval(request()->param('subjectid'));
        $classId = intval(request()->param('classid'));
        $courseId = intval(request()->param('courseid'));
        if (empty($uid) || empty($courseId) || empty($classId) || empty($subjectId)) {
            return objReturn(400, '参数错误');
        }

        // 判断该学生 是否已经存在该班级
        $isStudentExist = Db::name('classes_user')->where('class_id', $classId)->where('uid', $uid)->where('status', '<>', 3)->count();
        if ($isStudentExist) {
            return objReturn(400, '该学生已拥有当前课程');
        }

        // 判断科目是否存在
        $isSubjectExist = Db::name('subject')->where('subject_id', $subjectId)->where('status', 3)->count();
        // 判断当前班级是否存在
        $isClassExist = Db::name('classes')->where('class_id', $classId)->where('status', 3)->count();
        // 判断当前课程是否存在并获取课程相关信息
        $courseInfo = Db::name('course')->where('course_id', $courseId)->field('status, course_period, course_times')->find();

        if ($isSubjectExist || $isClassExist || $courseInfo['status'] == 3) {
            return objReturn(400, '科目|课程|班级 信息已变更，请刷新界面后重试');
        }
        
        // 进行课程插入
        $where['class_id'] = $classId;
        $where['uid'] = $uid;
        $where['course_left_times'] = $courseInfo['course_times'];
        $where['course_end_at'] = time() + intval($courseInfo['course_period'] * 365);
        $where['created_at'] = time();
        $where['update_by'] = Session::get('admin_id');
        $save = saveData('class_user', $where, false);
        if ($save) {
            return objReturn(0, '新增课程成功', $save);
        } else {
            return objReturn(400, '新增课程失败');
        }
    }

    /**
     * 删除用户指定课程
     *
     * @return void
     */
    public function delUserCourse()
    {
        $uid = intval(request()->param('uid'));
        $classId = intval(request()->param('classId'));
        if (empty($uid) || empty($classId)) {
            return objReturn(400, '参数错误');
        }
        $update = Db::name('classes_user')->where('uid', $uid)->where('class_id', $classId)->update(['status' => 3, 'update_at' => time(), 'update_by' => Session::get('admin_id')]);
        if ($update) {
            return objReturn(0, '课程删除成功');
        } else {
            return objReturn(400, '课程删除失败');
        }
    }
}

