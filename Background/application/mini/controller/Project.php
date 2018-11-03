<?php

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;


class Project extends Controller
{

    public function __construct()
    {
        if (!request()->post()) {
            return objReturn(400, 'Invalid Method');
        }
        if (!request()->param('uid')) {
            return objReturn(400, 'Invalid Param');
        }
    }

    public function getProjectDesc()
    {
        $pid = request()->param('pid');
        $uid = request()->param('uid');

        $projectDesc = Db::name('project')->where('project_id', $pid)->field('project_desc, project_video')->find();
        if (!$projectDesc) {
            return objReturn(400, 'failed');
        }

        if ($projectDesc['project_desc']) {
            $projectDescPic = explode(',', $projectDesc['project_desc']);
            $proSort = [];
            $proArr = [];
            foreach ($projectDescPic as $k => $v) {
                $temp = [];
                $temp = explode(':', $v);
                $proSort[] = $temp[1];
                $proArr[] = config('SITEROOT') . $temp[0];
            }
            array_multisort($proSort, SORT_ASC, SORT_NUMERIC, $proArr);
        }

        $res['desc'] = $projectDesc['project_desc'] ? $proArr : [];
        $res['video'] = $projectDesc['project_video'] ? config('SITEROOT') . $projectDesc['project_video'] : "";

        return objReturn(0, 'success', $res);
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
        foreach ($projectList as &$info) {
            if (isset($info['project_img'])) {
                $info['project_img'] = config('SITEROOT') . $info['project_img'];
            }
        }
        return objReturn(0, 'success', $projectList);
    }

}