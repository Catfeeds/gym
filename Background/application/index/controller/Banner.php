<?php
namespace app\index\controller;

use \think\Controller;
use \think\Request;
use \think\Cache;
use \think\Db;
use \think\Session;
use \think\File;
use app\index\model\Banner as bannerdata;

class Banner extends Controller{

	public function bannerlist(){
		// 调用公共函数
		$field = 'banner_id, img, status, sort';
		$bannerData = getBanner($field,true);
		$this->assign('bannerData',$bannerData);
        return $this->fetch();
	}

	/**
	 * 添加banner图
	 * @return html 页面
	 */
	public function banneradd(){
        return $this->fetch();
	}

	/**
	 * 添加banner
	 * @param ary 返回结果
	 */
	public function addBanner(Request $request){
		$banner = new bannerdata;
		$count = $banner -> field('banner_id') ->where('status',1) ->where('status','<>',3)->count();
		// 不能超过5张
		if($count>5){
			$add['status'] = 0;
		}else{
			$add['status'] = intval($request->param('banner_active'));
		} 
		$add['sort'] = intval($request->param('banner_sort')); 
		$add['created_at'] = time();
        $file = request()->file('file');
        // 移动到框架应用根目录/static/img/banner目录下
            $info = $file->move(PUBLIC_PATH .'static'.DS.'img'.DS.'banner');
            if ($info) {
                $str = $info->getSaveName();
                // 路径
                $bannersrc = DS.'static'.DS.'img'.DS.'banner'.DS. $str;
                $add['img'] = $bannersrc;
			    // 调用公共函数保存，参数false为新增
	            $res = saveData('banner',$add,false); 
                if($res){
                    return objReturn(0,'添加成功');              
                }else{
                    return objReturn(400,'添加失败');
                }	                      
			}
                return objReturn(400,'添加失败');
        }

    /**
     * 获取当前banner id
     * @return json banner 启用结果
     */
    public function bannerStart(Request $request){
        $where['banner_id'] = intval($request->param('id'));
        $where['status'] = 2;
        // 调用公共函数，参数true为更新
        $update = saveData('banner',$where,true);
        if($update){
            return objReturn(0,'启用成功');
        }else{
            return objReturn(400,'启用失败');
        }
    }

    /**
     * 获取当前banner id
     * @return json banner 停用结果
     */
    public function bannerStop(Request $request){
        $where['banner_id'] = intval($request->param('id'));
        $where['status'] = 1;
        // 调用公共函数，参数true为更新
        $update = saveData('banner',$where,true);
        if($update){
            return objReturn(0,'停用成功');
        }else{
            return objReturn(400,'停用失败');
        }
    }

    /**
     * 获取当前banner id
     * @return json 删除banner结果
     */
    public function bannerDel(Request $request){
        $where['banner_id'] = intval($request->param('id'));
        $where['status'] = 3;
        // 调用公共函数，参数true为更新
        $update = saveData('banner',$where,true);
        if($update){
            return objReturn(0,'删除成功');
        }else{
            return objReturn(400,'删除失败');
        }
    }

    /**
     * 获取当前banner id
     * @return json 更改banner排序结果
     */
    function bannerSortEdit(Request $request){
        $where['banner_id'] = intval($request->param('id'));
        $where['sort'] = intval($request->param('banner_sort'));
        // 调用公共函数，参数true为更新
        $update = saveData('banner',$where,true);
        if($update){
            return objReturn(0,'更改成功！');
        }else{
            return objReturn(400,'更改失败！');
        }        
    }
}
