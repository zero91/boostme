<?php

!defined('IN_SITE') && exit('Access Denied');

class attachmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function movetmpfile($attach, $targetfile) {
        forcemkdir(dirname($targetfile));
        if (copy($attach['tmp_name'], $targetfile) || move_uploaded_file($attach['tmp_name'], $targetfile)) {
            return 1;
        }

        if (is_readable($attach['tmp_name'])) {
            $fp = fopen($attach['tmp_name'], 'rb');
            flock($fp, 2);
            $attachedfile = fread($fp, $attach['size']);
            fclose($fp);
            $fp = fopen($targetfile, 'wb');
            flock($fp,2);
            if (fwrite($fp, $attachedfile)) {
                unlink($attach['tmp_name']);
            }
            fclose($fp);
            return 1;
        }
        return 0;
    }

    public function add($uid, $filename, $ftype, $fsize, $location, $isimage=1) {
        $time = time();
        $this->db->query("INSERT INTO attach(time,filename,filetype,filesize,location,isimage,uid) VALUES ('$time','$filename','$ftype','$fsize','$location',$isimage,$uid)");
        return $this->db->insert_id();
    }

    private $db;
}

?>
