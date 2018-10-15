<?php
namespace app\index\controller;

use app\index\model\Admin as AdminDB;
use app\index\model\Menu;
use app\index\model\Power;
use app\index\model\Teacher;
use \think\Controller;
use \think\Request;
use \think\Session;

class Admin extends Controller
{

    /**
     * [adminlist 管理员管理页面]
     * @return   [ary]     [数据]
     */
    public function adminlist()
    {
        $admin = new AdminDB;
        $adminData = $admin->field('id, name, create_time, status, admin_teacher')->where('id', '<>', 1)->where('status', '<>', 3)->select();
        $teacher = new Teacher;
        $teacherData = $teacher->field('teacher_id,teacher_name')->where('status', '<>', 4)->select();
        if ($adminData && $teacherData) {
            $adminData = collection($adminData)->toArray();
            $teacherData = collection($teacherData)->toArray();
            $temp = [];
            foreach ($adminData as $key => $value) {
                foreach ($teacherData as $k => $v) {
                    if ($value['admin_teacher'] == 0) {
                        $adminData[$key]['admin_teacher'] = 0;
                        $adminData[$key]['admin_teacher_name'] = '未绑定';
                        break 1;
                    } else {
                        $adminData[$key]['admin_teacher'] = 1;
                        if ($value['admin_teacher'] == $v['teacher_id']) {
                            $adminData[$key]['admin_teacher_name'] = $v['teacher_name'];
                            break 1;
                        }
                    }
                }
            }
        } else {
            // 无教师信息
            foreach ($adminData as $key => $value) {
                $adminData[$key]['admin_teacher'] = 0;
                $adminData[$key]['admin_teacher_name'] = '未绑定';
            }
        }
        // dump($adminData);die;
        $this->assign('adminData', $adminData);
        return $this->fetch();
    }

    /**
     * [startAdmin 展示]
     * @param    Request    $request [参数]
     * @return   ary                 [返回值]
     */
    public function startAdmin(Request $request)
    {
        $where['id'] = $request->param('id');
        $where['status'] = 2;
        // 调用公共函数，参数true为更新
        $update = saveData('admin', $where, true);
        if ($update) {
            return objReturn(0, '启用成功');
        } else {
            return objReturn(400, '启用失败');
        }
    }

    /**
     * [stopAdmin 不展示]
     * @param    Request    $request [参数]
     * @return   ary                 [返回值]
     */
    public function stopAdmin(Request $request)
    {
        $where['id'] = $request->param('id');
        $where['status'] = 1;
        // 调用公共函数，参数true为更新
        $update = saveData('admin', $where, true);
        if ($update) {
            return objReturn(0, '停用成功');
        } else {
            return objReturn(400, '停用失败');
        }
    }

    /**
     * [delAdmin 删除]
     * @param    Request    $request [参数]
     * @return   ary                 [返回值]
     */
    public function delAdmin(Request $request)
    {
        $del['id'] = $request->param('id');
        $del['status'] = 3;
        // 调用公共函数，参数true为更新
        $delete = saveData('admin', $del, true);
        if ($delete) {
            return objReturn(0, '删除成功');
        } else {
            return objReturn(400, '删除失败');
        }
    }

    /**
     * [adminadd 添加最高管理员]
     * @return   [html]     [页面]
     */
    public function adminadd()
    {
        // 教师信息
        $teacher = new Teacher;
        $teacherData = $teacher->field('teacher_id,teacher_name')->where('status', '<>', 3)->select();
        $this->assign('teacherData', $teacherData);
        return $this->fetch();
    }

    /**
     * [power 权限列表]
     * @param    Request    $request [参数]
     * @return   [ary]               [ztree数据]
     */
    public function power(Request $request)
    {
        $menu = new Menu();
        $menuList = $menu->field('id, parent_id, name')->where('is_admin', 0)->select();
        $menuAry = array();
        // 构造ztree数据
        foreach ($menuList as $key => $value) {
            $ary = array(
                'id' => $value['id'],
                'pId' => $value['parent_id'],
                'name' => $value['name'],
                'open' => true,
                'checked' => false,
            );
            array_push($menuAry, $ary);
        }
        return json($menuAry);
    }

    /**
     * [selectPower 权限id]
     * @param    Request    $request [参数]
     * @return   [ary]               [返回值]
     */
    public function selectPower(Request $request)
    {
        $menuid = $request->param('menuid');
        // 是否存在session
        if (Session::has('menuid')) {
            // 删除session信息
            Session::delete('menuid');
        }
        // 存menuid到session
        Session::set('menuid', $menuid);

        if (Session::has('menuid')) {
            return objReturn(0, '权限信息保存成功！');
        } else {
            return objReturn(400, '权限信息保存失败！');
        }
    }

    /**
     * [addAdmin 添加管理员]
     * @param    Request    $request [参数]
     * @return   [ary]               [返回值]
     */
    public function addAdmin(Request $request)
    {
        $add['name'] = $request->param('admin_name');
        $add['password'] = md5($request->param('password'));
        $add['status'] = intval($request->param('admin_active'));
        $add['admin_teacher'] = intval($request->param('admin_teacher'));
        $add['create_time'] = time();
        $admin = new AdminDB;
        // 先验证用户名是否重复
        $res = $admin->where('name', $add['name'])->where('status', '<>', 3)->find();
        if (!$res) {
            // 是否存在session
            if (Session::has('menuid')) {
                // 先更新admin表获得admin_id
                $admin_id = $admin->insertGetId($add);
                if ($admin_id) {
                    // 取session数据
                    $menuid = Session::get('menuid');
                    // 对字符串处理转为数组
                    $menuArr = explode(',', $menuid, -1);
                    // dump($menuArr);die;
                    // 构造数据写入power表
                    $update = array();
                    $temp = [];
                    foreach ($menuArr as $key => $value) {
                        $temp['admin_id'] = $admin_id;
                        $temp['menu_id'] = $menuArr[$key];
                        $update[] = $temp;
                    }
                    $power = new Power;
                    // 新增权限信息
                    $result = $power->saveAll($update);
                    if ($result) {
                        return objReturn(0, '保存成功！');
                    } else {
                        return objReturn(400, '保存失败！');
                    }
                }
                return objReturn(400, '保存失败！');
            }
            return objReturn(400, '保存失败,未勾选权限信息！');
        }
        return objReturn(400, '保存失败,名称重复，请输入新名称！');
    }

    /**
     * [adminedit 修改管理员界面]
     * @return   [ary]     [数据]
     */
    public function adminedit()
    {
        $request = Request::instance();
        $admin_id = intval($request->param('admin_id'));
        $admin = new AdminDB;
        $adminData = $admin->field('id,name,status,admin_teacher')->where('id', $admin_id)->where('status', '<>', 3)->find();
        $this->assign('adminData', $adminData);
        // 教师信息
        $teacher = new Teacher;
        $teacherData = $teacher->field('teacher_id,teacher_name')->where('status', '<>', 3)->select();
        $this->assign('teacherData', $teacherData);
        return $this->fetch();
    }

    /**
     * prePower 原先的权限
     * @return   ary     数据
     */
    public function prePower(Request $request)
    {
        $admin_id = intval($request->param('admin_id'));
        // menu表数据
        $menu = new Menu;
        $menuList = $menu->field('id,parent_id,name')->where('is_admin', 0)->select();
        $menuList = collection($menuList)->toArray();
        // power表数据
        $power = new Power;
        $powerList = $power->field('id,admin_id,menu_id')->where('admin_id', $admin_id)->select();
        $powerList = collection($powerList)->toArray();
        // 构造ztree数据
        $menuAry = array();
        foreach ($menuList as $key => $value) {
            $ary['id'] = $value['id'];
            $ary['pId'] = $value['parent_id'];
            $ary['name'] = $value['name'];
            $ary['open'] = true;
            $ary['checked'] = false;
            foreach ($powerList as $k => $v) {
                if ($value['id'] == $v['menu_id']) {
                    $ary['checked'] = true;
                    break 1;
                }
            }
            $menuAry[] = $ary;
        }
        return json($menuAry);
    }

    /**
     * [editAdmin 修改管理员]
     * @param    Request    $request [参数]
     * @return   [ary]               [返回值]
     */
    public function editAdmin(Request $request)
    {
        $admin_id = intval($request->param('admin_id'));
        $admin['name'] = $request->param('admin_name');
        $oldPwd = md5($request->param('password'));
        $pwd = $request->param('password1');
        $newPwd = md5($request->param('password1'));
        $admin['status'] = intval($request->param('admin_active'));
        $admin['admin_teacher'] = intval($request->param('admin_teacher'));
        $admindata = new AdminDB;
        // 先验证用户名是否重复
        $res = $admindata->field('name')->where('name', $admin['name'])->where('name', '<>', $admin['name'])->where('status', '<>', 3)->find();
        if (!$res) {
            // 是否存在session
            if (Session::has('menuid')) {
                // 取session数据
                $menuid = Session::get('menuid');
                // 对字符串处理转为数组
                $menuArr = explode(',', $menuid, -1);
                // 构造数据写入power表
                $update = array();
                $temp = [];
                foreach ($menuArr as $key => $value) {
                    $temp['admin_id'] = $admin_id;
                    $temp['menu_id'] = $menuArr[$key];
                    $update[] = $temp;
                }
                $power = new Power;
                // 先删除原来的权限信息
                $del = $power->where('admin_id', $admin_id)->delete();
                // 新增权限信息
                $result = $power->saveAll($update);
                if ($result) {
                    // 新增成功后删除session
                    Session::delete('menuid');
                }
            }
            // 原密码判断
            $res = $admindata->where('id', $admin_id)->where('password', $oldPwd)->where('status', '<>', 3)->find();
            if (!empty($res)) {
                if ($pwd != 0) {
                    $admin['password'] = $newPwd;
                    $admin['id'] = $admin_id;
                    // 调用公共函数保存，参数true为更新
                    $update = saveData('admin', $admin, true);
                    if ($update) {
                        return objReturn(0, '修改成功！');
                    } else {
                        return objReturn(400, '修改失败！');
                    }
                } else {
                    return objReturn(0, '修改成功！');
                }
            }
            return objReturn(400, '保存失败,原密码错误！');
        }
        return objReturn(400, '保存失败,名称重复，请输入新名称！');
    }
}
