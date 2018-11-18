<?php
namespace app\index\controller;

use \think\Controller;
use \think\Request;
use \think\Session;

use app\index\model\Admin;
use app\index\model\Power;
use app\index\model\User;
use think\Db;
use app\index\model\Coach_clock;

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
        // 查询今日打卡的教练列表
        $coachClock = new Coach_clock;
        $todayClockCoachList = $coachClock->alias('cc')->join('gym_user u', 'cc.uid = u.uid', 'LEFT')->where('cc.clock_start_at', 'between', [$todaytime, $todaytime + 86399])->field('cc.clock_start_at, u.user_avatar_url, u.user_name')->order('cc.clock_start_at asc')->select();
        if ($todayClockCoachList && count($todayClockCoachList) > 0) {
            $todayClockCoachList = collection($todayClockCoachList)->toArray();
        } else {
            $todayClockCoachList = [];
        }
        $this->assign('todayClockList', $todayClockCoachList);
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
