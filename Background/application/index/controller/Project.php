<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;
use think\Session;


class Project extends Controller
{
    /**
     * 获取项目列表界面
     *
     * @return html
     */
    public function projectlist()
    {
        $field = "project_id, project_name, project_img, project_video, created_at, status, sort";
        $projectList = getProject($field, true, null);
        $this->assign('projectList', $projectList);
        return $this->fetch();
    }

    /**
     * 项目编辑界面
     *
     * @return html
     */
    public function projectedit()
    {
        $pid = request()->param('pid');
        $field = "project_name, project_img, project_video, sort";
        $projectInfo = getProjectById($pid, $field, false);
        $this->assign('projectInfo', $projectInfo);
        $this->assign('projectId', $pid);
        return $this->fetch();
    }

    /**
     * 更新项目内容
     *
     * @return void
     */
    public function updateProject()
    {
        // 判断是否有封面图片
        $pcover = Session::get('proCover');
        $pvideo = Session::get('proVideo');

        $pid = request()->param('pid');

        $project['project_name'] = htmlspecialchars(request()->param('name'));
        $project['project_name'] = intval(request()->param('sort'));
        if ($pcover) {
            $project['project_img'] = $pcover;
        }
        if ($pvideo) {
            $project['project_video'] = $pcover;
        }
        $project['update_at'] = time();
        $project['update_by'] = Session::get('adminId');
        $update = Db::name('project')->where('project_id', $pid)->update($project);
        if ($update) {
            Session::delete('proCover');
            Session::delete('proVideo');
            return objReturn(0, '项目更新成功');
        } else {
            return objReturn(400, '项目更新失败');
        }
    }

    /**
     * 上传项目封面
     *
     * @return void
     */
    public function uploadProjectCover()
    {
        $file = request()->file('file');
        $dirName = '.' . DS . 'static' . DS . 'imgTemp' . DS;
        // 是否存在session
        if (Session::has('proCover')) {
            // 删除实际文件
            @unlink(ROOT_PATH . 'public' . Session::get('proCover'));
            // 删除session信息
            Session::delete('proCover');
        }
        // 移动到框架应用根目录/static/imgTemp/目录下
        $info = $file->move($dirName);
        if ($info) {
            $fileName = $info->getSaveName();
            $coverSrc = DS . 'static' . DS . 'imgTemp' . DS . $fileName;
            // 存路径名到session
            Session::set('proCover', $coverSrc);
            return objReturn(0, '上传成功！', $coverSrc);
        }
        return objReturn(400, '上传失败！', $file->getError());
    }

    /**
     * 上传项目封面
     *
     * @return void
     */
    public function uploadProjectVideo()
    {
        $file = request()->file('file');
        $dirName = '.' . DS . 'static' . DS . 'videoTemp' . DS;
        // 是否存在session
        if (Session::has('proVideo')) {
            // 删除实际文件
            @unlink(ROOT_PATH . 'public' . Session::get('proVideo'));
            // 删除session信息
            Session::delete('proVideo');
        }
        // 移动到框架应用根目录/static/imgTemp/目录下
        $info = $file->validate(['size' => 1024 * 1024 * 80, 'ext' => 'mp4, avi'])->move($dirName);
        if ($info) {
            $fileName = $info->getSaveName();
            $videoSrc = DS . 'static' . DS . 'videoTemp' . DS . $fileName;
            // 存路径名到session
            Session::set('proVideo', $videoSrc);
            return objReturn(0, '上传成功！', $videoSrc);
        }
        return objReturn(400, $file->getError());
    }

}