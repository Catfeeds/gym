<?php
namespace app\index\controller;

use \think\Controller;
use \think\Request;
use \think\Session;

use app\index\model\Admin;
use app\index\model\Power;
use app\index\model\User;
use app\index\model\Teacher;

class Index extends Controller
{
    /**
     * index 主页
     *
     * @return   [html]     [页面]
     */
    public function index()
    {
        // 判断是否存在session
        if (!Session::has('loginname')) {
            header("Location: http://art.up.maikoo.cn/index/index/login");
        } else {
            $username = Session::get('loginname');
            $this->assign("username", $username);
            $admin    = new Admin;
            $admin_id = $admin->where('name', $username)->where('status', '<>', 3)->value('id');
            // 存入session中
            Session::set('admin_id', $admin_id);
            $this->assign('admin_id', $admin_id);
            if ($admin_id != '') {
                // 根据id找菜单的id
                $power    = new Power();
                $menuList = $power->field('menu_id')->where('admin_id', $admin_id)->select();
                $menuList = collection($menuList)->toArray();
                $this->assign("menuList", $menuList);
            }
            return $this->fetch();
        }
    }

    /**
     * @return 登录界面
     */
    public function login()
    {
        // Session::delete('loginname');
        Session::clear();
        return $this->fetch();
    }

    /**
     * @return 退出登录
     */
    public function logout()
    {
        Session::clear();
        // Session::delete('loginname');
        $url = 'http://art.up.maikoo.cn/index/index/login';
        $this->redirect($url);
    }

    /**
     * checkLogin 确认登录信息
     *
     * @param    Request    $request [参数]
     * @return   [ary]               [返回值]
     */
    public function checkLogin(Request $request)
    {
        $username = $request->param('username');
        $password = $request->param('password');
        $admin    = new Admin;
        $res      = $admin->where('name', $username)->select();
        if (empty($res)) {
            return objReturn(100, '账号不存在！');
        } else {
            $result1 = $admin->where('name', $username)->where('status', 3)->find();
            if ($result1) {
                return objReturn(500, '账号已失效！');
            } else {
                $result2 =  $admin->where('name', $username)->where('status', 1)->find();
                if ($result2) {
                    return objReturn(400, '账号未启用！');
                } else {
                    $result3 = $admin->where('name', $username)->where('password', $password)->find();
                    if ($result3) {
                        // 存登录名到全局session
                        Session::set('loginname', $username);
                        return objReturn(0, '登录成功！');
                    } else {
                        return objReturn(300, '密码错误！');
                    }
                }
            }
        }
    }

    /**
     * @return 欢迎页面
     */
    public function welcome()
    {
        $user = new User;
        // 时间戳
        $todaytime = strtotime('today'); //当天时间戳
        // 查询学生
        $userList = $user -> field('uid, auth_at') -> select();
        $userList = collection($userList) -> toArray();
        // 初始化学生统计信息
        $userCal = [];
        $userCal['not_auth'] = 0;
        $userCal['al_auth'] = 0;
        $userCal['today_auth'] = 0;
        // 学生信息统计
        foreach ($userList as $k => $v) {
            if (empty($v['auth_at'])) {
                $userCal['not_auth']++;
            } elseif ($v['auth_at'] >= $todaytime) {
                $userCal['today_auth']++;
            } else {
                $userCal['al_auth']++;
            }
        }
        $userCal['al_auth'] += $userCal['today_auth'];
        $userCal['total'] = count($userList);
        // 教师信息统计
        $teacherCal = [];
        $teacherCal['not_auth'] = 0;
        $teacherCal['al_auth'] = 0;
        $teacherCal['today_auth'] = 0;
        $teacher = new Teacher;
        $teacherList = $teacher -> where('status', 'in', [1, 2]) -> field('teacher_id, auth_at') -> select();
        $teacherList = collection($teacherList) -> toArray();
        foreach ($teacherList as $k => $v) {
            if (empty($v['auth_at'])) {
                $teacherCal['not_auth']++;
            } elseif ($v['auth_at'] >= $todaytime) {
                $teacherCal['today_auth']++;
            } else {
                $teacherCal['al_auth']++;
            }
        }
        $teacherCal['al_auth'] += $teacherCal['today_auth'];
        $teacherCal['total'] = count($teacherList);
        // 数据返回
        $this->assign('userCal', $userCal);
        $this->assign('teacherCal', $teacherCal);
        return $this->fetch();
    }

    /**
     *修改管理员密码功能
     *
     */
    public function passwordUpdate(Request $request)
    {
        $adminId = intval($request->param('admin_id'));
        $oriPwd  = $request->param('password');
        $newPwd  = $request->param('password1');
        // 原密码与新密码不能相同
        if ($$oriPwd == $newPwd) {
            return objReturn(400, '修改失败,原密码与新密码不能相同！');
            exit;
        }
        $admin   = new Admin;
        $pwd     = $admin->where('id', $adminId)->value('password');
        if ($oriPwd != $pwd) {
            return objReturn(400, '初始密码错误！');
        } else {
            $where['id']       = $adminId;
            $where['password'] = $newPwd;
            // 调用公共函数，参数true为更新
            $update = saveData('admin', $where, true);
            if ($update) {
                return objReturn(0, '修改成功！');
            } else {
                return objReturn(400, '修改失败！');
            }
        }
    }
}
