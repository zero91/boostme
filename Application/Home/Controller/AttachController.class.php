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
            'original' => $file_info[C('EDITOR_UPLOAD.fieldKey')]['name'],
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
            'original' => $img_info[C('EDITOR_UPLOAD.fieldKey')]['name'],
            'width'  => $image_size[0],
            'height' => $image_size[1],
            'state'    => $img_info ? 'SUCCESS' : session('upload_error')
        );
        $this->ajaxReturn($res);
    }

    private function upload($setting) {
        session('upload_error', null);
        $uploader = new \Think\Upload($setting, 'Local');
        $info   = $uploader->upload($_FILES);
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
