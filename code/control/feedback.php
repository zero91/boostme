<?php

!defined('IN_SITE') && exit('Access Denied');

class feedbackcontrol extends base {

    function feedbackcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('feedback');
    }

    function onadd() {
        $content = htmlspecialchars($this->post['fb_content']);

        if (empty($content)) {
            $this->message("反馈内容不能为空，谢谢!", 'STOP');
        }

        if (isset($this->base->setting['max_feedback_num']) && $this->base->setting['max_feedback_num'] && !$_ENV['user']->is_allowed_register()) {
            $this->message("您反馈速度太快了，Boostme已经跟不上您的脚步了，先歇一会吧!", 'STOP');
        }

        $regular = $this->post['fb_regular'];
        $fid = $_ENV['feedback']->add($content, $regular);

        // 在这里为了防止对用户体验的伤害，不对添加反馈信息失败做特殊处理
        $this->message("我们已经收到您的反馈，非常感谢!", 'STOP');
    }

    function onview() {
        echo "Hello World";
    }
}

?>
