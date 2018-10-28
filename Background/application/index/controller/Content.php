<?php

namespace app\index\controller;

use \think\Controller;
use \think\Request;
use \think\Session;
use think\Db;
use app\index\model\Banner;

class Content extends Controller
{
    /**
     * bannerList展示界面
     *
     * @return html
     */
    public function bannerlist()
    {
		// 1 获取banner
        $bannerList = getBanner(true);
        // 如果有bannerlist 需要找到跳转对应的项目名称
        $projectIds = [];
        if ($bannerList) {
            foreach ($bannerList as $k => $v) {
                if ($v['nav_type'] == 1) {
                    $projectIds[] = $v['nav_id'];
                }
            }
            if (count($projectIds) > 0) {
                $projectNames = Db::name('project')->where('project_id', 'in', $projectIds)->field('project_name, project_id')->select();
                // 匹配banner 如果有不匹配则需要更新
                $updateBanner = [];
                foreach ($bannerList as $k => $v) {
                    $isMatch = false;
                    foreach ($projectNames as $ke => $va) {
                        if ($va['project_id'] == $v['nav_id']) {
                            $bannerList[$k]['nav_name'] = $va['project_name'];
                            $isMatch = true;
                            break 1;
                        }
                    }
                    if (!$isMatch) {
                        $temp = [];
                        $temp['banner_id'] = $v['banner_id'];
                        $temp['nav_type'] = 0;
                        $temp['nav_id'] = 0;
                        $updateBanner[] = $temp;
                        $bannerList[$k]['nav_type'] = 0;
                        $bannerList[$k]['nav_id'] = 0;
                    }
                }
                if (count($updateBanner) > 0) {
                    $bannerDB = new BannerDB;
                    $bannerDB->isUpdate()->saveAll($updateBanner);
                }
            }
        } else {
            $bannerList = [];
        }
        // 2 获取project
        $projectField = "project_id, project_name";
        $projectList = getProject($projectField, false);
        $this->assign('projectList', $projectList);
        $this->assign('bannerList', $bannerList);
        return $this->fetch();
    }

    /**
     * 删除banner
     *
     * @return void
     */
    public function delBanner()
    {
        $bannerId = intval(request()->param('bannerId'));
        $update = Db::name('banner')->where('banner_id', $bannerId)->update(['status' => 3, 'update_at' => time(), 'update_by' => Session::get('adminId')]);
        if ($update) {
            return objReturn(0, '删除成功');
        } else {
            return objReturn(400, '删除失败');
        }
    }

    /**
     * 将banner 状态改为展示
     *
     * @return void
     */
    public function activatBanner()
    {
        $bannerId = intval(request()->param('bannerId'));
        $update = Db::name('banner')->where('banner_id', $bannerId)->update(['status' => 1, 'update_at' => time(), 'update_by' => Session::get('adminId')]);
        if ($update) {
            return objReturn(0, '展示状态修改成功');
        } else {
            return objReturn(400, '展示状态修改失败');
        }
    }

    /**
     * 将banner 状态改为不展示
     *
     * @return void
     */
    public function closeBanner()
    {
        $bannerId = intval(request()->param('bannerId'));
        $update = Db::name('banner')->where('banner_id', $bannerId)->update(['status' => 2, 'update_at' => time(), 'update_by' => Session::get('adminId')]);
        if ($update) {
            return objReturn(0, '展示状态修改成功');
        } else {
            return objReturn(400, '展示状态修改失败');
        }
    }

    /**
     * 修改banner的排序
     *
     * @return void
     */
    public function editBannerSort()
    {
        $bannerId = intval(request()->param('bannerId'));
        $sort = intval(request()->param('sort'));
        $navType = intval(request()->param('navType'));
        $navId = $navType == 0 ? 0 : intval(request()->param('navId'));
        $update = Db::name('banner')->where('banner_id', $bannerId)->update(['sort' => $sort, 'nav_type' => $navType, 'nav_id' => $navId, 'update_at' => time(), 'update_by' => Session::get('adminId')]);
        if ($update) {
            return objReturn(0, 'Banner排序修改成功');
        } else {
            return objReturn(400, 'Banner排序修改失败');
        }
    }

    /**
     * 新增banner
     *
     * @return html
     */
    public function banneradd()
    {
        // 1 获取所有的project
        $projectField = "project_id, project_name";
        $projectList = getProject($projectField, false);
        $this->assign('projectList', $projectList);
        return $this->fetch();
    }

    /**
     * 新增banner
     *
     * @return void
     */
    public function addBanner()
    {
        if (!Session::has('bannerSrc')) {
            return objReturn(406, '请先上传Banner图片');
        }
        $banner['nav_type'] = intval(request()->param('nav_type'));
        $banner['nav_id'] = $banner['nav_type'] == 1 ? intval(request()->param('nav_type')) : 0;
        $banner['sort'] = intval(request()->param('sort'));
        $banner['status'] = intval(request()->param('active'));
        $banner['banner_img'] = Session::get('bannerSrc');
        $banner['created_at'] = time();
        $insert = Db::name('banner')->insert($banner);
        if ($insert) {
            // 移除Session
            Session::delete('bannerSrc');
            return objReturn(0, '添加Banner成功');
        } else {
            return objReturn(400, '添加Banner失败');
        }
    }

    /**
     * 接收上传的banner 图片
     *
     * @return void
     */
    public function uploadBanner()
    {
        $file = request()->file('file');
        $dirName = '.' . DS . 'static' . DS . 'imgTemp' . DS;
        // 是否存在session
        if (Session::has('bannerSrc')) {
            // 删除实际文件
            @unlink(Session::get('bannerSrc'));
            // 删除session信息
            Session::delete('bannerSrc');
        }
        // 移动到框架应用根目录/static/imgTemp/目录下
        $info = $file->move($dirName);
        if ($info) {
            $fileName = $info->getSaveName();
            $bannerSrc = DS . 'static' . DS . 'imgTemp' . DS . $fileName;
            // 存路径名到session
            Session::set('bannerSrc', $bannerSrc);
            return objReturn(0, '上传成功！', $bannerSrc);
        }
        return objReturn(400, '上传失败！');
    }

    /***************************/
    /********  用户反馈  ********/
    /***************************/

    /**
     * 用户反馈界面
     *
     * @return html
     */
    public function feedback()
    {
        $feedbackList = getFeedBack();
        $this->assign('fbList', $feedbackList ? $feedbackList : []);
        return $this->fetch();
    }

    /**
     * 回复用户反馈
     *
     * @return void
     */
    public function feedbackReply()
    {
        $feedbackId = intval(request()->param('idx'));
        $reply = htmlspecialchars(request()->param('reply'));
        $update = Db::name('feedback')->where('idx', $feedbackId)->update(['reply' => $reply, 'reply_at' => time(), 'status' => 2, 'reply_by' => Session::get('adminId')]);
        if ($update) {
            return objReturn(0, '回复成功');
        } else {
            return objReturn(400, '回复失败');
        }
    }

    /***************************/
    /********  用户协议  ********/
    /***************************/

    /**
     * 用户协议展示界面
     *
     * @return html
     */
    public function clause()
    {
        $clause = Db::name('clause')->where('clause_id', 1)->value('content');
        $this->assign('clause', $clause);
        return $this->fetch();
    }

    /**
     * 更新用户协议
     *
     * @return void
     */
    public function updateClause()
    {
        $content = htmlspecialchars(request()->param('content'));
        $update = Db::name('clause')->where('clause_id', 1)->update(['update_at' => time(), 'content' => $content]);
        if ($update) {
            return objReturn(0, '修改成功');
        } else {
            return objReturn(400, '修改失败');
        }
    }

    /***************************/
    /********  关于我们  ********/
    /***************************/

    /**
     * 关于我们界面
     *
     * @return html
     */
    public function aboutus()
    {
        $aboutUs = Db::name('mini_setting')->where('setting_id', 1)->value('about_us');
        $this->assign('aboutUs', $aboutUs);
        return $this->fetch();
    }

    /**
     * 更新关于我们
     *
     * @return void
     */
    public function updateAboutUs()
    {
        $content = htmlspecialchars(request()->param('content'));
        $update = Db::name('mini_setting')->where('setting_id', 1)->update(['update_at' => time(), 'content' => $content, 'update_by' => Session::get('adminId')]);
        if ($update) {
            return objReturn(0, '修改成功');
        } else {
            return objReturn(400, '修改失败');
        }
    }

}
