<?php

!defined('IN_SITE') && exit('Access Denied');

class questionmodel {
    var $db;
    var $base;

    function questionmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    // 获取问题信息
    function get($qid) {
        $question = $this->db->fetch_first("SELECT * FROM question WHERE qid='$qid'");
        if ($question) {
            $question['format_time'] = tdate($question['time']);
            $question['ip'] = formatip($question['ip']);
            $question['author_avartar'] = get_avatar_dir($question['authorid']);
        }
        return $question;
    }

    function get_list($start=0, $limit=10) {
        $questionlist = array();
        $query = $this->db->query("SELECT * FROM `question` WHERE 1=1 ORDER BY `update_time` DESC limit $start,$limit");
        while ($question = $this->db->fetch_array($query)) {
            $question['format_time'] = tdate($question['update_time']);
            $question['url'] = url('question/view/' . $question['qid'], $question['url']);
            $questionlist[] = $question;
        }
        return $questionlist;
    }

    function get_total_num() {
        return $this->db->result_first("SELECT count(*) FROM question");
    }

    // 前台问题搜索
    function list_by_condition($condition, $start = 0, $limit = 10) {
        $questionlist = array();
        $query = $this->db->query("SELECT * FROM `question` WHERE $condition ORDER BY time DESC limit $start,$limit");
        while ($question = $this->db->fetch_array($query)) {
            $question['format_time'] = tdate($question['time']);
            $question['url'] = url('question/view/' . $question['qid'], $question['url']);
            $questionlist[] = $question;
        }
        return $questionlist;
    }

    function get_hots($start=0, $limit=10) {
        $questionlist = array();
        $timestart = $this->base->time - 7 * 24 * 3600;
        $timeend = $this->base->time;
        $query = $this->db->query("SELECT * FROM question WHERE `time`>$timestart AND `time`<$timeend  ORDER BY answers DESC,views DESC, `time` DESC LIMIT $start,$limit");
        while ($question = $this->db->fetch_array($query)) {
            $question['format_time'] = tdate($question['time']);
            $questionlist[] = $question;
        }
        return $questionlist;
    }

    // 删除问题和问题的回答
    function remove($qids) {
        $this->db->query("DELETE FROM `question` WHERE `qid` IN ($qids)");
        $this->db->query("DELETE FROM `answer_comment ` WHERE `aid` IN (SELECT id FROM answer WHERE `qid` IN($qids))");
        $this->db->query("DELETE FROM `answer_support ` WHERE `aid` IN (SELECT id FROM answer WHERE `qid` IN($qids))");
        $this->db->query("DELETE FROM `answer` WHERE `qid` IN ($qids)");
    }

    // 我的所有提问，用户中心
    function list_by_uid($uid, $status, $start=0, $limit=10) {
        $questionlist = array();
        $sql = "SELECT * FROM question WHERE `authorid`=$uid ORDER BY `time` DESC LIMIT $start,$limit";
        $query = $this->db->query($sql);
        while ($question = $this->db->fetch_array($query)) {
            $question['format_time'] = tdate($question['time']);
            $question['url'] = url('question/view/' . $question['qid'], $question['url']);
            $questionlist[] = $question;
        }
        return $questionlist;
    }

    // 插入问题到question表
    function add($title, $description, $status = 0) {
        $creattime = $this->base->time;
        $uid = $this->base->user['uid'];
        $username = $uid ? $this->base->user['username'] : $this->base->user['ip'];
        (!strip_tags($description, '<img>')) && $description = '';

        $this->db->query("INSERT INTO question SET authorid='$uid',author='$username',title='$title',description='$description',time='$creattime',update_time='$creattime',ip='{$this->base->ip}'");
        $qid = $this->db->insert_id();
        $this->db->query("UPDATE user SET questions=questions+1 WHERE uid=$uid");
        return $qid;
    }

    function update($qid, $title, $description, $status=0) {
        $time = $this->base->time;
        $this->db->query("UPDATE `question` SET title='$title',description='$description',`status`=$status,`time`='$time',`update_time`='$time' WHERE `qid`=$qid");
        return $this->db->affected_rows();
    }

    function update_answers($qid, $delta=1) {
        $this->db->query("UPDATE `question` SET `answers`=`answers`+($delta),`update_time`='{$this->base->time}' WHERE `qid`=$qid");
        return $this->db->affected_rows();
    }

    // 设置qid的更新时间
    function update_time($qid) {
        $this->db->query("UPDATE `question` SET `update_time`='{$this->base->time}' WHERE `qid`='$qid'");
        return $this->db->affected_rows();
    }

    // 更新问题状态
    // 0: 通过审核；1:问题置顶；2:未通过审核
    function update_status($qid, $status=1) {
        $this->db->query("UPDATE `question` set status=$status WHERE `qid`=$qid");
        return $this->db->affected_rows();
    }

    //添加查看次数
    function add_views($qid, $num=1) {
        $this->db->query("UPDATE `question` SET views=views+($num) WHERE `qid`=$qid");
        return $this->db->affected_rows();
    }

    // 更新问题顶
    function add_goods($qid, $num=1) {
        $this->db->query("UPDATE `question` set goods=goods+($num) WHERE `qid`=$qid");
        return $this->db->affected_rows();
    }

    //根据标题搜索问题
    function search_title($title, $status='', $start=0, $limit=10) {
        $questionlist = array();
        $sql = "SELECT * FROM question WHERE title LIKE '%$title%' ";
        !empty($status) && $sql .= " AND STATUS IN ($status) ";
        $sql .= " LIMIT $start,$limit";
        $query = $this->db->query($sql);
        while ($question = $this->db->fetch_array($query)) {
            $question['format_time'] = tdate($question['time']);
            $question['description'] = strip_tags($question['description']);
            $questionlist[] = $question;
        }
        return $questionlist;
    }

    // 是否关注问题
    function is_followed($qid, $uid) {
        return $this->db->result_first("SELECT COUNT(*) FROM question_attention WHERE qid=$qid AND followerid=$uid");
    }

    // 获取问题管理者列表信息
    function get_follower($qid, $start = 0, $limit = 16) {
        $query = $this->db->query("SELECT * FROM question_attention WHERE qid=$qid ORDER BY `time` DESC LIMIT $start,$limit");

        $followerlist = array();
        while ($follower = $this->db->fetch_array($query)) {
            $follower['avatar'] = get_avatar_dir($follower['followerid']);
            $followerlist[] = $follower;
        }
        return $followerlist;
    }
}

?>
