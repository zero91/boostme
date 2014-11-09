<?php

!defined('IN_SITE') && exit('Access Denied');

class answermodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    public function get($id) {
        return $this->db->fetch_first("SELECT * FROM answer WHERE id='$id'");
    }

    // 添加答案
    public function add($uid, $username, $qid, $title, $content) {
        $time = time();
        $this->db->query("INSERT INTO answer SET qid='$qid',title='$title',author='$username',authorid='$uid',time='$time',content='$content',ip='" . getip() . "'");
        return $this->db->insert_id();
    }

    // 根据qid获取答案的列表，用于在浏览一个问题的时候显示用
    public function list_by_qid($qid, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM answer WHERE qid=$qid ORDER BY time ASC LIMIT $start,$limit");
    }

    // 根据uid获取答案的列表，用于在用户中心，我的回答显示
    public function list_by_uid($uid, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM `answer` WHERE `authorid`=$uid ORDER BY `time` DESC LIMIT $start,$limit");
    }

    // 修改回答
    public function update_content($aid, $content) {
        $this->db->query("UPDATE `answer` set content='$content' WHERE `id`=$aid");
        return $this->db->affected_rows();
    }

    // 时间段内问题数目
    public function rownum_by_time($uid, $hours = 1) {
        $endtime = time();
        $starttime = $endtime - $hours * 3600;
        return $this->db->fetch_total('answer', " `time`>$starttime AND `time`<$endtime AND authorid=$uid");
    }

    public function remove_by_aid($aid) {
        $answer = $this->get($aid);
        $this->db->query("DELETE FROM `answer` WHERE `id`=$aid");

        $comment_num = $this->db->fetch_total("answer_comment", "`aid`='$aid'");
        $this->db->query("DELETE FROM `answer_comment ` WHERE `aid`='$aid'");
        $this->db->query("DELETE FROM `answer_support ` WHERE `aid`='$aid'");

        $total_change_num = $comment_num + 1;
        $this->db->query("UPDATE `question` SET answers=answers-$total_change_num WHERE `qid`={$answer['qid']}");
        return $this->db->affected_rows();
    }

    public function remove_support($uid, $aid) {
        $this->db->query("DELETE FROM `answer_support ` WHERE `aid`='$aid'");
        $affected_rows = $this->db->affected_rows();
        if ($affected_rows > 0) {
            $this->db->query("UPDATE `answer` SET `supports`=supports-1 WHERE `id`=$aid");
        }
        return $affected_rows;
    }

    public function add_support($uid, $aid) {
        $time = time();
        $this->db->query("INSERT INTO answer_support(uid,aid,time) VALUES ('$uid','$aid','$time')");
        if ($this->db->insert_id() > 0) {
            $this->db->query("UPDATE `answer` SET `supports`=supports+1 WHERE `id`=$aid");
        }
        return $this->db->affected_rows();
    }

    private $db;
}

?>
