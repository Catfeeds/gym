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
        // 处理教练上下班相关信息
        $today = strtotime('today');
        $settingInfo['coach_work_start_at'] = date('H:i', $today + $settingInfo['coach_work_start_at']);
        $settingInfo['coach_work_end_at'] = date('H:i', $today + $settingInfo['coach_work_end_at']);
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
        $data['coach_work_start_at'] = strtotime(request()->param('coach_start')) - strtotime('today');
        $data['coach_work_end_at'] = strtotime(request()->param('coach_end')) - strtotime('today');
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
    }

}

