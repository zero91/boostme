/* DROP TABLE IF EXISTS user; */
CREATE TABLE user (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` char(18) NOT NULL,
  `password` char(32) NOT NULL,
  `email` varchar(40) NOT NULL,
  `email_verify` tinyint(1) unsigned NOT NULL DEFAULT '0', /* 邮箱验证 */
  `isadmin` tinyint(1) unsigned NOT NULL DEFAULT '0', /* 是否是管理员 */
  `regtime` int(10) NOT NULL DEFAULT '0',
  `regip` char(15) DEFAULT NULL,
  `lastlogin` int(10) unsigned NOT NULL DEFAULT '0',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `bday` date DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `qq` varchar(18) DEFAULT NULL,
  `wechat` varchar(30) DEFAULT NULL,
  `authstr` varchar(100) DEFAULT NULL,
  `signature` mediumtext,
  `problems` int(10) unsigned NOT NULL DEFAULT '0', /* 提交的需求数量 */
  `questions` int(10) unsigned NOT NULL DEFAULT '0', /* 发帖数量 */
  `answers` int(10) unsigned NOT NULL DEFAULT '0', /* 回帖数量 */
  `solved` int(10) unsigned NOT NULL DEFAULT '0', /* 解决的需求数量 */
  `failed` int(10) unsigned NOT NULL DEFAULT '0', /* 未抢到的需求数量 */
  `can_teach` tinyint(3) unsigned NOT NULL DEFAULT '0', /* 是否可以教别人，获取抢单的资格 */
  `teach_level` smallint(5) NOT NULL DEFAULT '0', /* 授课水平 */
  `credit` smallint(5) NOT NULL DEFAULT '0', /* 用户信用等级 */
  `userpoint` int(10) unsigned NOT NULL DEFAULT '0', /* 用户积分 */
  `paid` DOUBLE NOT NULL DEFAULT '0', /* 总共付费 */
  `earned` DOUBLE NOT NULL DEFAULT '0', /* 总共收入 */
  `balance` DOUBLE NOT NULL DEFAULT '0', /* 账户余额 */
  
  PRIMARY KEY (`uid`),
  KEY username(username),
  KEY email(email)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/* alter table `user` add column `balance` DOUBLE NOT NULL DEFAULT '0'; */

/* DROP TABLE IF EXISTS user_skill; */
CREATE TABLE user_skill (
  `uid` int(10) NOT NULL,
  `skill` varchar(20) NOT NULL,
  `time` int(10) NOT NULL DEFAULT '0',
  `verified` int(1) NOT NULL DEFAULT '0',

  PRIMARY KEY (`uid`,`skill`),
  KEY `uid`(`uid`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS user_ebank; */
CREATE TABLE user_ebank (
  `uid` int(10) NOT NULL,
  `ebank_type` varchar(20) NOT NULL, /* 账户类型：支付宝、财付通等 */
  `ebank_account` varchar(64) NOT NULL, /* 电子账户：支付宝账号、财付通账号等 */
  `time` int(10) NOT NULL DEFAULT '0', /* 绑定时间 */

  PRIMARY KEY (`uid`,`ebank_type`,`ebank_account`),
  KEY `uid`(`uid`),
  KEY `ebank_account`(`ebank_account`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS user_resume; */
CREATE TABLE user_resume (
  `uid` int(10) NOT NULL,
  `realname` char(18) DEFAULT NULL, /* 真实姓名 */
  `ID` varchar(18) DEFAULT NULL, /* 身份证号*/
  `ID_path` varchar(100) DEFAULT NULL, /* 身份证照片路径 */
  `experience` mediumtext,
  `resume_path` varchar(100) DEFAULT NULL,
  `studentID` varchar(100) DEFAULT NULL, /* 学生证照片路径 */
  `verified` tinyint(1) NOT NULL DEFAULT '0', /* 是否通过验证 */
  `apply_time` int(10) DEFAULT NULL, /* 申请时间 */
  `accepted_time` int(10) DEFAULT NULL, /* 通过审核的时间 */

  PRIMARY KEY (`uid`),
  UNIQUE KEY `ID`(`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS education; */
CREATE TABLE education (
  `eid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) NOT NULL,
  `edu_type` tinyint(1) DEFAULT NULL, /* 教育类型：小学、初中、高中、大学、硕士、博士 */
  `school` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `major` varchar(100) DEFAULT NULL,
  `start_time` date DEFAULT NULL,
  `end_time` date DEFAULT NULL,
  PRIMARY KEY (`eid`),
  KEY `uid`(`uid`),
  KEY `school`(`school`),
  KEY `department`(`department`),
  KEY `major`(`major`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8; 

/* DROP TABLE IF EXISTS service; */
CREATE TABLE service (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `username` char(18) NOT NULL,
  `picture` varchar(100) DEFAULT NULL, /* 照片 */
  `price` DOUBLE NOT NULL DEFAULT '0', /* 收费价格 */
  `profile` mediumtext, /* 针对此服务的简介 */
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1', /* 默认为未审核 */
  `avg_score` DOUBLE NOT NULL DEFAULT '0', /* 平均评价得分 */
  `time` int(10) NOT NULL DEFAULT '0',
  `service_num` mediumint(8) NOT NULL DEFAULT '0', /* 共服务人数 */

  PRIMARY KEY (`id`),
  KEY `uid`(`uid`),
  KEY `price`(`price`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8; 

/* DROP TABLE IF EXISTS service_category; */
CREATE TABLE service_category (
  `sid` int(10) unsigned NOT NULL,
  `cid` varchar(128) NOT NULL,

  PRIMARY KEY sid_cid(`sid`,`cid`),
  KEY `sid`(`sid`),
  KEY `cid`(`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8; 

/* DROP TABLE IF EXISTS service_comment; */
CREATE TABLE service_comment (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `authorid` int(10) unsigned NOT NULL,
  `author` char(18) NOT NULL,
  `sid` int(10) NOT NULL,
  `score` smallint(3) NOT NULL,
  `content` text NOT NULL,
  `time` int(10) NOT NULL DEFAULT '0',

  PRIMARY KEY (`id`),
  KEY `authorid`(`authorid`),
  KEY `sid`(`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS visit; */
CREATE TABLE visit (
  `ip` varchar(15) NOT NULL,
  `time` int(10) NOT NULL DEFAULT '0',
  KEY `ip` (`ip`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8; 

/* DROP TABLE IF EXISTS problem; */
CREATE TABLE problem (
  `pid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `authorid` int(10) unsigned NOT NULL DEFAULT '0',
  `author` char(18) NOT NULL DEFAULT '',
  `authorscore` tinyint(3) unsigned DEFAULT NULL,
  `authordesc` varchar(300) DEFAULT NULL,
  `solverid` int(10) unsigned DEFAULT NULL,
  `solver` char(18) DEFAULT NULL,
  `solverscore` tinyint(3) DEFAULT NULL,
  `solverdesc` varchar(300) DEFAULT NULL,
  `price` smallint(6) unsigned NOT NULL DEFAULT '0',
  `title` char(200) NOT NULL,
  `description` text NOT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `endtime` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1', /* 默认为未审核 */
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `demands` int(10) unsigned NOT NULL DEFAULT '0',

  PRIMARY KEY (`pid`),
  KEY authorid (authorid),
  KEY solverid (`solverid`),
  KEY time (`time`),
  KEY price (`price`),
  KEY cid(`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS problem_category; */
CREATE TABLE problem_category (
  `pid` int(10) unsigned NOT NULL,
  `cid` varchar(128) NOT NULL,

  PRIMARY KEY `pid_cid`(`pid`,`cid`),
  KEY `pid`(`pid`),
  KEY `cid`(`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*
ALTER TABLE problem ADD COLUMN cid varchar(32) DEFAULT NULL AFTER description;
ALTER TABLE problem ADD INDEX cid(`cid`);
*/

/* DROP TABLE IF EXISTS problem_tag; */
CREATE TABLE problem_tag (
  `pid` int(10) NOT NULL,
  `name` varchar(20) NOT NULL,
  `time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pid`,`name`),
  KEY `pid`(`pid`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS demand; */
CREATE TABLE demand (
  `did` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `username` char(18) NOT NULL,
  `pid` int(10) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `result` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `message` text DEFAULT NULL,

  PRIMARY KEY (`did`),
  UNIQUE KEY uid_pid (`uid`,`pid`),
  KEY uid (`uid`),
  KEY pid (`pid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS message; */
CREATE TABLE message (
  `mid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from` varchar(15) NOT NULL DEFAULT '',
  `fromuid` int(10) unsigned NOT NULL DEFAULT '0',
  `touid` int(10) unsigned NOT NULL DEFAULT '0',
  `new` tinyint(1) NOT NULL DEFAULT '1',
  `subject` varchar(75) NOT NULL DEFAULT '',
  `content` text NOT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`mid`),
  KEY `touid` (`touid`),
  KEY `fromuid` (`fromuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS banned; */
CREATE TABLE banned (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `ip1` char(3) NOT NULL,
  `ip2` char(3) NOT NULL,
  `ip3` char(3) NOT NULL,
  `ip4` char(3) NOT NULL,
  `admin` varchar(15) NOT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `expiration` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS session; */
CREATE TABLE session (
  `sid` char(16) NOT NULL DEFAULT '',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `code` char(4) NOT NULL DEFAULT '',
  `islogin` tinyint(1) NOT NULL DEFAULT '0',
  `ip` varchar(20) DEFAULT NULL COMMENT 'ip地址',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `sid` (`sid`),
  KEY `uid` (`uid`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS badword; */
CREATE TABLE badword (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `admin` varchar(15) NOT NULL DEFAULT '',
  `find` varchar(255) NOT NULL DEFAULT '',
  `replacement` varchar(255) NOT NULL DEFAULT '',
  `findpattern` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `find` (`find`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS crontab; */
CREATE TABLE crontab (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `available` tinyint(1) NOT NULL DEFAULT '0',
  `type` enum('user','system') NOT NULL DEFAULT 'user',
  `name` char(50) NOT NULL DEFAULT '',
  `method` varchar(50) NOT NULL DEFAULT '',
  `lastrun` int(10) unsigned NOT NULL DEFAULT '0',
  `nextrun` int(10) unsigned NOT NULL DEFAULT '0',
  `weekday` tinyint(1) NOT NULL DEFAULT '0',
  `day` tinyint(2) NOT NULL DEFAULT '0',
  `hour` tinyint(2) NOT NULL DEFAULT '0',
  `minute` char(36) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `nextrun` (`available`,`nextrun`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS attach; */
CREATE TABLE attach (
  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `filename` char(100) NOT NULL DEFAULT '',
  `filetype` char(50) NOT NULL DEFAULT '',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0',
  `location` char(100) NOT NULL DEFAULT '',
  `downloads` mediumint(8) NOT NULL DEFAULT '0',
  `isimage` tinyint(1) NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`aid`),
  KEY `uid` (`uid`),
  KEY `time` (`time`,`isimage`,`downloads`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS feedback */
CREATE TABLE feedback (
  `fid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT 0,
  `username` char(18) NOT NULL DEFAULT '',
  `content` char(200) NOT NULL,
  `page` char(100) NOT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(20) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1', /* 反馈状态 */
  PRIMARY KEY(`fid`),
  KEY `uid`(`uid`),
  KEY `time`(`time`),
  KEY `page`(`page`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS setting;
CREATE TABLE setting (
  k varchar(32) NOT NULL default '',
  v text NOT NULL,
  PRIMARY KEY  (k)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO setting VALUES ('index_life', '1'); /* 缓存数据有效时间，单位:分钟 */
INSERT INTO setting VALUES ('admin_email', 'boostme@qq.com'); /* 管理员邮箱 */
INSERT INTO setting VALUES ('auth_key', 'boostmec9eea704535d'); /* 数据加密token */
INSERT INTO setting VALUES ('site_name', 'Boostme'); /* 网站名称 */
INSERT INTO setting VALUES ('seo_prefix', '?'); /* SEO 参数前缀 */
INSERT INTO setting VALUES ('seo_suffix', '.html'); /* SEO 参数后缀 */
INSERT INTO setting VALUES ('sum_onlineuser_time', '30'); /* 用户最常在线时间，单位：分钟 */
INSERT INTO setting VALUES ('max_feedback_num', '20'); /* 过去24小时内，最多反馈数量 */
INSERT INTO setting VALUES ('overdue_days', '60'); /* 求助信息有效时间 */
INSERT INTO setting VALUES ('stopcopy_allowagent', 'webkit\r\nopera\r\nmsie\r\ncompatible\r\nbaiduspider\r\ngoogle\r\nsoso\r\nsogou\r\ngecko\r\nmozilla'); /* 防采集白名单*/
INSERT INTO setting VALUES ('stopcopy_stopagent', ''); /* 防采集黑名单 */
INSERT INTO setting VALUES ('stopcopy_maxnum', '60'); /* 1分钟内最大采集量 */




INSERT INTO setting VALUES ('notify_message', '1');
INSERT INTO setting VALUES ('notify_mail', '0');
INSERT INTO setting VALUES ('max_register_num', '5');
INSERT INTO setting VALUES ('censor_username', '');
INSERT INTO setting VALUES ('access_email', '');
INSERT INTO setting VALUES ('censor_email', '');
INSERT INTO setting VALUES ('stopcopy_on', '0');
INSERT INTO setting VALUES ('allow_register', '1');
INSERT INTO setting VALUES ('code_register', '1');
INSERT INTO setting VALUES ('code_login', '0');
INSERT INTO setting VALUES ('code_problem', '0');
INSERT INTO setting VALUES ('maildefault', 'boostme@qq.com');
INSERT INTO setting VALUES ('list_default', '10');
INSERT INTO setting VALUES ('verify_problem', '0');
INSERT INTO setting VALUES ('editor_toolbars', 'bold,forecolor,insertimage,autotypeset,attachment,link,unlink,map,insertcode,fullscreen');
INSERT INTO setting VALUES ('tpl_dir', 'default');


INSERT INTO setting VALUES ('admin_prob_page_size', '15');
INSERT INTO setting VALUES ('admin_user_page_size', '10');
INSERT INTO setting VALUES ('admin_fb_page_size', '20');


INSERT INTO setting VALUES ('code_ask', '0');
INSERT INTO setting VALUES ('code_message', '0'); 
INSERT INTO setting VALUES ('cookie_domain', '');
INSERT INTO setting VALUES ('cookie_pre', 'bm_');
INSERT INTO setting VALUES ('date_format', 'Y/m/d');
INSERT INTO setting VALUES ('time_format', 'H:i');
INSERT INTO setting VALUES ('time_offset', '8');
INSERT INTO setting VALUES ('time_diff', '0');
INSERT INTO setting VALUES ('site_icp', '');
INSERT INTO setting VALUES ('site_statcode', '');
INSERT INTO setting VALUES ('mailsend', '1');
INSERT INTO setting VALUES ('mailserver', 'smtp.domain.com');
INSERT INTO setting VALUES ('mailport', '25');
INSERT INTO setting VALUES ('mailauth', '0');
INSERT INTO setting VALUES ('mailfrom', 'boostme <boostme@boostme.cn>');
INSERT INTO setting VALUES ('mailauth_username', 'boostme@boostme.cn');
INSERT INTO setting VALUES ('mailauth_password', '');
INSERT INTO setting VALUES ('maildelimiter', '0');
INSERT INTO setting VALUES ('mailusername', '1');
INSERT INTO setting VALUES ('mailsilent', '0');


INSERT INTO setting VALUES ('index_prob_status', '1,2,4,8');

/* 各个页面显示数量设置 */
INSERT INTO setting VALUES ('list_index_per_page', '10');

INSERT INTO setting VALUES ('list_indexnosolve', '10');
INSERT INTO setting VALUES ('list_indexcommend', '10');
INSERT INTO setting VALUES ('list_indexreward', '8');
INSERT INTO setting VALUES ('list_indexnote', '10');
INSERT INTO setting VALUES ('list_indexhottag', '20');
INSERT INTO setting VALUES ('list_indexexpert', '3');
INSERT INTO setting VALUES ('list_indexallscore', '8');
INSERT INTO setting VALUES ('list_indexweekscore', '8');

INSERT INTO setting VALUES ('rss_ttl', '60');
INSERT INTO setting VALUES ('passport_type', '0');
INSERT INTO setting VALUES ('passport_open', '0');
INSERT INTO setting VALUES ('passport_key', '');
INSERT INTO setting VALUES ('passport_client', '');
INSERT INTO setting VALUES ('passport_server', '');
INSERT INTO setting VALUES ('passport_login', 'login.php');
INSERT INTO setting VALUES ('passport_logout', 'login.php?action=quit');
INSERT INTO setting VALUES ('passport_register', 'register.php');
INSERT INTO setting VALUES ('passport_expire', '3600');
INSERT INTO setting VALUES ('passport_credit1', '0');
INSERT INTO setting VALUES ('passport_credit2', '0');
INSERT INTO setting VALUES ('ucenter_open', '0');
INSERT INTO setting VALUES ('ucenter_url', '');
INSERT INTO setting VALUES ('ucenter_ip', '');
INSERT INTO setting VALUES ('ucenter_password', '');
INSERT INTO setting VALUES ('ucenter_ask', '1');
INSERT INTO setting VALUES ('ucenter_answer', '1');
INSERT INTO setting VALUES ('verify_question', '0');
INSERT INTO setting VALUES ('msgtpl', 'a:4:{i:0;a:2:{s:5:"title";s:36:"您的问题{wtbt}有了新回答！";s:7:"content";s:51:"你在{wzmc}上的提出的问题有了新回答！";}i:1;a:2:{s:5:"title";s:54:"恭喜，您对问题{wtbt}的回答已经被采纳！";s:7:"content";s:42:"你在{wzmc}上的回答内容被采纳！";}i:2;a:2:{s:5:"title";s:78:"抱歉，您的问题{wtbt}由于长时间没有处理，现已过期关闭！";s:7:"content";s:69:"您的问题{wtbt}由于长时间没有处理，现已过期关闭！";}i:3;a:2:{s:5:"title";s:42:"您对{wtbt}的回答有了新的评分！";s:7:"content";s:36:"您的回答{hdnr}有了新评分！";}}');
INSERT INTO setting VALUES ('editor_wordcount', 'false');
INSERT INTO setting VALUES ('editor_elementpath', 'false');
INSERT INTO setting VALUES ('gift_range', 'a:3:{i:0;s:2:"50";i:50;s:3:"100";i:100;s:3:"300";}');
INSERT INTO setting VALUES ('usernamepre', 'tipask_');
INSERT INTO setting VALUES ('usercount', '0');
INSERT INTO setting VALUES ('sum_category_time', '60');
INSERT INTO setting VALUES ('del_tmp_crontab', '1440');
INSERT INTO setting VALUES ('allow_credit3', '-10');
INSERT INTO setting VALUES ('apend_question_num', '5');
INSERT INTO setting VALUES ('time_friendly', '1');
INSERT INTO setting VALUES ('register_clause', '<p>&nbsp; &nbsp; &nbsp; &nbsp;当您申请用户时，表示您已经同意遵守本规章。 <br/>欢迎您加入本站点参加交流和讨论，本站点为公共论坛，为维护网上公共秩序和社会稳定，请您自觉遵守以下条款： <br/><br/>一、不得利用本站危害国家安全、泄露国家秘密，不得侵犯国家社会集体的和公民的合法权益，不得利用本站制作、复制和传播下列信息：<br/>　 （一）煽动抗拒、破坏宪法和法律、行政法规实施的；<br/>　（二）煽动颠覆国家政权，推翻社会主义制度的；<br/>　（三）煽动分裂国家、破坏国家统一的；<br/>　（四）煽动民族仇恨、民族歧视，破坏民族团结的；<br/>　（五）捏造或者歪曲事实，散布谣言，扰乱社会秩序的；<br/>　（六）宣扬封建迷信、淫秽、色情、赌博、暴力、凶杀、恐怖、教唆犯罪的；<br/>　（七）公然侮辱他人或者捏造事实诽谤他人的，或者进行其他恶意攻击的；<br/>　（八）损害国家机关信誉的；<br/>　（九）其他违反宪法和法律行政法规的；<br/>　（十）进行商业广告行为的。<br/><br/>二、互相尊重，对自己的言论和行为负责。<br/>三、禁止在申请用户时使用相关本站的词汇，或是带有侮辱、毁谤、造谣类的或是有其含义的各种语言进行注册用户，否则我们会将其删除。<br/>四、禁止以任何方式对本站进行各种破坏行为。<br/>五、如果您有违反国家相关法律法规的行为，本站概不负责，您的登录论坛信息均被记录无疑，必要时，我们会向相关的国家管理部门提供此类信息。</p><p><br/></p><p><br/> </p><p><br/></p>');

/* DROP TABLE IF EXISTS question; */
CREATE TABLE question (
  `qid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author` char(15) NOT NULL DEFAULT '',
  `authorid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `title` char(50) NOT NULL,
  `description` text NOT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  `answers` smallint(5) unsigned NOT NULL DEFAULT '0',
  `attentions` int(10) unsigned NOT NULL DEFAULT '0',
  `goods` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ip` varchar(20) DEFAULT NULL,
  `views` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`qid`),
  KEY `time` (`time`),
  KEY answers (answers),
  KEY authorid (authorid)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS question_attention; */
CREATE TABLE question_attention (
  `qid` int(10) NOT NULL,
  `followerid` int(10) unsigned NOT NULL,
  `follower` char(18) NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`qid`,`followerid`),
  KEY `followerid`(`followerid`),
  KEY `qid`(`qid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS answer; */
CREATE TABLE answer (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `qid` int(10) unsigned NOT NULL DEFAULT '0',
  `title` char(50) NOT NULL,
  `author` varchar(15) NOT NULL DEFAULT '',
  `authorid` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `content` mediumtext NOT NULL,
  `comments` int(10) NOT NULL,
  `ip` varchar(20) DEFAULT NULL,
  `supports` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `qid` (`qid`),
  KEY `authorid` (`authorid`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS answer_comment; */
CREATE TABLE answer_comment (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `aid` int(10) NOT NULL,
  `authorid` int(10) unsigned NOT NULL,
  `author` char(18) NOT NULL,
  `content` varchar(100) NOT NULL,
  `time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `aid`(`aid`),
  KEY `authorid`(`authorid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS answer_support; */
CREATE TABLE answer_support (
  `uid` int(10) unsigned NOT NULL,
  `aid` int(10) NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`uid`,`aid`),
  KEY `uid`(`uid`),
  KEY `aid`(`aid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8; 

/* DROP TABLE IF EXISTS material; */
CREATE TABLE material (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `username` char(18) NOT NULL DEFAULT '',
  `picture` varchar(100) DEFAULT NULL, /* 资料封面图片 */
  `title` char(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` DOUBLE NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1', /* 默认为未审核 */
  `avg_score` DOUBLE NOT NULL DEFAULT '0', /* 平均评价得分 */
  `sold_num` mediumint(8) unsigned NOT NULL DEFAULT '0', /* 卖出多少份 */
  `comment_num` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `view_num` int(10) unsigned NOT NULL DEFAULT '0',
  `site_url` text DEFAULT NULL,
  `access_code` varchar(200) DEFAULT NULL,
  `auto_delivery` smallint(1) NOT NULL DEFAULT '0', /* 是否自动发货 */

  PRIMARY KEY (`id`),
  KEY `uid`(`uid`),
  KEY time (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS register_material; */
CREATE TABLE register_material (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `username` char(18) NOT NULL,
  `description` text NOT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `existed` smallint(1) NOT NULL DEFAULT '0',

  PRIMARY KEY (`id`),
  KEY `uid`(`uid`),
  KEY `time`(`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS material_category_info; */
CREATE TABLE material_category_info (
  `region_id` varchar(32) NOT NULL,
  `region_name` varchar(32) NOT NULL,
  `school_id` varchar(32) NOT NULL,
  `school_name` varchar(128) NOT NULL,
  `dept_id` varchar(128) NOT NULL,
  `dept_name` varchar(128) NOT NULL,
  `major_id` varchar(128) NOT NULL,
  `major_name` varchar(128) NOT NULL,

  KEY `region_id`(`region_id`),
  KEY `region_name`(`region_name`),
  KEY `school_id`(`school_id`),
  KEY `school_name`(`school_name`),
  KEY `dept_id`(`dept_id`),
  KEY `dept_name`(`dept_name`),
  UNIQUE KEY `major_id`(`major_id`),
  KEY `major_name`(`major_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS material_category; */
CREATE TABLE material_category (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `material_id` int(10) unsigned NOT NULL,

  `region_id` varchar(32) NOT NULL,
  `school_id` varchar(32) NOT NULL,
  `dept_id` varchar(128) NOT NULL,
  `major_id` varchar(128) NOT NULL,

  PRIMARY KEY(`id`),
  UNIQUE KEY `mid_majorid`(`material_id`,`major_id`),
  KEY `mid`(`material_id`),
  KEY `region_id`(`region_id`),
  KEY `school_id`(`school_id`),
  KEY `dept_id`(`dept_id`),
  KEY `major_id`(`major_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS material_comment; */
CREATE TABLE material_comment (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `mid` int(10) NOT NULL,
  `authorid` int(10) unsigned NOT NULL,
  `author` char(18) NOT NULL,
  `content` text NOT NULL,
  `time` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `mid`(`mid`),
  KEY `authorid`(`authorid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS material_score; */
CREATE TABLE material_score (
  `uid` int(10) unsigned NOT NULL,
  `mid` int(10) NOT NULL,
  `score` smallint(3) NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`uid`,`mid`),
  KEY `uid`(`uid`),
  KEY `mid`(`mid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8; 

/* DROP TABLE IF EXISTS trade; */
CREATE TABLE trade (
  `trade_no` varchar(32) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `username` char(18) NOT NULL,
  `tot_price` double NOT NULL,
  `status` smallint(3) NOT NULL,
  `goods_num` mediumint(8) NOT NULL, 
  `trade_total_fee` double DEFAULT NULL,
  `trade_discount` double DEFAULT NULL,
  `trade_type` varchar(32) DEFAULT NULL, /* 支付类型：alipay、tenpay等 */
  `trade_mode` smallint(2) NOT NULL, /* 支付方式：即时到账、担保交易等 */
  `transaction_id` varchar(32) DEFAULT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`trade_no`),
  KEY `uid`(`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8; 

/* DROP TABLE IF EXISTS trade_info; */
CREATE TABLE trade_info (
  `trade_no` varchar(32) NOT NULL,
  `mid` int(10) unsigned NOT NULL,
  `title` char(200) NOT NULL,
  `price` double NOT NULL,
  `buy_num` mediumint(8) NOT NULL,
  `time` int(10) NOT NULL,

  PRIMARY KEY (`trade_no`, `mid`),
  KEY (`trade_no`),
  KEY (`mid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8; 

