<?php

!defined('IN_SITE') && exit('Access Denied');

class answer_commentmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_by_aid($aid, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM `answer_comment` WHERE aid=$aid ORDER BY `time` ASC limit $start,$limit");
    }

    public function add($aid, $content, $authorid, $author) {
        $time = time();
        $answer = $this->db->fetch_first("SELECT * FROM answer WHERE id='$aid'");

        $this->db->query("INSERT INTO `answer_comment`(`aid`,`authorid`,`author`,`content`,`time`) values ('$aid','$authorid','$author','$content','$time')");
        $id = $this->db->insert_id();

        $this->db->query("UPDATE answer SET comments=comments+1 WHERE `id`='$aid'");
        $this->db->query("UPDATE `question` SET `answers`=`answers`+1,`update_time`='$time' WHERE `qid`={$answer['qid']}");

        return $id;
    }

    public function remove($commentids, $aid) {
        $commentcount = 1;
        if (is_array($commentids)) {
            $commentcount = count($commentids);
            $commentids = implode(",", $commentids);
        }
        $this->db->query("DELETE FROM answer_comment WHERE `id` IN ($commentids)");

        $answer = $this->db->fetch_first("SELECT * FROM answer WHERE id='$aid'");
        $this->db->query("UPDATE answer SET comments=comments-$commentcount WHERE `id`='$aid'");
        $this->db->query("UPDATE question SET answers=answers-$commentcount WHERE `qid`='{$answer['qid']}'");
        return $this->db->affected_rows();
    }

    private $db;
}

?>
