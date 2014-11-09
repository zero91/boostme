<?php

!defined('IN_SITE') && exit('Access Denied');

class material_commentmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_by_mid($mid, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM `material_comment` WHERE mid=$mid ORDER BY `time` ASC limit $start,$limit");
    }

    public function add($mid, $comment, $authorid, $author) {
        $time = time();
        $this->db->query("INSERT INTO `material_comment`(`mid`,`authorid`,`author`,`content`,`time`) values ('$mid','$authorid','$author','$comment','$time')");

        $id = $this->db->insert_id();

        $this->db->query("UPDATE material SET comment_num=comment_num+1 WHERE `id`=$mid");
        return $id;
    }

    public function remove($commentids, $mid) {
        $commentcount = 1;
        if (is_array($commentids)) {
            $commentcount = count($commentids);
            $commentids = implode(",", $commentids);
        }
        $this->db->query("DELETE FROM material_comment WHERE `id` IN ($commentids)");
        $this->db->query("UPDATE material SET comment_num=comment_num-$commentcount WHERE `mid`=$mid");
        return $this->db->affected_rows();
    }

    private $db;
}

?>
