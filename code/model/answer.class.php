<?php

!defined('IN_SITE') && exit('Access Denied');

class answermodel {
    var $db;
    var $base;

    function answermodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get($id) {
        return $this->db->fetch_first("SELECT * FROM answer WHERE id='$id'");
    }

    // 添加答案
    function add($qid, $title, $content) {
        $uid = $this->base->user['uid'];
        $username = $this->base->user['username'];
        $this->db->query("INSERT INTO answer SET qid='$qid',title='$title',author='$username',authorid='$uid',time='{$this->base->time}',content='$content',ip='{$this->base->ip}'");
        $aid = $this->db->insert_id();
        $this->db->query("UPDATE question SET answers=answers+1 WHERE qid=$qid");
        return $aid;
    }

    // 根据qid获取答案的列表，用于在浏览一个问题的时候显示用
    function list_by_qid($qid, $ordertype=1, $rownum=0, $start=0, $limit=10) {
        $answerlist = array();
        if (1 == $ordertype) {
            $timeorder = 'DESC';
            $floor = $rownum - $start;
        } else {
            $timeorder = 'ASC';
            $floor = $start + 1;
        }
        $query = $this->db->query("SELECT * FROM answer WHERE qid=$qid ORDER BY time $timeorder LIMIT $start,$limit");
        while ($answer = $this->db->fetch_array($query)) {
            $answer['floor'] = $floor;
            $answer['time'] = tdate($answer['time']);
            $answer['ip'] = formatip($answer['ip']);
            $answer['author_avartar'] = get_avatar_dir($answer['authorid']);
            $answerlist[] = $answer;
            if (1 == $floor) {
                $floor++;
            } else {
                $floor--;
            }
        }
        return $answerlist;
    }

    function get_comment_options($groupcredit, $type = 1) {
        $maxcredit = ($groupcredit == 0 || $groupcredit > 10) ? 10 : $groupcredit;
        $optionlist = range(1, $maxcredit);
        $optionstr = '<select name="credit3">';
        foreach ($optionlist as $val) {
            if ($type)
                $optionstr .= '<option value="' . $val . '">+' . $val . '</option>';
            else
                $optionstr .= '<option value="-' . $val . '">-' . $val . '</option>';
        }
        $optionstr .= '</select>';
        return $optionstr;
    }

    // 根据uid获取答案的列表，用于在用户中心，我的回答显示
    function list_by_uid($uid, $status, $start=0, $limit=5) {
        $answerlist = array();
        $sql = "SELECT * FROM `answer` WHERE `authorid`=$uid ORDER BY `time` DESC LIMIT $start,$limit";
        $query = $this->db->query($sql);
        while ($answer = $this->db->fetch_array($query)) {
            $answer['time'] = tdate($answer['time']);
            $answerlist[] = $answer;
        }
        return $answerlist;
    }

    // 修改回答，同时重置回答的状态 
    function update_content($aid, $content) {
        $this->db->query("UPDATE `answer` set content='$content' WHERE `id`=$aid");
    }

    // 时间段内问题数目
    function rownum_by_time($uid, $hours = 1) {
        $starttime = strtotime(date("Y-m-d H:00:00", $this->base->time));
        $endtime = $starttime + $hours * 3600;
        return $this->db->fetch_total('answer', " `time`>$starttime AND `time`<$endtime AND authorid=$uid");
    }

    function remove($aids) {
        //更新问题回答数
        $query = $this->db->query("SELECT qid,count(*) as answers FROM answer WHERE `id` IN ($aids) GROUP BY `qid`");
        while (list($qid, $answers) = $this->db->fetch_row($query)) {
            $this->db->query("UPDATE question SET answers=answers-$answers WHERE `qid`=$qid");
        }

        //更新回答人回答数
        $query = $this->db->query("SELECT authorid,count(*) as answers FROM answer WHERE `id` IN ($aids) GROUP BY `authorid`");
        while (list($authorid, $answers) = $this->db->fetch_row($query)) {
            $this->db->query("UPDATE user SET answers=answers-$answers WHERE `uid`=$authorid");
        }

        //删除回答
        $this->db->query("DELETE FROM `answer_comment ` WHERE `aid` IN ($aids)");
        $this->db->query("DELETE FROM `answer_support ` WHERE `aid` IN ($aids)");
        $this->db->query("DELETE FROM `answer` WHERE `id` IN ($aids)");
    }

    function remove_by_qid($aid, $qid) {
        $this->db->query("DELETE FROM `answer` WHERE `id`=$aid");
        $this->db->query("UPDATE `question` SET answers=answers-1 WHERE `qid`=$qid");
    }

    function update_time_content($aid, $time, $content) {
        $this->db->query("UPDATE `answer` SET `content`='$content',`time`=$time WHERE `id`=$aid");
    }

    function get_support_by_uid_aid($uid, $aid) {
        return $this->db->fetch_total("answer_support", " uid='$uid' AND aid=$aid ");
    }

    function add_support($uid, $aid, $authorid) {
        $this->db->query("REPLACE INTO answer_support(uid,aid,time) VALUES ('$uid',$aid,{$this->base->time})");
        $this->db->query("UPDATE `answer` SET `supports`=supports+1 WHERE `id`=$aid");
    }
}

?>
