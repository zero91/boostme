<?php

namespace Home\Controller;

// 资源访问权限控制类
class ResourceController extends HomeController {
    public function index() {
    }

    public function student() {
        $uid = is_login();
        if ($uid > 0) {
            $student = D('UserResume')->where(array("uid" => $uid))->getField("student");
            if (isset($student)) {
                $filename = WEB_ROOT . C('STUDENT_UPLOAD.rootPath') . "/" . $student;
                $filename = str_replace('//', '/', $filename);
                $filename = str_replace('./', '/', $filename);

                header("Content-Type: application/force-download");
                header("Content-Disposition: attachment; filename=$data_type");
                if (file_exists($filename)) {
                    echo file_get_contents($filename);
                }
            }
        }
        exit(0);
    }
}
