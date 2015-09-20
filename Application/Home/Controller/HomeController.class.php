<?php
namespace Home\Controller;
use Think\Controller;

class HomeController extends Controller {
    // TODO 做统一的用户登录要求处理

    // 空操作，用于输出404页面
    public function _empty() {
        $this->redirect('Index/index');
    }

    protected function _initialize() {
        $uid = is_login();
        $new_msg_num = 0;
        if ($uid > 0) {
            $new_msg_num = D('LatestMessage')->where(array("uid" => $uid))->sum("new_num");
        }
        $this->assign("new_msg_num", $new_msg_num); // 获取最新消息数量

        /*
        // 读取站点配置 *
        $config = api('Config/lists');
        C($config); //添加配置

        if (!C('WEB_SITE_CLOSE')){
            $this->error('站点已经关闭，请稍后访问~');
        }
        */
    }

    // 用户登录检测
    protected function login() {
        is_login() || $this->error('您还没有登录，请先登录！', U('User/login'));
    }
}
