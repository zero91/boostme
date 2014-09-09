<?php

!defined('IN_SITE') && exit('Access Denied');

class settingmodel {
    var $db;
    var $base;

    function settingmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function update($setting) {
        foreach ($setting as $key => $value) {
            $this->db->query("REPLACE INTO setting (k,v) VALUES ('$key','$value')");
        }
        $this->base->cache->remove('setting');
    }

    // 用户问题回答数目校正
    //function regulate_user() {
    //    return;
    //    $query = $this->db->query("SELECT * FROM ".DB_TABLEPRE."user");
    //    while($user = $this->db->fetch_array($query)) {
    //        $questions=$this->db->fetch_total('question','authorid='.$user['uid']);
    //        $answers=$this->db->fetch_total('answer','authorid='.$user['uid']);
    //        $this->db->query("UPDATE ".DB_TABLEPRE."user SET questions=$questions,answers=$answers where uid=".$user['uid']);
    //    }
    //}
    
    //function get_hot_words($hot_words) {
    //    $lines = explode("\n", $hot_words);
    //    $wordslist = array();
    //    foreach ($lines as $line){
    //        $words = explode("，", $line);
    //        if(is_array($words)){
    //            $word['w']=$words[0];
    //            $word['qid']=intval($words[1]);
    //            $wordslist[] = $word;
    //        }
    //    }
    //    return serialize($wordslist);
    //}
}

?>
