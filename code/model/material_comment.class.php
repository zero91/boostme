<?php

!defined('IN_SITE') && exit('Access Denied');

class material_commentmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    // 获取评论内容，不包含评分
    public function get_by_mid($mid, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM `material_comment` WHERE mid=$mid ORDER BY `time` DESC limit $start,$limit");
    }

    public function get_comment_num_by_mid($mid) {
        return $this->db->fetch_total("material_comment", "mid='$mid'");
    }

    // 获取评论以及评分信息
    public function get_full_by_mid($mid, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT comment.*,score.score FROM `material_comment` as comment,`material_score` as score WHERE comment.mid=$mid AND comment.authorid=score.uid AND comment.mid=score.mid ORDER BY `time` DESC limit $start,$limit");
    }

    public function get_user_comment($uid, $mid) {
        return $this->db->fetch_first("SELECT comment.*,score.score FROM `material_comment` as comment,`material_score` as score WHERE comment.mid=$mid AND comment.authorid='$uid' AND comment.authorid=score.uid AND comment.mid=score.mid");
    }

    public function add($mid, $comment, $authorid, $author) {
        $time = time();
        $this->db->query("INSERT INTO `material_comment`(`mid`,`authorid`,`author`,`content`,`time`) values ('$mid','$authorid','$author','$comment','$time')");

        $id = $this->db->insert_id();

        $avg_score = $this->db->result_first("SELECT AVG(score) FROM `material_score` WHERE `mid`='$mid'");

        $this->db->query("UPDATE material SET comment_num=comment_num+1,avg_score='$avg_score' WHERE `id`=$mid");
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

    public function get_user_support($uid, $comment_id) {
        return $this->db->fetch_all("SELECT * FROM `material_comment_support` WHERE `uid`='$uid' AND `comment_id`='$comment_id'");
    }

    // 0: 顶      1：踩
    public function add_support($uid, $comment_id, $sup_type=0) {
        $time = time();
        $this->db->query("INSERT INTO material_comment_support(uid,comment_id,time,sup_type) VALUES ('$uid','$comment_id','$time','$sup_type')");
        if ($sup_type == 0) {
            $this->db->query("UPDATE `material_comment` SET up=up+1 WHERE `id`='$comment_id'");
        } else {
            $this->db->query("UPDATE `material_comment` SET down=down+1 WHERE `id`=$comment_id");
        }
        return $this->db->affected_rows();
    }

    public function remove_support($uid, $comment_id) {
        $user_support = $this->get_user_support($uid, $comment_id);

        if (!empty($user_support)) {
            $this->db->query("DELETE FROM `material_comment_support` WHERE `uid`='$uid' AND `comment_id`='$comment_id'");
            if ($user_support['sup_type'] == 0) {
                $this->db->query("UPDATE `material_comment` SET up=up-1 WHERE `id`='$comment_id'");
            } else {
                $this->db->query("UPDATE `material_comment` SET down=down-1 WHERE `id`='$comment_id'");
            }
            return true;
        }
        return false;
    }

    public function get_comment($comment_id) {
        return $this->db->fetch_first("SELECT * FROM `material_comment` WHERE `id`='$comment_id'");
    }

    private $db;
}

?>
