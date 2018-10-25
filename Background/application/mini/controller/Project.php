<?php

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;


class Project extends Controller
{

    public $uid = "";

    public function __construct()
    {
        if (!request()->post()) {
            return objReturn(400, 'Invalid Method');
        }
        if (!request()->param('uid')) {
            return objReturn(400, 'Invalid Param');
        }
        $this->uid = request()->param('uid');
    }

    public function getProjectDesc()
    {

    }

    /**
     * 获取项目列表
     *
     * @return void
     */
    public function getProject()
    {
        $pageNum = request()->param('pageNum');
        $uid = request()->param('uid');
        $projectField = "project_id, project_name, project_img";
        $projectList = getProject($projectField, false, $pageNum);
        if (!$projectList) {
            return objReturn(405, 'No Project Or Failed');
        }
        return objReturn(0, 'success', $projectList);
    }

}