<?php

!defined('IN_SITE') && exit('Access Denied');

class feedbackcontrol extends base {

    function feedbackcontrol(& $get, & $post) {
        $this->base($get, $post);
        $this->load('feedback');
    }

    function onadd() {
        $content = htmlspecialchars($this->post['fb_content']);
        $regular = $this->post['fb_regular'];

        $fid = $_ENV['feedback']->add($content, $regular);

        // 在这里为了防止对用户体验的伤害，不对添加反馈信息失败做特殊处理
        $this->message("我们已经收到您的反馈，非常感谢!", 'BACK');
    }

    function onview() {
        echo "Hello World";
    }
}

?>
