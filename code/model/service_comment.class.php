<?php

!defined('IN_SITE') && exit('Access Denied');

class service_commentmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get_by_id($id) {
        return $this->db->fetch_first("SELECT * FROM `service_comment` WHERE id='$id'");
    }

    public function get_by_sid($sid, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM `service_comment` WHERE sid='$sid' ORDER BY `time` ASC limit $start,$limit");
    }

    public function get_comment_num_by_sid($sid) {
        return $this->db->fetch_total("service_comment", "sid='$sid'");
    }

    public function add($authorid, $author, $sid, $score, $content) {
        $time = time();
        $this->db->query("INSERT INTO `service_comment`(`authorid`,`author`,`sid`,`score`,`content`,`time`) values ('$authorid','$author','$sid','$score','$content','$time')");
        $id = $this->db->insert_id();

        $avg_score = $this->db->result_first("SELECT AVG(`score`) FROM `service_comment` WHERE sid='$sid'");
        $this->db->query("UPDATE service SET `service_num`=`service_num`+1,`avg_score`='$avg_score' WHERE `sid`=$sid");
        return $id;
    }

    public function update($id, $score, $content) {
        $this->db->query("UPDATE service SET `score`='$score',`content`='$content' WHERE `id`=$id");

        $comment = $this->get_by_id($id);
        $avg_score = $this->db->result_first("SELECT AVG(`score`) FROM `service_comment` WHERE sid='{$comment['sid']}'");
        $this->db->query("UPDATE service SET `avg_score`='$avg_score' WHERE `sid`='{$comment['sid']}'");

        return $this->db->affected_rows();
    }

    public function remove($id) {
        $this->db->query("DELETE FROM service_comment WHERE `id`='$id'");
        return $this->db->affected_rows();
    }

    private $db;
}

?>
