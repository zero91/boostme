<?php

class db {
    public function __construct($dbhost, $dbuser, $dbpw, $dbname = '', $dbcharset='utf8', $pconnect=0) {
        if ($pconnect) {
            if (!$this->mlink = @mysql_pconnect($dbhost, $dbuser, $dbpw)) {
                $this->halt('Can not connect to MySQL');
            }
        } else {
            if (!$this->mlink = @mysql_connect($dbhost, $dbuser, $dbpw)) {
                $this->halt('Can not connect to MySQL');
            }
        }

        if ($this->version() > '4.1') {
            if (strtolower($dbcharset) == 'utf-8') {
                $dbcharset = 'utf8';
            }
            if ($dbcharset) {
                mysql_query("SET character_set_connection=$dbcharset, character_set_results=$dbcharset, character_set_client=binary", $this->mlink);
            }
            if ($this->version() > '5.0.1') {
                mysql_query("SET sql_mode=''", $this->mlink);
            }
        }
        if ($dbname) {
            $this->select_db($dbname, $this->mlink);
        }
    }

    // @brief: 选择数据库名
    public function select_db($dbname) {
        return mysql_select_db($dbname, $this->mlink);
    }

    // @brief: 从结果集中取得一行作为关联数组，或数字数组，或二者兼有
    // @param:
    // result_type = MYSQL_ASSOC - 关联数组
    //               MYSQL_NUM - 数字数组
    //               MYSQL_BOTH - 默认。同时产生关联和数字数组
    public function fetch_array($query, $result_type = MYSQL_ASSOC) {
        return (is_resource($query)) ? mysql_fetch_array($query, $result_type) : false;
    }

    // @brief: 取sql查询语句结果集的第一行数据，以关联数组、数字数组，或二者兼有
    public function fetch_first($sql) {
        $query = $this->query($sql);
        return $this->fetch_array($query);
    }

    // @brief: 取结果集第row行第field列的结果
    public function result($query, $row, $field=0) {
        $query = @mysql_result($query, $row, $field);
        return $query;
    }

    // @brief: 取结果集第0行第0列的结果
    public function result_first($sql) {
        $query = $this->query($sql);
        return $this->result($query, 0);
    } 

    // @brief 返回结果集的下一行结果，数组为数字下标
    public function fetch_row($query) {
        $query = mysql_fetch_row($query);
        return $query;
    }

    // @brief: 更新table满足where条件的的列值为value
    public function update_field($table, $field, $value, $where) {
        return $this->query("UPDATE $table SET $field='$value' WHERE $where");
    }

    // @brief: 取table中满足条件where的数据的条数
    // @return: 返回table中满足条件where的数据的条数
    public function fetch_total($table, $where=' 1=1 ') {
        return $this->result_first("SELECT COUNT(*) num FROM $table WHERE $where");
    }

    public function query($sql, $type = '') {
        global $debug, $querynum;
        $func = $type == 'UNBUFFERED' && @function_exists('mysql_unbuffered_query') ? 'mysql_unbuffered_query' : 'mysql_query';
        if(!($query = $func($sql, $this->mlink)) && $type != 'SILENT') {
            $this->halt(mysql_error(), $debug);
        }
        $querynum++;
        return $query;
    }

    // @return 返回数据库之前查询语句影响的行数
    public function affected_rows() {
        return mysql_affected_rows($this->mlink);
    }

    public function error() {
        return (($this->mlink) ? mysql_error($this->mlink) : mysql_error());
    }

    public function errno() {
        return intval(($this->mlink) ? mysql_errno($this->mlink) : mysql_errno());
    }

    // @return 返回结果集的总行数
    public function num_rows($query) {
        $query = mysql_num_rows($query);
        return $query;
    }

    // @return 返回结果集的总列数
    public function num_fields($query) {
        return mysql_num_fields($query);
    }

    public function free_result($query) {
        return mysql_free_result($query);
    }

    // @return 返回上一步 INSERT 操作产生的 ID
    public function insert_id() {
        return ($id = mysql_insert_id($this->mlink)) >= 0 ? $id : $this->result($this->query('SELECT last_insert_id()'), 0);
    }

    // @brief 从结果集中取得列信息并作为对象返回
    public function fetch_fields($query) {
        return mysql_fetch_field($query);
    }

    // @brief 获取sql查询结果，若指定了key，则数组的键为key列对应的数值
    public function fetch_all($sql, $id = '') {
        $arr = array();
        $query = $this->query($sql);
        while ($data = $this->fetch_array($query)) {
            $id ? $arr[$data[$id]] = $data : $arr[] = $data;
        }
        return $arr;
    }

    public function close() {
        return mysql_close($this->mlink);
    }

    public function version() {
        return mysql_get_server_info($this->mlink);
    }

    private function halt($msg, $debug=true) {
        if ($debug) {
            echo "<html>\n";
            echo "<head>\n";
            echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\n";
            echo "<title>$msg</title>\n";
            echo "<br><font size=\"6\" color=\"red\"><b>$msg</b></font>";
            exit();
        }
    }

    private $mlink;
}

?>
