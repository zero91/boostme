<?php

!defined('IN_SITE') && exit('Access Denied');

class problemmodel {
    var $db;
    var $base;
    var $search;
    var $index;

    var $prob_status_table;
    var $prob_order_table;

    var $statustable = array(
        'all' => ' AND status<>0 ',
        '0' => ' AND STATUS=0',
        '1' => ' AND STATUS=1',
        '2' => ' AND STATUS=2',
        '4' => ' AND STATUS=4',
        '8' => ' AND STATUS=8'
    );

    var $ordertable = array(
        'all' => ' AND status<>0 ORDER BY time DESC',
        '0' => ' AND STATUS=0 ORDER BY time DESC',
        '1' => ' AND STATUS=1 ORDER BY time DESC, price DESC',
        '2' => ' AND STATUS=2 ORDER BY time DESC',
        '4' => ' AND STATUS=4 ORDER BY time DESC',
        '8' => ' AND STATUS=8 ORDER BY time DESC'
    );

    function problemmodel(&$base) {
        $this->prob_status_table = array(
            'all' => ' AND status<>' .  PB_STATUS_INVALID,
            PB_STATUS_UNAUDIT => ' AND status=' . PB_STATUS_UNAUDIT,
            PB_STATUS_UNSOLVED => ' AND status=' . PB_STATUS_UNSOLVED,
            PB_STATUS_SOLVED => ' AND status=' . PB_STATUS_SOLVED,
            PB_STATUS_CLOSED => ' AND status=' . PB_STATUS_CLOSED,
            PB_STATUS_AUDIT => ' AND status=' .PB_STATUS_AUDIT); 

        $this->prob_order_table = array(
            'all' => ' AND status<>' .  PB_STATUS_INVALID . ' ORDER BY time DESC',
            PB_STATUS_UNAUDIT => ' AND status=' . PB_STATUS_UNAUDIT . ' ORDER BY time DESC',
            PB_STATUS_UNSOLVED => ' AND status=' . PB_STATUS_UNSOLVED . ' ORDER BY time DESC, price DESC',
            PB_STATUS_SOLVED => ' AND status=' . PB_STATUS_SOLVED . ' ORDER BY time DESC',
            PB_STATUS_CLOSED => ' AND status=' . PB_STATUS_CLOSED . ' ORDER BY time DESC',
            PB_STATUS_AUDIT => ' AND status=' .PB_STATUS_AUDIT . ' ORDER BY time DESC'
        );

        $this->base = $base;
        $this->db = $base->db;
        if ($this->base->setting['xunsearch_open']) {
            require_once $this->base->setting['xunsearch_sdk_file'];
            $xs = new XS('problem');
            $this->search = $xs->search;
            $this->index = $xs->index;
        }
    }

    // 获取问题信息
    function get($pid) {
        $problem = $this->db->fetch_first("SELECT * FROM problem WHERE pid='$pid'");
        if ($problem) {
            $problem['format_time'] = tdate($problem['time']);
            $problem['ip'] = formatip($problem['ip']);
            $problem['author_avartar'] = get_avatar_dir($problem['authorid']);
        }
        return $problem;
    }

    function get_by_title($title) {
        return $this->db->fetch_first("SELECT * FROM problem WHERE `title`='$title'");
    }

    function get_list($start=0, $limit='') {
        $problemlist = array();

        $sql = "SELECT * FROM `problem` WHERE 1=1 ";
        !empty($limit) && $sql.=" LIMIT $start,$limit";
        $sql .= " ORDER BY `time` DESC";

        $query = $this->db->query($sql);
        while ($problem = $this->db->fetch_array($query)) {
            $problem['format_time'] = tdate($problem['time']);
            $problem['url'] = url('problem/view/' . $problem['pid'], $problem['url']);
            $problemlist[] = $problem;
        }
        return $problemlist;
    }

    function get_all_prob_num() {
        return $this->db->fetch_total('problem');
    }

    // 前台问题搜索
    function list_by_condition($condition, $start = 0, $limit = 10) {
        $problemlist = array();
        $query = $this->db->query("SELECT * FROM `problem` WHERE $condition order by time desc limit $start, $limit");
        while ($problem = $this->db->fetch_array($query)) {
            $problem['format_time'] = tdate($problem['time']);
            $problem['url'] = url('problem/view/' . $problem['pid'], $problem['url']);
            $problemlist[] = $problem;
        }
        return $problemlist;
    }

    // 插入问题到problem表
    function add($title, $description, $price, $status=PB_STATUS_UNAUDIT) {
        $overdue_days = intval($this->base->setting['overdue_days']);
        $creattime = $this->base->time;
        $endtime = $this->base->time + $overdue_days * 86400;
        $uid = $this->base->user['uid'];
        $username = $uid ? $this->base->user['username'] : $this->base->user['ip'];
        (!strip_tags($description, '<img>')) && $description = '';

        $this->db->query("INSERT INTO problem SET authorid='$uid',author='$username',title='$title',description='$description',price='$price',time='$creattime',endtime='$endtime',status='$status',ip='{$this->base->ip}'");
        $pid = $this->db->insert_id();
        return $pid;
    }

    function update_solver_score($pid, $solverscore, $solverdesc) {
        $this->db->query("UPDATE `problem` SET `solverscore`='$solverscore',`solverdesc`='$solverdesc' WHERE `pid`=$pid");
    }

    // 获得热门问题，目前定义热门问题为请求帮助量大、浏览量大的问题
    function get_hots($start, $limit) {
        $problemlist = array();
        $timestart = $this->base->time - 7 * 24 * 3600;
        $timeend = $this->base->time;
        $query = $this->db->query("SELECT * FROM problem WHERE `time`>$timestart AND `time`<$timeend ORDER BY demands DESC,views DESC,`time` DESC LIMIT $start,$limit");
        while ($problem = $this->db->fetch_array($query)) {
            $problem['format_time'] = tdate($problem['time']);
            $problemlist[] = $problem;
        }
        return $problemlist;
    }

    // 后台问题搜索
    function list_by_search($title = '', $author = '', $datestart = '', $dateend = '', $status = '', $start = 0, $limit = 10) {
        $sql = "SELECT * FROM `problem` WHERE 1=1 ";
        $title && ($sql .= " AND `title` like '$title%' ");
        $author && ($sql .= " AND `author`='$author'");
        $datestart && ($sql .= " AND `time` >= " . strtotime($datestart));
        $dateend && ($sql .=" AND `time` <= " . strtotime($dateend));
        $sql .= $this->prob_status_table[$status];
        $sql .= " ORDER BY `time` DESC LIMIT $start,$limit";
        $problemlist = array();
        $query = $this->db->query($sql);
        while ($problem = $this->db->fetch_array($query)) {
            $problem['format_time'] = tdate($problem['time']);
            $problem['url'] = url('problem/view/' . $problem['pid'], $problem['url']);
            $problemlist[] = $problem;
        }
        return $problemlist;
    }

    //通过标签获取同类问题
    function list_by_tag($name, $status=PB_STATUS_UNSOLVED, $start=0, $limit=20) {
        $problemlist = array();
        $query = $this->db->query("SELECT * FROM `problem` AS p, problem_tag AS t WHERE t.name='$name' AND p.pid=t.pid AND p.status IN ($status) ORDER BY p.demands DESC,p.time DESC LIMIT $start,$limit");
        while ($problem = $this->db->fetch_array($query)) {
            $problem['format_time'] = tdate($problem['time']);
            $problem['description'] = strip_tags($problem['description']);
            $problemlist[] = $problem;
        }
        return $problemlist;
    }

    function rownum_by_tag($name, $status=PB_STATUS_UNSOLVED) {
        $query = $this->db->query("SELECT * FROM `problem` AS p, problem_tag AS t WHERE t.name='$name' AND p.pid=t.pid AND p.status IN ($status)");
        return $this->db->num_rows($query);
    }

    // 求助列表，根据指定的status来查询
    function list_by_status($status=PB_STATUS_UNSOLVED, $start = 0, $limit='') {
        $problemlist = array();

        $status= ($status && is_array($status)) ? implode(",", $status) : $status;
        $sql = "SELECT * FROM problem WHERE status IN ($status) ORDER BY time DESC";

        !empty($limit) && $sql.=" LIMIT $start,$limit";
        $query = $this->db->query($sql);
        while ($problem = $this->db->fetch_array($query)) {
            $problem['format_time'] = tdate($problem['time']);
            $problemlist[] = $problem;
        }
        return $problemlist;
    }

    // 我的所有求助，用户中心
    function list_by_uid($uid, $status, $start=0, $limit=10) {
        $problemlist = array();
        $sql = 'SELECT * FROM problem WHERE `authorid` = ' . $uid;
        $sql .= $this->prob_status_table[$status] . " ORDER BY `time` DESC LIMIT $start,$limit";
        $query = $this->db->query($sql);
        while ($problem = $this->db->fetch_array($query)) {
            if (intval($problem['endtime'])) {
                $problem['format_endtime'] = tdate($problem['endtime']);
            }
            $problem['format_time'] = tdate($problem['time']);
            $problem['url'] = url('problem/view/' . $problem['pid'], $problem['url']);
            $problemlist[] = $problem;
        }
        return $problemlist;
    }

    // 我解决的所有求助，用户中心
    function list_by_solverid($uid, $start=0, $limit=10) {
        $problemlist = array();
        $sql = 'SELECT * FROM problem WHERE `solverid` = ' . $uid;
        $sql .= " ORDER BY `time` DESC LIMIT $start,$limit";
        $query = $this->db->query($sql);
        while ($problem = $this->db->fetch_array($query)) {
            if (intval($problem['endtime'])) {
                $problem['format_endtime'] = tdate($problem['endtime']);
            }
            $problem['format_time'] = tdate($problem['time']);
            $problem['url'] = url('problem/view/' . $problem['pid'], $problem['url']);
            $problemlist[] = $problem;
        }
        return $problemlist;
    }

    // 更新求助
    function update($pid, $title, $description, $price, $status) {
        $overdue_days = intval($this->base->setting['overdue_days']);
        $asktime = $this->base->time;
        $endtime = $asktime + $overdue_days * 86400;
        $this->db->query("UPDATE `problem` SET title='$title',description='$description',price='$price',`status`=$status,`time`= $asktime,endtime='$endtime' WHERE `pid` = $pid");
        if ($this->base->setting['xunsearch_open']) { // 更新索引
            $problem = array();
            $problem['pid'] = $pid;
            $problem['status'] = $status;
            $problem['title'] = $title;
            $problem['description'] = $description;
            $doc = new XSDocument;
            $doc->setFields($problem);
            $this->index->update($doc);
        }
    }

    // 更新求助状态
    function update_status($pid, $status=PB_STATUS_SOLVED) {
        $this->db->query("UPDATE `problem` set `status`=$status WHERE `pid`=$pid");
    }

    // 更新问题解决者
    function update_solver($pid, $solverid, $solver) {
        $this->db->query("UPDATE `problem` set `solverid`=$solverid,`solver`='$solver',`status`=" . PB_STATUS_SOLVED ." WHERE `pid`=$pid");
    }

    //添加查看次数
    function add_views($pid) {
        $this->db->query("UPDATE `problem` SET views=views+1 WHERE `pid`=$pid");
    }

    //求助审核
    function change_to_verify($pids) {
        $this->db->query("UPDATE `problem` set status=" . PB_STATUS_UNSOLVED . " WHERE status=" . PB_STATUS_UNAUDIT . " AND `pid` in ($pids)");
    }

    //编辑求助标题
    function renametitle($pid, $title) {
        $this->db->query("UPDATE `problem` SET `title`='$title' WHERE `pid`=$pid");
        if ($this->base->setting['xunsearch_open']) {
            $problem = array();
            $problem['pid'] = $pid;
            $problem['title'] = $title;
            $doc = new XSDocument;
            $doc->setFields($problem);
            $this->index->update($doc);
        }
    }

    //编辑求助内容
    function update_content($pid, $title, $content) {
        $this->db->query("UPDATE `problem` SET `title`='$title',`description`='$content' WHERE `pid`=$pid");
        if ($this->base->setting['xunsearch_open']) {
            $problem = array();
            $problem['pid'] = $pid;
            $problem['title'] = $title;
            $problem['description'] = $content;
            $doc = new XSDocument;
            $doc->setFields($problem);
            $this->index->update($doc);
        }
    }

    //根据标题搜索问题的结果数
    function search_title_num($title, $status=PB_STATUS_UNSOLVED) {
        $problemnum = 0;
        if ($this->base->setting['xunsearch_open']) {
            $problemnum = $this->search->getLastCount();
        } else {
            $condition = " STATUS IN ($status) AND title LIKE '%$title%' ";
            $problemnum = $this->db->fetch_total('problem', $condition);
        }
        return $problemnum;
    }

    //根据标题搜索问题
    function search_title($title, $status=PB_STATUS_UNSOLVED, $start=0, $limit=20) {
        $problemlist = array();
        if ($this->base->setting['xunsearch_open']) {
            $statusarr = explode(",", $status);
            $size = count($statusarr);
            $to = $statusarr[$size - 1];
            $from = $statusarr[0];
            $result = $this->search->setQuery($title)->addRange('status', $from, $to)->setLimit($limit, $start)->search();
            foreach ($result as $doc) {
                $problem = array();
                $problem['pid'] = $doc->id;
                $problem['author'] = $doc->author;
                $problem['authorid'] = $doc->authorid;
                $problem['status'] = $doc->status;
                $problem['format_time'] = tdate($doc->time);
                $problem['title'] = $this->search->highlight($doc->title);
                $problem['description'] = $this->search->highlight(strip_tags($doc->description));
                $problemlist[] = $problem;
            }
        } else {
            $sql = "SELECT * FROM problem WHERE STATUS IN ($status) AND title LIKE '%$title%' LIMIT $start,$limit";
            $query = $this->db->query($sql);
            while ($problem = $this->db->fetch_array($query)) {
                $problem['format_time'] = tdate($problem['time']);
                $problem['description'] = strip_tags($problem['description']);
                $problemlist[] = $problem;
            }
        }
        return $problemlist;
    }

    // 防采集，功能还有问题，待后续完善
    function stopcopy() {
        $ip = $this->base->ip;
        $bengintime = $this->base->time - 60;
        $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        $useragent = strtolower($useragent);
        $allowagent = explode("\n", $this->base->setting['stopcopy_allowagent']);
        $allow = false;
        foreach ($allowagent as $agent) {
            if (false !== strpos($useragent, $agent)) {
                $allow = true;
                break;
            }
        }
        !$allow && exit('Boostme禁止不明浏览行为！');
        $stopagent = explode("\n", $this->base->setting['stopcopy_stopagent']);
        foreach ($stopagent as $agent) {
            if (false !== strpos($useragent, $agent)) {
                exit('Boostme禁止不明浏览行为！');
            }
        }
        $visits = $this->db->fetch_total('visit', " time>$bengintime AND ip='$ip' ");
        if ($visits > $this->base->setting['stopcopy_maxnum']) {
            $userip = explode(".", $ip);
            $expiration = 3600 * 24;
            $this->db->query("INSERT INTO `banned` (`ip1`,`ip2`,`ip3`,`ip4`,`admin`,`time`,`expiration`) VALUES ('{$userip[0]}', '{$userip[1]}', '{$userip[2]}', '{$userip[3]}', 'SYSTEM', '{$this->base->time}', '{$expiration}')");
            exit('你采集的速度太快了吧 : ) ');
        } else {
            $this->db->query("INSERT INTO visit (`ip`,`time`) values ('$ip','{$this->base->time}')"); //加入数据库记录visit表中
        }
    }

    function add_demand($pid) {
        $this->db->query("UPDATE problem SET `demands`=`demands`+1 WHERE `pid`=$pid");
    }

    function update_demand($pid, $delta) {
        $this->db->query("UPDATE problem SET `demands`=`demands`+($delta) WHERE `pid`=$pid");
    } 

    // 获得相关结果关键词
    function get_related_words() {
        $words = array();
        if ($this->base->setting['xunsearch_open'])
            $words = $this->search->getRelatedQuery();
        return $words;
    }

    /**
     * 获得热门搜索词
     * @param type $size
     * @return type
     */
    function get_hot_words($size = 8) {
        $words = array();
        if ($this->base->setting['xunsearch_open'])
            $words = array_keys($this->search->getHotQuery($size, "currnum"));
        return $words;
    }

    function get_corrected_word() {
        $words = array();
        if ($this->base->setting['xunsearch_open'])
            $words = $this->search->getCorrectedQuery();
        return $words;
    }

    function makeindex() {
        if ($this->base->setting['xunsearch_open']) {
            $this->index->clean();
            $query = $this->db->query("SELECT * FROM problem ");
            while ($problem = $this->db->fetch_array($query)) {
                $data = array();
                $data['id'] = $problem['pid'];
                $data['author'] = $problem['author'];
                $data['authorid'] = $problem['authorid'];
                $data['status'] = $problem['status'];
                $data['time'] = $problem['time'];
                $data['title'] = $problem['title'];
                $data['description'] = $problem['description'];
                $doc = new XSDocument;
                $doc->setFields($data);
                $this->index->add($doc);
            }
        }
    }
}

?>
