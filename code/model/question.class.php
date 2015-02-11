<?php

!defined('IN_SITE') && exit('Access Denied');

class questionmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    // 获取问题信息
    public function get($qid) {
        $question = $this->db->fetch_first("SELECT * FROM question WHERE qid='$qid'");
        $question['format_time'] = tdate($question['time']);
        return $question;
    }

    public function get_list($start=0, $limit=10) {
        $sql = "SELECT * FROM `question` ORDER BY `update_time` DESC limit $start,$limit";
        $question_list = $this->db->fetch_all($sql);
        foreach ($question_list as &$question) {
            $question['avatar'] = get_avatar_dir($question['authorid']);
            $question['format_time'] = tdate($question['time']);
            $question['format_update_time'] = tdate($question['update_time']);
            $question['strip_description'] = strip_tags($question['description']);
            $img_match = fetch_img_tag($question['description']);
            $question['images'] = $img_match[0];
        }
        return $question_list;
    }

    public function get_total_num() {
        return $this->db->fetch_total("question");
    }

    // 前台问题搜索
    public function list_by_condition($condition, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM `question` WHERE $condition ORDER BY `update_time` DESC limit $start,$limit");
    }

    public function get_hots($start=0, $limit=10) {
        $timestart = time() - 7 * 24 * 3600;
        $timeend = time();
        return $this->db->fetch_all("SELECT * FROM question WHERE `time`>$timestart AND `time`<$timeend  ORDER BY answers DESC,views DESC, `time` DESC LIMIT $start,$limit");
    }

    // 删除问题和问题的回答
    public function remove($qids) {
        $this->db->query("DELETE FROM `answer_comment ` WHERE `aid` IN (SELECT id FROM answer WHERE `qid` IN($qids))");
        $affected_rows = $this->db->affected_rows();

        $this->db->query("DELETE FROM `answer_support ` WHERE `aid` IN (SELECT id FROM answer WHERE `qid` IN($qids))");
        $affected_rows += $this->db->affected_rows();

        $this->db->query("DELETE FROM `answer` WHERE `qid` IN ($qids)");
        $affected_rows += $this->db->affected_rows();

        $this->db->query("DELETE FROM `question` WHERE `qid` IN ($qids)");
        $affected_rows = $this->db->affected_rows();
        return $affected_rows;
    }

    // 我的所有提问，用户中心
    public function list_by_uid($uid, $status, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM question WHERE `authorid`=$uid ORDER BY `time` DESC LIMIT $start,$limit");
    }

    // 插入问题到question表
    public function add($uid, $username, $title, $description, $status=0) {
        $creattime = time();
        (!strip_tags($description, '<img>')) && $description = '';

        $this->db->query("INSERT INTO question SET authorid='$uid',author='$username',title='$title',description='$description',time='$creattime',update_time='$creattime',ip='" . getip() . "'");
        $qid = $this->db->insert_id();
        $this->db->query("UPDATE user SET questions=questions+1 WHERE uid=$uid");
        return $qid;
    }

    public function update($qid, $title, $description, $status=0) {
        $time = time();
        $this->db->query("UPDATE `question` SET title='$title',description='$description',`status`=$status,`update_time`='$time' WHERE `qid`=$qid");
        return $this->db->affected_rows();
    }

    public function update_answers($qid, $delta=1) {
        $time = time();
        $this->db->query("UPDATE `question` SET `answers`=`answers`+($delta),`update_time`='$time' WHERE `qid`=$qid");
        return $this->db->affected_rows();
    }

    // 设置qid的更新时间
    public function update_time($qid) {
        $time = time();
        $this->db->query("UPDATE `question` SET `update_time`='$time' WHERE `qid`='$qid'");
        return $this->db->affected_rows();
    }

    // 更新问题状态
    // 0: 通过审核；1:问题置顶；2:未通过审核
    public function update_status($qid, $status=1) {
        $this->db->query("UPDATE `question` set status=$status WHERE `qid`=$qid");
        return $this->db->affected_rows();
    }

    //添加查看次数
    public function add_views($qid, $num=1) {
        $this->db->query("UPDATE `question` SET views=views+($num) WHERE `qid`=$qid");
        return $this->db->affected_rows();
    }

    // 更新问题顶
    public function add_goods($qid, $num=1) {
        $this->db->query("UPDATE `question` set goods=goods+($num) WHERE `qid`=$qid");
        return $this->db->affected_rows();
    }

    //根据标题搜索问题
    public function search_title($title, $status=0, $start=0, $limit=10) {
        $sql = "SELECT * FROM question WHERE title LIKE '%$title%' ";
        !empty($status) && $sql .= " AND STATUS IN ($status) ";
        $sql .= " LIMIT $start,$limit";
        return $this->db->fetch_all($sql);
    }

    // 是否关注问题
    public function is_followed($qid, $uid) {
        return $this->db->fetch_total("question_attention",  "qid=$qid AND followerid=$uid");
    }

    // 获取问题管理者列表信息
    public function get_follower($qid, $start = 0, $limit = 16) {
        return $this->db->fetch_all("SELECT * FROM question_attention WHERE qid=$qid ORDER BY `time` DESC LIMIT $start,$limit");
    }

    private $db;
}

?>
