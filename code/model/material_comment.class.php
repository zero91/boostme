<?php

!defined('IN_SITE') && exit('Access Denied');
/*
 * DROP TABLE IF EXISTS material_comment;
 * CREATE TABLE material_comment (
 * `id` int(10) NOT NULL AUTO_INCREMENT,
 * `mid` int(10) NOT NULL,
 * `authorid` int(10) unsigned NOT NULL,
 * `author` char(18) NOT NULL,
 * `content` text NOT NULL,
 * `time` int(10) NOT NULL DEFAULT '0',
 * PRIMARY KEY (`id`),
 * KEY `mid`(`mid`),
 * KEY `authorid`(`authorid`)
 * ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
 * */

class material_commentmodel {
    var $db;
    var $base;

    function material_commentmodel(&$base) {
        $this->base = $base;
        $this->db = $base->db;
    }

    function get_by_mid($mid, $start=0, $limit=10) {
        $commentlist = array();
        $query = $this->db->query("SELECT * FROM `material_comment` WHERE mid=$mid ORDER BY `time` ASC limit $start,$limit");
        while ($comment = $this->db->fetch_array($query)) {
            $comment['author_avatar'] = get_avatar_dir($comment['authorid']);
            $comment['format_time'] = tdate($comment['time']);
            $commentlist[] = $comment;
        }
        return $commentlist;
    }

    function add($mid, $conmment, $authorid, $author) {
        $this->db->query("INSERT INTO `material_comment`(`mid`,`authorid`,`author`,`content`,`time`) values ('$mid','$authorid','$author','$conmment','{$this->base->time}')");
        $this->db->query("UPDATE material SET comment_num=comment_num+1 WHERE `id`=$mid");
    }

    function remove($commentids, $mid) {
        $commentcount = 1;
        if (is_array($commentids)) {
            $commentcount = count($commentids);
            $commentids = implode(",", $commentids);
        }
        $this->db->query("DELETE FROM material_comment WHERE `id` IN ($commentids)");
        $this->db->query("UPDATE material SET comment_num=comment_num-$commentcount WHERE `mid`=$mid");
    }
}

?>
