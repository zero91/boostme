<?php

!defined('IN_SITE') && exit('Access Denied');

class problemmodel {
    public function __construct(&$db) {
        $this->db = & $db;
    }

    // 获取求助信息
    public function get($pid) {
        return $this->db->fetch_first("SELECT * FROM problem WHERE `pid`='$pid'");
    }

    public function get_list($start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM `problem` LIMIT $start,$limit ORDER BY `time` DESC");
    }

    public function get_all_prob_num() {
        return $this->db->fetch_total('problem');
    }

    // 前台问题搜索
    public function list_by_condition($condition, $start=0, $limit=10) {
        return $this->db->fetch_all("SELECT * FROM `problem` WHERE $condition ORDER BY time DESC limit $start,$limit");
    }

    // 插入问题到problem表
    public function add($uid, $username, $title, $description, $price, $status=PB_STATUS_UNAUDIT) {
        global $setting;
        $overdue_days = intval($setting['overdue_days']);
        $creattime = time();
        $endtime = time() + $overdue_days * 86400;
        (!strip_tags($description, '<img>')) && $description = '';

        $this->db->query("INSERT INTO problem SET authorid='$uid',author='$username',title='$title',description='$description',price='$price',time='$creattime',endtime='$endtime',status='$status',ip='" . getip() . "'");
        $pid = $this->db->insert_id();

        $this->db->query("UPDATE user SET `problems`=`problems`+1 WHERE `uid`=$uid");
        return $pid;
    }

    public function update_solver_score($pid, $solverscore, $solverdesc) {
        $this->db->query("UPDATE `problem` SET `solverscore`='$solverscore',`solverdesc`='$solverdesc' WHERE `pid`='$pid'");
        return $this->db->affected_rows();
    }

    // 获得热门求助，目前定义热门求助为请求帮助量大、浏览量大的问题
    public function get_hots($start, $limit) {
        $timestart = time() - 7 * 24 * 3600;
        $timeend = time();
        return $this->db->fetch_all("SELECT * FROM problem WHERE `time`>$timestart AND `time`<$timeend ORDER BY demands DESC,views DESC,`time` DESC LIMIT $start,$limit");
    }

    // 后台问题搜索
    public function list_by_search($title='', $author='', $datestart='', $dateend='', $status='all', $start=0, $limit=10) {
        $sql = "SELECT * FROM `problem` WHERE 1=1 ";
        !empty($title) && ($sql .= " AND `title` like '$title%' ");
        !empty($author) && ($sql .= " AND `author`='$author'");
        !empty($datestart) && ($sql .= " AND `time` >= " . strtotime($datestart));
        !empty($dateend) && ($sql .=" AND `time` <= " . strtotime($dateend));

        if ($status == 'all') {
            $sql .= ' AND status<>' . PB_STATUS_INVALID . ' ';
        } else {
            $sql .= " AND status=$status ";
        }
        $sql .= " ORDER BY `time` DESC LIMIT $start,$limit";
        return $this->db->fetch_all($sql);
    }

    // 通过标签获取同类问题
    public function list_by_tag($name, $status='all', $start=0, $limit=10) {
        $sql = "SELECT * FROM `problem` AS p, problem_tag AS t WHERE t.name='$name' AND p.pid=t.pid ";
        if ($status == 'all') {
            $sql .= " AND p.status<>" . PB_STATUS_INVALID . " ";
        } else {
            $sql .= " AND p.status IN ($status) ";
        }
        $sql .= " ORDER BY p.demands DESC,p.time DESC LIMIT $start,$limit";
        return $this->db->fetch_all($sql);
    }

    public function rownum_by_tag($name, $status='all') {
        $sql = "SELECT COUNT(*) FROM `problem` AS p, problem_tag AS t WHERE t.name='$name' AND p.pid=t.pid ";
        if ($status == 'all') {
            $sql .= " AND p.status<>" . PB_STATUS_INVALID . " ";
        } else {
            $sql .= " AND p.status IN ($status)";
        }
        return $this->db->result_first($sql);
    }

    // 求助列表，根据指定的status来查询
    public function list_by_status($status='all', $start=0, $limit=10) {
        if ($status == 'all') {
            $sql = "SELECT * FROM problem WHERE status<>" . PB_STATUS_INVALID . " ORDER BY time DESC LIMIT $start,$limit";
        } else {
            $sql = "SELECT * FROM problem WHERE status IN ($status) ORDER BY time DESC LIMIT $start,$limit";
        }
        return $this->db->fetch_all($sql);
    }

    // 我的所有求助，用户中心
    public function list_by_uid($uid, $status='all', $start=0, $limit=10) {
        $sql = "SELECT * FROM problem WHERE `authorid`='$uid' ";

        if ($status == 'all') {
            $sql .= " AND status<>" . PB_STATUS_INVALID . " ";
        } else {
            $sql .= " AND status IN ($status) ";
        }
        $sql .= " ORDER BY `time` DESC LIMIT $start,$limit";
        return $this->db->fetch_all($sql);
    }

    // 我解决的所有求助，用户中心
    public function list_by_solverid($uid, $status='all', $start=0, $limit=10) {
        $sql = "SELECT * FROM problem WHERE `solverid`='$uid'";

        if ($status == 'all') {
            $sql .= " AND status<>" . PB_STATUS_INVALID . " ";
        } else {
            $sql .= " AND status IN ($status) ";
        }
        $sql .= " ORDER BY `time` DESC LIMIT $start,$limit";
        return $this->db->fetch_all($sql);
    }

    // 更新求助
    public function update($pid, $title, $description, $cid, $price, $status) {
        global $settting;
        $overdue_days = intval($setting['overdue_days']);
        $asktime = time();
        $endtime = $asktime + $overdue_days * 86400;
        $this->db->query("UPDATE `problem` SET title='$title',description='$description',cid='$cid',price='$price',`status`=$status,`time`= $asktime,endtime='$endtime' WHERE `pid`=$pid");
        return $this->db->affected_rows();
    }

    // 更新求助状态
    public function update_status($pid, $status=PB_STATUS_SOLVED) {
        $this->db->query("UPDATE `problem` set `status`=$status WHERE `pid`=$pid");
        return $this->db->affected_rows();
    }

    // 更新问题解决者
    public function update_solver($pid, $solverid, $solver) {
        $this->db->query("UPDATE `problem` set `solverid`=$solverid,`solver`='$solver',`status`=" . PB_STATUS_SOLVED ." WHERE `pid`='$pid'");
        return $this->db->affected_rows();
    }

    // 添加查看次数
    public function add_views($pid) {
        $this->db->query("UPDATE `problem` SET views=views+1 WHERE `pid`=$pid");
        return $this->db->affected_rows();
    }

    // 求助审核
    public function change_to_verify($pids) {
        $this->db->query("UPDATE `problem` set status=" . PB_STATUS_UNSOLVED . " WHERE status=" . PB_STATUS_UNAUDIT . " AND `pid` in ($pids)");
        return $this->db->affected_rows();
    }

    // 编辑求助标题
    public function renametitle($pid, $title) {
        $this->db->query("UPDATE `problem` SET `title`='$title' WHERE `pid`=$pid");
        return $this->db->affected_rows();
    }

    // 编辑求助内容
    public function update_content($pid, $title, $content) {
        $this->db->query("UPDATE `problem` SET `title`='$title',`description`='$content' WHERE `pid`=$pid");
        return $this->db->affected_rows();
    }

    // 根据标题搜索问题的结果数
    public function search_title_num($title, $status='all') {
        if ($status == 'all') {
            $condition = " STATUS<>" . PB_STATUS_INVALID . " AND title LIKE '%$title%' ";
        } else {
            $condition = " STATUS IN ($status) AND title LIKE '%$title%' ";
        }
        return $this->db->fetch_total('problem', $condition);
    }

    // 根据标题搜索问题
    public function search_title($title, $status='all', $start=0, $limit=10) {
        if ($status == 'all') {
            $sql = "SELECT * FROM problem WHERE status<>" . PB_STATUS_INVALID . " AND title LIKE '%$title%' LIMIT $start,$limit";
        } else {
            $sql = "SELECT * FROM problem WHERE status IN ($status) AND title LIKE '%$title%' LIMIT $start,$limit";
        }

        return $this->db->fetch_all($sql);
    }

    public function add_demand($pid) {
        $this->db->query("UPDATE problem SET `demands`=`demands`+1 WHERE `pid`=$pid");
        return $this->db->affected_rows();
    }

    public function update_demand($pid, $delta) {
        $this->db->query("UPDATE problem SET `demands`=`demands`+($delta) WHERE `pid`=$pid");
        return $this->db->affected_rows();
    } 

    // 获得相关结果关键词
    public function get_related_words() {
        $words = array();
        return $words;
    }

    public function get_hot_words($size = 8) {
        $words = array();
        return $words;
    }

    public function get_corrected_word() {
        $words = array();
        return $words;
    }

    private $db;
}

?>
