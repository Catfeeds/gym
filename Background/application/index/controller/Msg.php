<?php
namespace app\index\controller;

use \think\Controller;
use \think\Db;
use \think\File;
use \think\Request;
use \think\Session;
use app\index\model\Classes_user;
use app\index\model\Course;
use app\index\model\Msg as msgData;
use app\index\model\User;
use app\index\model\Subject;


class Msg extends Controller
{

    /**
     * 公告消息页面
     * @return html 页面
     */
    public function tomsg()
    {
        // 调用公用函数，0为公告
        $field   = 'msg_id, msg_content, msg_img, status ,send_at';
        $msgData = getMessage(0, $field, true);
        // dump($msgData);die;
        $this->assign('msgData', $msgData);
        return $this->fetch();
    }

    /**
     * 上传图片(单张)
     *
     * @param Request $request 参数
     * @return string  图片上传路径
     */
    public function addPic(Request $request)
    {
        $file = request()->file('file');
        // 是否存在session
        if (Session::has('picsrc')) {
            // 删除session信息
            Session::delete('picsrc');
        }
        // 移动到框架应用根目录/static/imgTemp/目录下
        $info = $file->move(PUBLIC_PATH . DS . 'static' . DS . 'imgTemp');
        if ($info) {
            $str    = $info->getSaveName();
            $picsrc = 'static' . DS . 'imgTemp' . DS . $str;
            // 存路径名到session
            Session::set('picsrc', $picsrc);
            return $picsrc;
        }
        return 400;
    }

    /**
     * 公告添加页面
     * @return html 页面
     */
    public function tomsgadd()
    {
        return $this->fetch();
    }

    /**
     * 添加公告功能
     *
     * @return ary 返回值
     */
    public function addTomsg()
    {
        $request = Request::instance();
        // 0为公告
        $add['msg_type']    = 0;
        $add['msg_content'] = htmlspecialchars($request->param('msg_content'));
        $add['status']      = 2;
        $add['created_at']  = time();
        $add['send_at']     = time();
        // 是否存在session
        if (Session::has('picsrc')) {
            $source = PUBLIC_PATH . Session::get('picsrc');
            // 新的路径,取session值
            $word = DS . 'msg';
            $str  = substr_replace(Session::get('picsrc'), $word, 10, 4);
            // 创建文件夹
            $str3 = substr($str, 0, 23);
            if (!is_dir(PUBLIC_PATH . $str3)) {
                mkdir(PUBLIC_PATH . $str3);
            }
            // 框架应用根目录/static/img/目录
            $destination = PUBLIC_PATH . $str;
            // 拷贝文件到指定目录
            $res = copy($source, $destination);
            // 移动成功
            if ($res) {
                $add['msg_img'] = DS . $str;
            }
            // 删除session信息
            Session::delete('picsrc');
            // 调用公共函数，参数false为新增
            $insert = saveData('msg', $add, false);
            if ($insert) {
                return objReturn(0, '发送成功');
            } else {
                return objReturn(400, '发送失败');
            }
        }
        return objReturn(400, '发送失败，请上传图片！');
    }

    /**
     * 公告修改页面
     * @return html 页面
     */
    // public function tomsgedit(){
    //        $request = Request::instance();
    //        $msg_id = intval($request -> param('msg_id'));
    //        // 调用公用函数,参数false为全部
    //     $field = 'msg_id, msg_content, msg_img, status';
    //     $msgData = getMsgById($msg_id,$field,false);
    //     $this->assign('msgData',$msgData);
    //        return $this->fetch();
    // }

    /**
     * 修改公告功能
     *
     * @return ary 返回值
     */
    // public function editMsg(){
    //        $request = Request::instance();
    //        $update['msg_id'] = intval($request -> param('msg_id'));
    //        $update['msg_content'] = htmlspecialchars($request->param('msg_content'));
    //        $update['status'] = htmlspecialchars($request->param('status'));
    //        // 是否存在session
    //        if(Session::has('picsrc')){
    //            $source = PUBLIC_PATH.Session::get('picsrc');
    //            // 新的路径,取session值
    //            $word = DS.'msg';
    //            $str = substr_replace(Session::get('picsrc'),$word,10,4);
    //            // 创建文件夹
    //            $str3 = substr($str,0,23);
    //            if(!is_dir(PUBLIC_PATH.$str3)){
    //                mkdir(PUBLIC_PATH.$str3);
    //            }
    //            // 框架应用根目录/static/img/目录
    //            $destination = PUBLIC_PATH.$str;
    //            // 拷贝文件到指定目录
    //            $res = copy($source,$destination);
    //            // 移动成功
    //            if($res){
    //                $update['msg_img'] = DS.$str;
    //            }
    //            // 删除session信息
    //            Session::delete('picsrc');
    //        }
    //        // 调用公共函数，参数true为更新
    //        $new = saveData('msg',$update,true);
    //        if($new){
    //            return objReturn(0,'修改成功');
    //        }else{
    //            return objReturn(400,'修改失败');
    //        }
    // }

    /**
     * 更改展示状态为启用
     * @param  Request $request 参数
     * @return ary           返回结果
     */
    // public function startMsg(Request $request){
    //     $where['msg_id'] = $request->param('id');
    //     $where['status'] = 1;
    //    // 调用公共函数，参数true为更新
    //     $update = saveData('msg',$where,true);
    //     if($update){
    //         return objReturn(0,'展示成功');
    //     }else{
    //         return objReturn(400,'展示失败');
    //     }
    // }

    /**
     * 更改展示状态为不启用
     * @param  Request $request 参数
     * @return ary           返回结果
     */
    // public function stopMsg(Request $request){
    //     $where['msg_id'] = $request->param('id');
    //     $where['status'] = 0;
    //    // 调用公共函数，参数true为更新
    //     $update = saveData('msg',$where,true);
    //     if($update){
    //         return objReturn(0,'停用成功');
    //     }else{
    //         return objReturn(400,'停用失败');
    //     }
    // }

    /**
     * 删除功能
     * @param  Request $request 参数
     * @return ary           返回结果
     */
    public function delMsg(Request $request)
    {
        $del['msg_id'] = $request->param('id');
        $del['status'] = 3;
        // 调用公共函数，参数true为更新
        $delete = saveData('msg', $del, true);
        if ($delete) {
            return objReturn(0, '删除成功');
        } else {
            return objReturn(400, '删除失败');
        }
    }

    /**
     * 推送消息页面
     * @return html 页面
     */
    public function pushmsg()
    {
        $msg         = new msgData;
        $pushmsgData = $msg->alias('a')->join('classes c', 'a.class_id = c.class_id', 'LEFT')->join('course n', 'c.course_id = n.course_id', 'LEFT')->join('user w', 'a.target_uid = w.uid', 'LEFT')->field('a.msg_id,a.msg_content,a.msg_img,a.status,a.send_at,w.openid,w.username,n.course_name')->where('a.msg_type', 1)->where('a.status', '<>', 3)->select();
        $pushmsgData = collection($pushmsgData)->toArray();
        $this->assign('pushmsgData', $pushmsgData);
        return $this->fetch();
    }

    /**
     * 推送添加页面
     * @return html 页面
     */
    public function pushmsgadd()
    {
        // 调用公用函数，获取所有课程
        // $field      = 'course_id, course_name';
        // $courseData = getCourse($field, false, null);
        // // dump($courseData);die;
        // $this->assign('courseData', $courseData);
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
    public function getSubjectCourse(Request $request){
        $subjectId     = intval($request->param('subject_id'));
        $course = new Course;
        $courseData = $course ->field('course_id,course_name') ->where('subject_id',$subjectId)->where('status', '<>', 3)->select();
        if($courseData){
            return objReturn(0, 'success',$courseData);
        }else{
            return objReturn(400, '无科目对应的课程信息！');

        }
    }

    /**
     * 获取课程对应班级
     * @param  Request $request 参数
     * @return ary              返回值
     */
    public function courseClass(Request $request)
    {
        $courseId = intval($request->param('id'));
        $course   = new Course;
        // 连表查询
        $classData = $course->alias('a')->join('classes w', 'a.course_id = w.course_id', 'LEFT')->field('w.class_id,w.class_name')->where('a.status', '<>', 3)->where('a.course_id', $courseId)->where('w.course_id', $courseId)->select();
        if ($classData && count($classData) != 0) {
            $classData = collection($classData)->toArray();
            return objReturn(0, 'success', $classData);
        }
        return objReturn(400, '无班级信息！');
    }

    /**
     * 获取班级的用户信息
     * @param  Request $request 参数
     * @return ary              返回值
     */
    public function classUser(Request $request)
    {
        $classId      = intval($request->param('id'));
        $classes_user = new Classes_user;
        // 连表查询
        $userData = $classes_user->alias('a')->join('user w', 'a.uid = w.uid', 'LEFT')->where('a.class_id', $classId)->where('a.status', '<>', 3)->select();
        if ($userData && count($userData) != 0) {
            $userData = collection($userData)->toArray();
            return objReturn(0, 'success', $userData);
        }
        return objReturn(400, '无对应的用户信息！');
    }

    /**
     * 推送消息添加功能
     * @return ary 返回值
     */
    public function addPushmsg()
    {
        $request = Request::instance();
        // 1为推送
        $add['msg_type']      = 1;
        $add['msg_content']   = htmlspecialchars($request->param('pushmsg_content'));
        $add['status']        = intval($request->param('pushmsg_active'));
        $add['created_at']    = time();
        $add['target_uid']    = intval($request->param('user'));
        $user                 = new User;
        $add['target_openid'] = $user->where('uid', $add['target_uid'])->value('openid');
        $add['class_id']      = intval($request->param('class_id'));
        $courseName           = htmlspecialchars($request->param('courseName'));
        $add['send_at'] = time();
        $add['send_by'] = Session::get('admin_id');
        
        // 是否存在session
        if (Session::has('picsrc')) {
            $source = PUBLIC_PATH . Session::get('picsrc');
            // 新的路径,取session值
            $word = DS . 'msg';
            $str  = substr_replace(Session::get('picsrc'), $word, 10, 4);
            // 创建文件夹
            $str3 = substr($str, 0, 23);
            if (!is_dir(PUBLIC_PATH . $str3)) {
                mkdir(PUBLIC_PATH . $str3);
            }
            // 框架应用根目录/static/img/目录
            $destination = PUBLIC_PATH . $str;
            // 拷贝文件到指定目录
            $res = copy($source, $destination);
            // 移动成功
            if ($res) {
                $add['msg_img'] = DS . $str;
            }
            // 删除session信息
            Session::delete('picsrc');
            // // 调用公共函数，参数false为新增
            // $insert = saveData('msg',$add,false);
            // 返回主键id
            $msg    = new msgData;
            $insert = $msg->insertGetId($add);
            if ($insert) {
                $msgId      = $insert;
                $formIdInfo = getFormId($add['target_uid']);
                if ($formIdInfo) {
                    $message = array(
                        'touser'      => $add['target_openid'],
                        'template_id' => 'joZZ2-EiVu8Ac6AVNnFswAt9oUHZNrfBRHEK2U-0VjU',
                        'page'        => '/pages/messagedetail/messagedetail?msgId=' . $msgId,
                        'form_id'     => $formIdInfo['formid'],
                        'data'        => array(
                            'keyword1' => array('value' => $courseName), //课程名称
                            'keyword2' => array('value' => $add['msg_content']), //回复内容
                            'keyword3' => array('value' => date('Y-m-d H:i:s', time())), //回复时间
                        ),
                    );

                    $message    = json_encode($message);
                    $sendResult = sendTemplateMessage($message);
                    if ($sendResult == 0) {
                        Db::name('formid')->update(['idx' => $formIdInfo['idx'], 'status' => 1]);
                    }
                }
                return objReturn(0, '新增成功');
            } else {
                return objReturn(400, '新增失败');
            }
        } else {
            return objReturn(400, '新增失败，请上传图片！');
        }

    }

    /**
     * 推送修改页面
     * @return html 页面
     */
    // public function pushmsgedit(){
    //        $request = Request::instance();
    //        $msg_id = intval($request -> param('pushmsg_id'));
    //        $msg = new msgData;
    //        $msgData = $msg -> alias('a') -> join('user_profile w', 'a.target_uid = w.uid', 'LEFT') ->join('course_user m','a.target_uid = m.uid') ->join('course n','m.course_id = n.course_id') -> field('a.msg_id,a.msg_content,a.msg_img,a.status,w.nickname,n.course_id,n.course_name') ->where('msg_id',$msg_id)-> find();
    //        // dump($msgData);die;
    //     $this->assign('msgData',$msgData);
    //        // 调用公用函数，获取所有课程
    //        $field = 'course_id, course_name';
    //        $courseData = getAllCourse($field,false,null);
    //        // dump($courseData);die;
    //        $this->assign('courseData',$courseData);
    //        return $this->fetch();
    // }

    /**
     * 修改推送功能
     *
     * @return ary 返回值
     */
    // public function editPushmsg(){
    //        $request = Request::instance();
    //        $update['msg_id'] = intval($request -> param('pushmsg_id'));
    //        $update['msg_content'] = htmlspecialchars($request->param('pushmsg_content'));
    //        $update['status'] = htmlspecialchars($request->param('pushmsg_active'));
    //        $update['target_uid'] = intval($request->param('user'));
    //        $user = new User;
    //        $update['target_openid'] = $user -> where('uid',$update['target_uid']) ->value('openid');
    //        // 是否存在session
    //        if(Session::has('picsrc')){
    //            $source = PUBLIC_PATH.Session::get('picsrc');
    //            // 新的路径,取session值
    //            $word = DS.'msg';
    //            $str = substr_replace(Session::get('picsrc'),$word,10,4);
    //            // 创建文件夹
    //            $str3 = substr($str,0,23);
    //            if(!is_dir(PUBLIC_PATH.$str3)){
    //                mkdir(PUBLIC_PATH.$str3);
    //            }
    //            // 框架应用根目录/static/img/目录
    //            $destination = PUBLIC_PATH.$str;
    //            // 拷贝文件到指定目录
    //            $res = copy($source,$destination);
    //            // 移动成功
    //            if($res){
    //                $update['msg_img'] = DS.$str;
    //            }
    //            // 删除session信息
    //            Session::delete('picsrc');
    //        }
    //        // dump($update);die;
    //        // 调用公共函数，参数true为更新
    //        $new = saveData('msg',$update,true);
    //        if($new){
    //            return objReturn(0,'修改成功');
    //        }else{
    //            return objReturn(400,'修改失败');
    //        }
    // }

    /**
     * 更改展示状态为启用
     * @param  Request $request 参数
     * @return ary           返回结果
     */
    public function startPushmsg(Request $request)
    {
        $where['msg_id'] = $request->param('id');
        $where['status'] = 2;
        $where['send_at'] = time();
        // 调用公共函数，参数true为更新
        $update = saveData('msg', $where, true);
        if ($update) {
            return objReturn(0, '发送成功！');
        } else {
            return objReturn(400, '发送失败！');
        }
    }

    /**
     * 更改展示状态为不启用
     * @param  Request $request 参数
     * @return ary           返回结果
     */
    // public function stopPushmsg(Request $request){
    //     $where['msg_id'] = $request->param('id');
    //     $where['status'] = 0;
    //    // 调用公共函数，参数true为更新
    //     $update = saveData('msg',$where,true);
    //     if($update){
    //         return objReturn(0,'停用成功');
    //     }else{
    //         return objReturn(400,'停用失败');
    //     }
    // }

    /**
     * 删除功能
     * @param  Request $request 参数
     * @return ary           返回结果
     */
    public function delPushmsg(Request $request)
    {
        $del['msg_id'] = $request->param('id');
        $del['status'] = 3;
        // 调用公共函数，参数true为更新
        $delete = saveData('msg', $del, true);
        if ($delete) {
            return objReturn(0, '删除成功');
        } else {
            return objReturn(400, '删除失败');
        }
    }
}
