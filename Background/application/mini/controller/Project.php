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

}