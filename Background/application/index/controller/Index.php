<?php
namespace app\index\controller;

use \think\Controller;
use \think\Request;
use \think\Session;

use app\index\model\Admin;
use app\index\model\Power;
use app\index\model\User;

class Index extends Controller
{
    /**
     * index 主页
     *
     * @return void
     */
    public function index()
    {
        // 判断是否存在session
        if (!Session::has('adminId')) {
            $url = 'https://test.kekexunxun.com/index/index/login';
            $this->redirect($url);
        } else {
            // 存入session中
            $this->assign('uname', Session::get('uname'));
            $this->assign('adminId', Session::get('adminId'));
            return $this->fetch();
        }
    }

    /**
     * @return 登录界面
     */
    public function login()
    {
        Session::clear();
        return $this->fetch();
    }

    /**
     * @return 退出登录
     */
    public function logout()
    {
        Session::clear();
        $url = 'https://test.kekexunxun.com/index/index/login';
        $this->redirect($url);
    }

    /**
     * checkLogin 确认登录信息
     *
     * @return
     */
    public function checkLogin()
    {
        $username = htmlspecialchars(request()->param('username'));
        $password = request()->param('password');
        $admin = new Admin;
        $res = $admin->where('uname', $username)->field('pwd, admin_id')->find();
        if (!$res) {
            return objReturn(400, '账号不存在!');
        }
        if ($password == $res['pwd']) {
            Session::set('uname', $username);
            Session::set('adminId', $res['admin_id']);
            return objReturn(0, '登陆成功');
        }
        return objReturn(300, '密码错误！');
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
        // 初始化学生统计信息
        return $this->fetch();
    }

    /**
     *修改管理员密码功能
     *
     */
    public function updatePass()
    {
        $adminId = intval(request()->param('admin_id'));
        $oriPwd = request()->param('password');
        $newPwd = request()->param('password1');
        $admin = new Admin;
        $pwd = $admin->where('admin_id', $adminId)->value('pwd');
        if ($oriPwd != $pwd) {
            return objReturn(400, '原始密码错误！');
        }
        $update = $admin->where('admin_id', $adminId)->update(['pwd' => $newPwd, 'update_at' => time()]);
        if ($update) {
            return objReturn(0, '密码修改成功！');
        } else {
            return objReturn(400, '密码修改失败！');
        }
    }
}
