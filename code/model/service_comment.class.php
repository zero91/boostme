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
        $service_list = $this->db->fetch_all("SELECT * FROM `service_comment` WHERE sid='$sid' ORDER BY `time` ASC limit $start,$limit");
        foreach ($service_list as &$service) {
            $service['format_time'] = tdate($service['time']);
        }
        return $service_list;
    }

    public function get_comment_num_by_sid($sid) {
        return $this->db->fetch_total("service_comment", "sid='$sid'");
    }

    public function get_by_uid_sid($uid, $sid) {
        return $this->db->fetch_first("SELECT * FROM `service_comment` WHERE `authorid`='$uid' AND `sid`='$sid'");
    }

    public function add($sid, $content, $score, $authorid, $author) {
        $time = time();
        $this->db->query("INSERT INTO `service_comment`(`authorid`,`author`,`sid`,`score`,`content`,`time`) values ('$authorid','$author','$sid','$score','$content','$time')");
        $id = $this->db->insert_id();

        $avg_score = $this->db->result_first("SELECT AVG(`score`) FROM `service_comment` WHERE sid='$sid'");
        $this->db->query("UPDATE service SET `comment_num`=`comment_num`+1,`avg_score`='$avg_score' WHERE `id`=$sid");
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

    public function get_user_support($uid, $comment_id) {
        return $this->db->fetch_all("SELECT * FROM `service_comment_support` WHERE `uid`='$uid' AND `comment_id`='$comment_id'");
    }

    // 0: 顶      1：踩
    public function add_support($uid, $comment_id, $sup_type=0) {
        $time = time();
        $this->db->query("INSERT INTO service_comment_support(uid,comment_id,time,sup_type) VALUES ('$uid','$comment_id','$time','$sup_type')");
        if ($sup_type == 0) {
            $this->db->query("UPDATE `service_comment` SET up=up+1 WHERE `id`='$comment_id'");
        } else {
            $this->db->query("UPDATE `service_comment` SET down=down+1 WHERE `id`=$comment_id");
        }
        return $this->db->affected_rows();
    }

    public function remove_support($uid, $comment_id) {
        $user_support = $this->get_user_support($uid, $comment_id);

        if (!empty($user_support)) {
            $this->db->query("DELETE FROM `service_comment_support` WHERE `uid`='$uid' AND `comment_id`='$comment_id'");
            if ($user_support['sup_type'] == 0) {
                $this->db->query("UPDATE `service_comment` SET up=up-1 WHERE `id`='$comment_id'");
            } else {
                $this->db->query("UPDATE `service_comment` SET down=down-1 WHERE `id`='$comment_id'");
            }
            return true;
        }
        return false;
    }

    private $db;
}

?>
