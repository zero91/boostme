<?php
namespace Home\Controller;

class AttachController extends HomeController {

    // ueditor编辑器上传图片处理
    public function ue_upimg() {
        $img_info = $this->upload(C('EDITOR_UPLOAD'));
        $res = array(
            'url'      => $img_info['fullpath'],
            'title'    => htmlspecialchars($_POST['pictitle'], ENT_QUOTES),
            'original' => $img_info[C('EDITOR_UPLOAD.fieldKey')]['name'],
            'state'    => $img_info ? 'SUCCESS' : session('upload_error')
        );
        $this->ajaxReturn($res);
    }

    // ueditor编辑器上传附件处理
    public function ue_upload() {
        $file_info = $this->upload(C('DOWNLOAD_UPLOAD'));
        $res = array(
            'url'      => $file_info['fullpath'],
            'title'    => htmlspecialchars($_POST['pictitle'], ENT_QUOTES),
            'original' => $file_info[C('DOWNLOAD_UPLOAD.fieldKey')]['name'],
            'state'    => $file_info ? 'SUCCESS' : session('upload_error')
        );
        $this->ajaxReturn($res);
    }

    // 用户上传头像
    public function upload_avatar() {
        $img_info = $this->upload(C('AVATAR_UPLOAD'));
        $image_size = getimagesize(WEB_ROOT . $img_info['fullpath']);
        $res = array(
            'url'      => $img_info['fullpath'],
            'title'    => htmlspecialchars($_POST['pictitle'], ENT_QUOTES),
            'original' => $img_info[C('AVATAR_UPLOAD.fieldKey')]['name'],
            'width'  => $image_size[0],
            'height' => $image_size[1],
            'state'    => $img_info ? 'SUCCESS' : session('upload_error')
        );
        $this->ajaxReturn($res);
    }

    // @brief  upload_student  用户上传学生证
    // @request  POST
    // @param  array  上传文件相关信息，格式同通过form提交
    //
    // @ajaxReturn  成功 - array("success" => true)
    //              失败 - array("success" => false, "error" => 错误码)
    //
    // @error  101  用户尚未登录
    // @error  102  数据更新失败
    // @error  103  上传失败
    //
    public function upload_student() {
        $uid = is_login();
        if ($uid > 0) {
            $img_info = $this->upload(C('STUDENT_UPLOAD'));
            if ($img_info) {
                $save_fname = $img_info[C('STUDENT_UPLOAD.fieldKey')]['savepath'];
                $save_fname .= $img_info[C('STUDENT_UPLOAD.fieldKey')]['savename'];

                $filename = WEB_ROOT . C('STUDENT_UPLOAD.rootPath') . "/" . $save_fname;
                $filename = str_replace('//', '/', $filename);
                $filename = str_replace('./', '/', $filename);
                if (file_exists($filename)) {
                    if (D('UserResume')->update(array("uid" => $uid, "student" => $save_fname))) {
                        $this->ajaxReturn(array("success" => true));
                    }
                }
                $this->ajaxReturn(array("success" => false, "error" => 102));
            } else {
                $res = array(
                    "success" => false,
                    "error" => 103,
                    "info" => session('upload_error')
                );
                $this->ajaxReturn($res);
            }
        } else {
            $this->ajaxReturn(array("success" => false, "error" => 101));
        }
    }

    private function upload($setting) {
        session('upload_error', null);
        $uploader = new \Think\Upload($setting, 'Local');
        $info = $uploader->upload($_FILES);
        if ($info) {
            $key = $setting['fieldKey'];
            $url = $setting['rootPath'] . $info[$key]['savepath'] . $info[$key]['savename'];
            $url = str_replace('./', '/', $url);
            $info['fullpath'] = __ROOT__ . $url;
        }
        session('upload_error', $uploader->getError());
        return $info;
    }
}
