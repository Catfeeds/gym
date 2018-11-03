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

        $project['project_name'] = htmlspecialchars(request()->param('pname'));
        $project['sort'] = intval(request()->param('sort'));
        if ($pcover) {
            $project['project_img'] = $pcover;
        }
        if ($pvideo) {
            $project['project_video'] = $pvideo;
        }
        $project['update_at'] = time();
        $project['update_by'] = Session::get('adminId');
        $update = Db::name('project')->where('project_id', $pid)->update($project);
        if ($update) {
            Session::delete('proCover');
            Session::delete('proVideo');
            return objReturn(0, '项目更新成功');
        } else {
            return objReturn(400, '项目更新失败', $update);
        }
    }

    /**
     * 新增一个项目界面
     *
     * @return html
     */
    public function projectadd()
    {
        return $this->fetch();
    }

    /**
     * 项目新增
     *
     * @return void
     */
    public function addProject()
    {
        // 判断是否有封面图片
        $pcover = Session::get('proCover');
        $pvideo = Session::get('proVideo');
        if (!$pcover) {
            return objReturn(400, '请上传项目封面');
        }
        $project['project_name'] = htmlspecialchars(request()->param('pname'));
        $project['sort'] = intval(request()->param('sort'));
        $project['status'] = intval(request()->param('active'));
        $project['project_desc'] = request()->param('pdesc');
        $project['created_at'] = time();
        $insert = Db::name('project')->insert($project);
        if ($insert) {
            Session::delete('proCover');
            Session::delete('proVideo');
            return objReturn(0, '新增项目成功');
        } else {
            return objReturn(400, '新增项目失败', $update);
        }
    }

    /**
     * 项目详情界面
     *
     * @return html
     */
    public function projectdesc()
    {
        // 获取项目详情
        $pid = request()->param('pid');
        $projectDesc = Db::name('project')->where('project_id', $pid)->value('project_desc');
        if ($projectDesc) {
            $projectDesc = explode(',', $projectDesc);
            $proSort = [];
            $proArr = [];
            foreach ($projectDesc as $k => $v) {
                $temp = [];
                $temp = explode(':', $v);
                $proSort[] = $temp[1];
                $pro = [];
                $pro['name'] = $temp[1];
                $pro['img'] = config('SITEROOT') . $temp[0];
                $proArr[] = $pro;
            }
            array_multisort($proSort, SORT_ASC, SORT_NUMERIC, $proArr);
        } else {
            $projectDesc = [];
        }
        $this->assign('descArr', $proArr);
        $this->assign('pid', $pid);
        return $this->fetch();
    }

    /**
     * 项目详情新增界面
     *
     * @return html
     */
    public function projectdescadd()
    {
        $pid = request()->param('pid');
        $this->assign('pid', $pid);
        return $this->fetch();
    }

    /**
     * 项目详情界面新增详情图片
     *
     * @return void
     */
    public function addProjectDesc()
    {
        $pid = request()->param('pid');
        $pdesc = request()->param('pdesc');
        $projectDesc = Db::name('project')->where('project_id', $pid)->value('project_desc');
        $updateDesc = $pdesc . ',' . $projectDesc;
        $update = Db::name('project')->where('project_id', $pid)->update(['project_desc' => $updateDesc, 'update_at' => time()]);
        if ($update) {
            return objReturn(0, '项目详情添加成功');
        }
        return objReturn(400, '项目详情添加失败');
    }

    /**
     * 删除项目详情头图片
     *
     * @return void
     */
    public function delProDesc()
    {
        $pid = request()->param('pid');
        $descIds = request()->param('descIds');
        $descIdArr = explode(',', $descIds);
        $descOri = Db::name('project')->where('project_id', $pid)->value('project_desc');
        $descOriArr = explode(',', $descOri);
        $descUpdate = [];
        foreach ($descOriArr as $k => $v) {
            $temp = [];
            $temp = explode(':', $v);
            foreach ($descIdArr as $ke => $va) {
                $descUpdate[$k] = $v;
                if ($va == $temp[1]) {
                    unset($descUpdate[$k]);
                    break 1;
                }
            }
        }
        $descUpdate = implode(',', $descUpdate);
        $update = Db::name('project')->where('project_id', $pid)->update(['project_desc' => $descUpdate, 'update_at' => time()]);
        if ($update) {
            return objReturn(0, '项目详情删除成功');
        }
        return objReturn(400, '项目详情删除失败');
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

    public function uploadProjectImg()
    {
        $file = request()->file('file');
        $dirName = '.' . DS . 'static' . DS . 'imgTemp' . DS;
        // 移动到框架应用根目录/static/imgTemp/目录下
        $info = $file->move($dirName);
        if ($info) {
            $fileInfo = $file->getInfo();
           //获取图片的原名称
            $oriName = explode('.', $fileInfo['name']);
            $picSort = $oriName[0];
            $fileName = $info->getSaveName();
            $picSrc = DS . 'static' . DS . 'imgTemp' . DS . $fileName;

            $res['pic'] = $picSrc . ':' . $picSort;
            return objReturn(0, 'success', $res);
        }
        return json($file->getError());
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