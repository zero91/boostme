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
  `solved` int(10) unsigned NOT NULL DEFAULT '0', /* 解决的需求数量 */
  `failed` int(10) unsigned NOT NULL DEFAULT '0', /* 未抢到的需求数量 */
  `can_teach` tinyint(3) unsigned NOT NULL DEFAULT '0', /* 是否可以教别人，获取抢单的资格 */
  `teach_level` smallint(5) NOT NULL DEFAULT '0', /* 授课水平 */
  `credit` smallint(5) NOT NULL DEFAULT '0', /* 用户信用等级 */
  `paid` DOUBLE NOT NULL DEFAULT '0', /* 总共付费 */
  `earned` DOUBLE NOT NULL DEFAULT '0', /* 总共收入 */
  
  PRIMARY KEY (`uid`),
  KEY username(username),
  KEY email(email)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
/* alter table `user` add column `balance` int(10) NOT NULL DEFAULT '0'; */

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

/* DROP TABLE IF EXISTS userlog; */
CREATE TABLE userlog (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `sid` varchar(10) NOT NULL DEFAULT '',
  `uid` int(10) NOT NULL DEFAULT 0,
  `type` enum('login','problem','demand','cancel','accept','denied') NOT NULL,
  `time` int(10) NOT NULL,
  `comment` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sid` (`sid`),
  KEY `uid` (`uid`),
  KEY `time` (`time`),
  KEY `type`(`time`)
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
  `cid` varchar(32) DEFAULT NULL,
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
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `status` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`mid`),
  KEY `touid` (`touid`,`time`),
  KEY `fromuid` (`fromuid`,`time`)
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
INSERT INTO setting VALUES ('index_life', '1');
INSERT INTO setting VALUES ('admin_email', 'boostme@qq.com');
INSERT INTO setting VALUES ('site_name', 'Boostme');
INSERT INTO setting VALUES ('notify_message', '1');
INSERT INTO setting VALUES ('notify_mail', '0');
INSERT INTO setting VALUES ('auth_key', 'boostmec9eea704535d');
INSERT INTO setting VALUES ('sum_onlineuser_time', '30');
INSERT INTO setting VALUES ('max_register_num', '5');
INSERT INTO setting VALUES ('censor_username', '');
INSERT INTO setting VALUES ('access_email', '');
INSERT INTO setting VALUES ('censor_email', '');
INSERT INTO setting VALUES ('overdue_days', '60');
INSERT INTO setting VALUES ('stopcopy_on', '0');
INSERT INTO setting VALUES ('stopcopy_allowagent', 'webkit\r\nopera\r\nmsie\r\ncompatible\r\nbaiduspider\r\ngoogle\r\nsoso\r\nsogou\r\ngecko\r\nmozilla');
INSERT INTO setting VALUES ('stopcopy_stopagent', '');
INSERT INTO setting VALUES ('allow_register', '1');
INSERT INTO setting VALUES ('code_register', '1');
INSERT INTO setting VALUES ('code_login', '0');
INSERT INTO setting VALUES ('code_problem', '0');
INSERT INTO setting VALUES ('maildefault', 'boostme@qq.com');
INSERT INTO setting VALUES ('list_default', '10');
INSERT INTO setting VALUES ('verify_problem', '0');
INSERT INTO setting VALUES ('editor_toolbars', 'bold,forecolor,insertimage,autotypeset,attachment,link,unlink,map,insertcode,fullscreen');
INSERT INTO setting VALUES ('tpl_dir', 'default');
INSERT INTO setting VALUES ('max_feedback_num', '20');


INSERT INTO setting VALUES ('admin_prob_page_size', '15');
INSERT INTO setting VALUES ('admin_user_page_size', '10');
INSERT INTO setting VALUES ('admin_fb_page_size', '20');


INSERT INTO setting VALUES ('code_ask', '0');
INSERT INTO setting VALUES ('code_message', '0'); 
INSERT INTO setting VALUES ('cookie_domain', '');
INSERT INTO setting VALUES ('cookie_pre', 'bm_');
INSERT INTO setting VALUES ('seo_prefix', '?');
INSERT INTO setting VALUES ('seo_suffix', '.html');
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
INSERT INTO setting VALUES ('stopcopy_maxnum', '60');
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


DROP TABLE IF EXISTS category;
CREATE TABLE category (
  `cid` varchar(32) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(100) DEFAULT NULL,

  PRIMARY KEY (`cid`),
  KEY `name`(`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


INSERT INTO category VALUES ('S100001','清华大学','北京考研院校');
INSERT INTO category VALUES ('S100002','北京大学','北京考研院校');
INSERT INTO category VALUES ('S100003','中国人民大学','北京考研院校');
INSERT INTO category VALUES ('S100004','中国政法大学','北京考研院校');
INSERT INTO category VALUES ('S100005','北京航空航天大学','北京考研院校');
INSERT INTO category VALUES ('S100006','中央财经大学','北京考研院校');
INSERT INTO category VALUES ('S100007','北京邮电大学','北京考研院校');
INSERT INTO category VALUES ('S100008','中国传媒大学','北京考研院校');
INSERT INTO category VALUES ('S100009','北京师范大学','北京考研院校');
INSERT INTO category VALUES ('S100010','北京化工大学','北京考研院校');
INSERT INTO category VALUES ('S100011','北京理工大学','北京考研院校');
INSERT INTO category VALUES ('S100012','北京交通大学','北京考研院校');
INSERT INTO category VALUES ('S100013','中国地质大学(北京)','北京考研院校');
INSERT INTO category VALUES ('S100014','中央民族大学','北京考研院校');
INSERT INTO category VALUES ('S100015','北京工业大学','北京考研院校');
INSERT INTO category VALUES ('S100016','北京外国语大学','北京考研院校');
INSERT INTO category VALUES ('S100017','首都经济贸易大学','北京考研院校');
INSERT INTO category VALUES ('S100018','中国人民公安大学','北京考研院校');
INSERT INTO category VALUES ('S100019','北京科技大学','北京考研院校');
INSERT INTO category VALUES ('S100020','首都师范大学','北京考研院校');
INSERT INTO category VALUES ('S100021','对外经济贸易大学','北京考研院校');
INSERT INTO category VALUES ('S100022','华北电力大学(北京)','北京考研院校');
INSERT INTO category VALUES ('S100023','中国石油大学(北京)','北京考研院校');
INSERT INTO category VALUES ('S100024','中国矿业大学(北京)','北京考研院校');
INSERT INTO category VALUES ('S100025','北京电影学院','北京考研院校');
INSERT INTO category VALUES ('S100026','北京工商大学','北京考研院校');
INSERT INTO category VALUES ('S100027','中国农业大学','北京考研院校');
INSERT INTO category VALUES ('S100028','北京林业大学','北京考研院校');
INSERT INTO category VALUES ('S100029','中央音乐学院','北京考研院校');
INSERT INTO category VALUES ('S100030','北京中医药大学','北京考研院校');
INSERT INTO category VALUES ('S100031','北京体育大学','北京考研院校');
INSERT INTO category VALUES ('S100032','北京语言大学','北京考研院校');
INSERT INTO category VALUES ('S100033','首都医科大学','北京考研院校');
INSERT INTO category VALUES ('S100034','财政部财政科学研究所','北京考研院校');
INSERT INTO category VALUES ('S100035','中国科学院','北京考研院校');
INSERT INTO category VALUES ('S100036','中国社会科学院','北京考研院校');
INSERT INTO category VALUES ('S100037','中国农业科学院','北京考研院校');
INSERT INTO category VALUES ('S100038','中国林业科学研究院','北京考研院校');
INSERT INTO category VALUES ('S100039','中央戏剧学院','北京考研院校');
INSERT INTO category VALUES ('S100040','北方工业大学','北京考研院校');
INSERT INTO category VALUES ('S100041','北京信息科技大学','北京考研院校');
INSERT INTO category VALUES ('S200001','复旦大学','上海考研院校');
INSERT INTO category VALUES ('S200002','上海交通大学','上海考研院校');
INSERT INTO category VALUES ('S200003','同济大学','上海考研院校');
INSERT INTO category VALUES ('S200004','华东师范大学','上海考研院校');
INSERT INTO category VALUES ('S200005','上海财经大学','上海考研院校');
INSERT INTO category VALUES ('S200006','华东理工大学','上海考研院校');
INSERT INTO category VALUES ('S200007','上海外国语大学','上海考研院校');
INSERT INTO category VALUES ('S200008','华东政法大学','上海考研院校');
INSERT INTO category VALUES ('S200009','上海大学','上海考研院校');
INSERT INTO category VALUES ('S200010','上海理工大学','上海考研院校');
INSERT INTO category VALUES ('S200011','东华大学','上海考研院校');
INSERT INTO category VALUES ('S200012','上海师范大学','上海考研院校');
INSERT INTO category VALUES ('S200013','上海海事大学','上海考研院校');
INSERT INTO category VALUES ('S200014','第二军医大学','上海考研院校');
INSERT INTO category VALUES ('S200015','上海体育学院','上海考研院校');
INSERT INTO category VALUES ('S200016','上海中医药大学','上海考研院校');
INSERT INTO category VALUES ('S200017','上海对外经贸大学','上海考研院校');
INSERT INTO category VALUES ('S300001','南开大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300002','天津大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300003','天津医科大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300004','天津财经大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300005','天津师范大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300006','天津科技大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300007','天津工业大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300008','天津商业大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300009','河北工业大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300010','华北电力大学（保定）','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300011','燕山大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300012','河北大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300013','河北师范大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300014','山西师范大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300015','山西财经大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300016','山西大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300017','太原理工大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300018','中北大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300019','内蒙古大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300020','内蒙古师范大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S300021','内蒙古工业大学','华北考研院校（天津 | 河北 | 内蒙古 | 山西）');
INSERT INTO category VALUES ('S400001','东北大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400002','大连理工大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400003','东北财经大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400004','大连海事大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400005','辽宁师范大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400006','辽宁大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400007','沈阳工业大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400008','辽宁工程技术大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400009','沈阳师范大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400010','沈阳药科大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400011','中国医科大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400012','大连交通大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400013','吉林大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400014','东北师范大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400015','延边大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400016','长春理工大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400017','哈尔滨工业大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400018','哈尔滨工程大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400019','黑龙江大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400020','东北农业大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400021','东北林业大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400022','哈尔滨师范大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400023','哈尔滨理工大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400024','东北石油大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S400025','大连工业大学','东北考研院校（辽宁 | 吉林 | 黑龙江）');
INSERT INTO category VALUES ('S500001','山东大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500002','中国海洋大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500003','中国石油大学(华东)','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500004','山东师范大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500005','青岛大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500006','山东农业大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500007','山东科技大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500008','曲阜师范大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500009','青岛科技大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500010','山东财经大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500011','南京大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500012','东南大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500013','南京航空航天大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500014','南京师范大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500015','南京理工大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500016','苏州大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500017','河海大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500018','中国矿业大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500019','南京邮电大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500020','中国药科大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500021','南京信息工程大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500022','江苏大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500023','江南大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500024','扬州大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500025','南京农业大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500026','南京工业大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500027','浙江大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500028','浙江工业大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500029','宁波大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500030','浙江师范大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500031','南京财经大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500032','浙江理工大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500033','杭州电子科技大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500034','浙江工商大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500035','杭州师范大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500036','浙江财经大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500037','厦门大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500038','福州大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500039','福建师范大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500040','福建农林大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500041','华侨大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500042','中国科学技术大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500043','合肥工业大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500044','安徽大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500045','安徽师范大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500046','安徽理工大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500047','南昌大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500048','江西师范大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500049','江西财经大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S500050','华东交通大学','华东考研院校（山东 | 江苏 | 浙江 | 福建 | 安徽 | 江西）');
INSERT INTO category VALUES ('S600001','郑州大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600002','河南大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600003','河南师范大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600004','河南理工大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600005','中南民族大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600006','武汉大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600007','华中科技大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600008','华中师范大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600009','武汉理工大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600010','中南财经政法大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600011','湖北大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600012','中国地质大学(武汉)','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600013','华中农业大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600014','武汉科技大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600015','长江大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600016','湖南大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600017','中南大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600018','湖南师范大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600019','湘潭大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600020','国防科技大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600021','长沙理工大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600022','南华大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600023','湖南工业大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S600024','吉首大学','华中考研院校（河南 | 湖北 | 湖南 ）');
INSERT INTO category VALUES ('S700001','广州大学','华南考研院校（广东|广西|海南）');
INSERT INTO category VALUES ('S700002','中山大学','华南考研院校（广东|广西|海南）');
INSERT INTO category VALUES ('S700003','华南理工大学','华南考研院校（广东|广西|海南）');
INSERT INTO category VALUES ('S700004','华南师范大学','华南考研院校（广东|广西|海南）');
INSERT INTO category VALUES ('S700005','暨南大学','华南考研院校（广东|广西|海南）');
INSERT INTO category VALUES ('S700006','深圳大学','华南考研院校（广东|广西|海南）');
INSERT INTO category VALUES ('S700007','广东工业大学','华南考研院校（广东|广西|海南）');
INSERT INTO category VALUES ('S700008','广东外语外贸大学','华南考研院校（广东|广西|海南）');
INSERT INTO category VALUES ('S700009','汕头大学','华南考研院校（广东|广西|海南）');
INSERT INTO category VALUES ('S700010','华南农业大学','华南考研院校（广东|广西|海南）');
INSERT INTO category VALUES ('S700011','南方医科大学','华南考研院校（广东|广西|海南）');
INSERT INTO category VALUES ('S700012','广州中医药大学','华南考研院校（广东|广西|海南）');
INSERT INTO category VALUES ('S700013','广西大学','华南考研院校（广东|广西|海南）');
INSERT INTO category VALUES ('S700014','桂林电子科技大学','华南考研院校（广东|广西|海南）');
INSERT INTO category VALUES ('S700015','广西师范大学','华南考研院校（广东|广西|海南）');
INSERT INTO category VALUES ('S700016','海南大学','华南考研院校（广东|广西|海南）');
INSERT INTO category VALUES ('S800001','西安交通大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800002','西安电子科技大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800003','长安大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800004','陕西师范大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800005','西北大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800006','西北工业大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800007','西安建筑科技大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800008','西北农林科技大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800009','西安理工大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800010','西安科技大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800011','陕西科技大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800012','西北政法大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800013','第四军医大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800014','兰州大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800015','兰州交通大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800016','西北师范大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800017','兰州理工大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800018','青海大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800019','宁夏大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800020','新疆大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S800021','石河子大学','西北考研院校（陕西 | 甘肃 | 宁夏 | 青海 | 新疆）');
INSERT INTO category VALUES ('S900001','四川大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900002','西南财经大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900003','电子科技大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900004','西南交通大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900005','成都理工大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900006','西南民族大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900007','西南石油大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900008','四川师范大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900009','四川农业大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900010','成都中医药大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900011','成都信息工程学院','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900012','重庆大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900013','重庆邮电大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900014','西南政法大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900015','重庆工商大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900016','重庆师范大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900017','重庆交通大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900018','西南大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900019','云南大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900020','昆明理工大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900021','云南师范大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900022','云南民族大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900023','贵州大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900024','贵州师范大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');
INSERT INTO category VALUES ('S900025','西藏大学','西南考研院校（四川 | 重庆 | 云南 | 贵州 | 西藏）');

INSERT INTO category VALUES ('C100001','政治','公共课');
INSERT INTO category VALUES ('C100002','英语1','公共课');
INSERT INTO category VALUES ('C100003','英语2','公共课');
INSERT INTO category VALUES ('C100004','数学','公共课');
INSERT INTO category VALUES ('C200001','计算机与软件','统考/联考科目');
INSERT INTO category VALUES ('C200002','历史学','统考/联考科目');
INSERT INTO category VALUES ('C200003','医学','统考/联考科目');
INSERT INTO category VALUES ('C200004','教育学','统考/联考科目');
INSERT INTO category VALUES ('C200005','心理学','统考/联考科目');
INSERT INTO category VALUES ('C200006','农学','统考/联考科目');
INSERT INTO category VALUES ('C200007','日语','统考/联考科目');
INSERT INTO category VALUES ('C200008','俄语','统考/联考科目');
INSERT INTO category VALUES ('C300001','哲学','专业课');
INSERT INTO category VALUES ('C300002','经济学','专业课');
INSERT INTO category VALUES ('C300003','金融学','专业课');
INSERT INTO category VALUES ('C300004','国际贸易','专业课');
INSERT INTO category VALUES ('C300005','法学','专业课');
INSERT INTO category VALUES ('C300006','政治学','专业课');
INSERT INTO category VALUES ('C300007','社会学','专业课');
INSERT INTO category VALUES ('C300008','民族学','专业课');
INSERT INTO category VALUES ('C300009','马克思主义理论','专业课');
INSERT INTO category VALUES ('C300010','体育学','专业课');
INSERT INTO category VALUES ('C300011','中国语言文学','专业课');
INSERT INTO category VALUES ('C300012','英语语言文学','专业课');
INSERT INTO category VALUES ('C300013','外国语言文学','专业课');
INSERT INTO category VALUES ('C300014','新闻传播学','专业课');
INSERT INTO category VALUES ('C300015','数学专业','专业课');
INSERT INTO category VALUES ('C300016','物理学','专业课');
INSERT INTO category VALUES ('C300017','化学与化工','专业课');
INSERT INTO category VALUES ('C300018','天文学','专业课');
INSERT INTO category VALUES ('C300019','地理学','专业课');
INSERT INTO category VALUES ('C300020','大气科学','专业课');
INSERT INTO category VALUES ('C300021','海洋科学','专业课');
INSERT INTO category VALUES ('C300022','地球物理学','专业课');
INSERT INTO category VALUES ('C300023','地学与地质','专业课');
INSERT INTO category VALUES ('C300024','生物学','专业课');
INSERT INTO category VALUES ('C300025','系统科学','专业课');
INSERT INTO category VALUES ('C300026','生态学','专业课');
INSERT INTO category VALUES ('C300027','力学','专业课');
INSERT INTO category VALUES ('C300028','材料科学与工程','专业课');
INSERT INTO category VALUES ('C300029','电子与信息','专业课');
INSERT INTO category VALUES ('C300030','计算机科学与技术','专业课');
INSERT INTO category VALUES ('C300031','基础医学','专业课');
INSERT INTO category VALUES ('C300032','公共卫生与预防医学','专业课');
INSERT INTO category VALUES ('C300033','药学/中药学','专业课');
INSERT INTO category VALUES ('C300034','医学技术/护理学','专业课');
INSERT INTO category VALUES ('C300035','机械工程','专业课');
INSERT INTO category VALUES ('C300036','车辆工程','专业课');
INSERT INTO category VALUES ('C300037','光学工程','专业课');
INSERT INTO category VALUES ('C300038','仪器科学与技术','专业课');
INSERT INTO category VALUES ('C300039','冶金工程','专业课');
INSERT INTO category VALUES ('C300040','动力工程及工程热物理','专业课');
INSERT INTO category VALUES ('C300041','电气工程','专业课');
INSERT INTO category VALUES ('C300042','控制科学/自动化','专业课');
INSERT INTO category VALUES ('C300043','土木工程/建筑学','专业课');
INSERT INTO category VALUES ('C300044','水利工程','专业课');
INSERT INTO category VALUES ('C300045','测绘科学与技术','专业课');
INSERT INTO category VALUES ('C300046','矿业工程','专业课');
INSERT INTO category VALUES ('C300047','石油与天然气工程','专业课');
INSERT INTO category VALUES ('C300048','纺织科学与工程','专业课');
INSERT INTO category VALUES ('C300049','轻工技术与工程','专业课');
INSERT INTO category VALUES ('C300050','交通运输工程','专业课');
INSERT INTO category VALUES ('C300051','船舶与海洋工程','专业课');
INSERT INTO category VALUES ('C300052','航空宇航科学与技术','专业课');
INSERT INTO category VALUES ('C300053','兵器科学与技术','专业课');
INSERT INTO category VALUES ('C300054','核科学与技术','专业课');
INSERT INTO category VALUES ('C300055','农业工程/林业工程','专业课');
INSERT INTO category VALUES ('C300056','环境科学与工程','专业课');
INSERT INTO category VALUES ('C300057','生物医学工程','专业课');
INSERT INTO category VALUES ('C300058','食品科学与工程','专业课');
INSERT INTO category VALUES ('C300059','城乡规划/风景园林','专业课');
INSERT INTO category VALUES ('C300060','软件工程','专业课');
INSERT INTO category VALUES ('C300061','生物工程','专业课');
INSERT INTO category VALUES ('C300062','管理科学与工程','专业课');
INSERT INTO category VALUES ('C300063','工学（其他）','专业课');
INSERT INTO category VALUES ('C300064','保险与精算','专业课');
INSERT INTO category VALUES ('C300065','环境与市政','专业课');
INSERT INTO category VALUES ('C300066','管理学','专业课');
INSERT INTO category VALUES ('C300067','会计学','专业课');
INSERT INTO category VALUES ('C300068','工商管理','专业课');
INSERT INTO category VALUES ('C300069','公共管理','专业课');
INSERT INTO category VALUES ('C300070','图书情报与档案管理','专业课');
INSERT INTO category VALUES ('C300071','农林经济管理','专业课');
INSERT INTO category VALUES ('C300072','艺术学','专业课');

/* DROP TABLE IF EXISTS discuss; */
/* CREATE TABLE discuss (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `from` varchar(15) NOT NULL DEFAULT '',
  `fromuid` int(10) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(75) NOT NULL DEFAULT '',
  `ip` varchar(20) DEFAULT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `content` text NOT NULL, 

  PRIMARY KEY (`id`),
  KEY `fromuid` (`fromuid`),
  KEY time (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
*/

/* DROP TABLE IF EXISTS question; */
CREATE TABLE question (
  `qid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author` char(15) NOT NULL DEFAULT '',
  `authorid` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `title` char(50) NOT NULL,
  `description` text NOT NULL,
  `time` int(10) unsigned NOT NULL DEFAULT '0',
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
  KEY `aid`(`aid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/* DROP TABLE IF EXISTS answer_support; */
CREATE TABLE answer_support (
  `uid` int(10) unsigned NOT NULL,
  `aid` int(10) NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`uid`,`aid`),
  KEY `uid`(`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8; 

DROP TABLE IF EXISTS material;
CREATE TABLE material (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `username` char(18) NOT NULL DEFAULT '',
  `title` char(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` DOUBLE NOT NULL DEFAULT '0',
  `time` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '1', /* 默认为未审核 */
  `avg_score` DOUBLE NOT NULL DEFAULT '0', /* 平均评价得分 */
  `sold_num` mediumint(8) unsigned NOT NULL DEFAULT '0', /* 卖出多少份 */
  `comment_num` mediumint(8) unsigned NOT NULL DEFAULT '0',

  PRIMARY KEY (`id`),
  KEY `uid`(`uid`),
  KEY time (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS material_category;
CREATE TABLE material_category (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `material_id` int(10) unsigned NOT NULL,
  `cid` varchar(32) NOT NULL,

  PRIMARY KEY(`id`),
  UNIQUE KEY `mid_cid`(`material_id`,`cid`),
  KEY `mid`(`material_id`),
  KEY `cid`(`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS material_comment;
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

DROP TABLE IF EXISTS material_score ;
CREATE TABLE material_score (
  `uid` int(10) unsigned NOT NULL,
  `mid` int(10) NOT NULL,
  `score` smallint(3) NOT NULL,
  `time` int(10) NOT NULL,
  PRIMARY KEY (`uid`,`mid`),
  KEY `uid`(`uid`),
  KEY `mid`(`mid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8; 

