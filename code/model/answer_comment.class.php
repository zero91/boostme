<?php

!defined('IN_SITE') && exit('Access Denied');

class answer_commentmodel {
    var $db;
    var $base;

    function answer_commentmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_aid($aid, $start=0, $limit=10) {
        $commentlist = array();
        $query = $this->db->query("SELECT * FROM `answer_comment` WHERE aid=$aid ORDER BY `time` ASC limit $start,$limit");
        while ($comment = $this->db->fetch_array($query)) {
            $comment['avatar'] = get_avatar_dir($comment['authorid']);
            $comment['format_time'] = tdate($comment['time']);
            $commentlist[] = $comment;
        }
        return $commentlist;
    }

    function add($answerid, $conmment, $authorid, $author) {
        $this->db->query("INSERT INTO `answer_comment`(`aid`,`authorid`,`author`,`content`,`time`) values ('$answerid','$authorid','$author','$conmment','{$this->base->time}')");
        $this->db->query("UPDATE answer SET comments=comments+1 WHERE `id`=$answerid");
    }

    function remove($commentids, $answerid) {
        $commentcount = 1;
        if (is_array($commentids)) {
            $commentcount = count($commentids);
            $commentids = implode(",", $commentids);
        }
        $this->db->query("DELETE FROM answer_comment WHERE `id` IN ($commentids)");
        $this->db->query("UPDATE answer SET comments=comments-$commentcount WHERE `id`=$answerid");
    }
}

?>
