<?php

namespace Home\Controller;

// 资源访问权限控制类
class ResourceController extends HomeController {
    public function index(){
    }

    public function onrequest() {
        // TODO 访问敏感资源的权限控制
        /*
        $uid = intval($this->post['uid']);
        $data_type = $this->post['data_type'];

        if ($uid != $this->user['uid']) {
            return;
        }

        $resume = $_ENV['userresume']->get_by_uid($uid);

        $filepath = "";
        if ($data_type == 'ID')             $filepath = WEB_ROOT . "/" . $resume['ID_path'];
        else if ($data_type == 'studentID') $filepath = WEB_ROOT . "/" . $resume['studentID'];
        else if ($data_type == 'resume')    $filepath = WEB_ROOT . "/" . $resume['resume_path'];

        header("Content-Type: application/force-download");
        header("Content-Disposition: attachment; filename=$data_type");
        if (file_exists($filepath)) {
            echo readfromfile($filepath);
        }
        */
    }
}
