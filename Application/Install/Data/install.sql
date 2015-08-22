DROP TABLE IF EXISTS `bm_ucenter_user`;
CREATE TABLE `bm_ucenter_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID号',
  `username` char(16) NOT NULL COMMENT '用户名',
  `password` char(32) NOT NULL COMMENT '密码',
  `email` varchar(64) NOT NULL COMMENT '邮件',
  `gender` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '性别(男:0,女:1)',
  `birthday` date DEFAULT NULL COMMENT '出生日期',
  `mobile` char(15) DEFAULT NULL COMMENT '用户手机',
  `qq` char(10) DEFAULT NULL COMMENT 'QQ号',
  `wechat` varchar(30) DEFAULT NULL COMMENT '微信号',
  `reg_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  `reg_ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',
  `last_login_ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) DEFAULT '0' COMMENT '用户状态',

  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `mobile` (`mobile`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8 COMMENT='用户表';

DROP TABLE IF EXISTS bm_user;
CREATE TABLE bm_user (
  `uid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID号',
  `nickname` char(16) NOT NULL DEFAULT '' COMMENT '昵称',
  `paid` double NOT NULL DEFAULT '0' COMMENT '总支出',
  `earned` double NOT NULL DEFAULT '0' COMMENT '总收入',
  `balance` double NOT NULL DEFAULT '0' COMMENT '账户余额',
  `login` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '登录次数',
  `score` mediumint(8) NOT NULL DEFAULT '0' COMMENT '用户积分',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '会员状态',
  `questions` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发帖数量',
  `answers` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回帖数量',
  `reg_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '注册时间',
  `reg_ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `last_login_ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `last_login_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后登录时间',

  PRIMARY KEY (`id`),
  KEY `qq` (`qq`),
  KEY `wechat` (`wechat`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=10001 DEFAULT CHARSET=utf8 COMMENT='会员表';

INSERT INTO bm_user(`uid`,`nickname`,`gender`) values (10001, "test002", 0);
INSERT INTO bm_user(`uid`,`nickname`,`gender`) values (10002, "zero91", 0);

DROP TABLE IF EXISTS bm_service;
CREATE TABLE bm_service (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '服务ID号',
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID号',
  `username` char(16) NOT NULL COMMENT '用户名',
  `content` mediumtext COMMENT '服务内容',
  `duration` mediumint(8) unsigned NOT NULL DEFAULT 10 COMMENT '服务时长(分钟)',
  `supplement` mediumtext COMMENT '补充内容',
  `price` DOUBLE NOT NULL COMMENT '收费价格',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '服务状态(0:未审核)',
  `avg_score` DOUBLE NOT NULL DEFAULT '0' COMMENT '平均评价得分',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `service_num` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '服务次数',
  `comment_num` mediumint(8) unsigned NOT NULL DEFAULT '0' COMMENT '评论数量',
  `view_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '浏览量',

  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT="服务表";

INSERT INTO bm_service VALUES (1, 10002, "zero91", "数学分析的课程讲解", 30, "晚上九点后有时间", 20, 1, 0, 1439127049, 1439127051, 0, 0, 0);
INSERT INTO bm_service VALUES (2, 10002, "zero91", "C++高级编程中值得注意的地方", 10, "晚上九点后有时间", 10, 1, 0, 1439127143, 1439127451, 0, 0, 0);

DROP TABLE IF EXISTS bm_service_category;
CREATE TABLE bm_service_category (
  `service_id` int(10) unsigned NOT NULL COMMENT '服务ID号',
  `region` char(16) NOT NULL COMMENT '地区',
  `school` varchar(64) DEFAULT "" COMMENT '学校',
  `dept` varchar(64) DEFAULT "" COMMENT '院系',
  `major` char(64) DEFAULT "" COMMENT '专业',

  KEY `service_id` (`service_id`),
  KEY `region` (`region`),
  KEY `school` (`school`),
  KEY `dept` (`dept`),
  KEY `major` (`major`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT="服务分类表"; 

INSERT INTO bm_service_category VALUES (1, "北京", "北京大学", "数学科学学院", "基础数学");
INSERT INTO bm_service_category VALUES (1, "北京", "北京大学", "计算机科学与技术系", "计算机科学与技术");
INSERT INTO bm_service_category(`service_id`,`region`,`school`,`dept`)  VALUES (1, "北京", "北京大学", "经济学院");
INSERT INTO bm_service_category(`service_id`,`region`,`school`,`dept`)  VALUES (2, "北京", "北京大学", "数学科学学院");

DROP TABLE IF EXISTS bm_service_comment;
CREATE TABLE bm_service_comment (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '服务评论ID号',
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID号',
  `nickname` char(16) NOT NULL COMMENT '用户昵称',
  `service_id` int(10) unsigned NOT NULL COMMENT '服务ID号',
  `score` DOUBLE NOT NULL COMMENT '评分',
  `content` text NOT NULL COMMENT '评论内容',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `up` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '支持用户数量',
  `down` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '不支持用户数量',

  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `service_id` (`service_id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT="服务评论表";

INSERT INTO bm_service_comment VALUES (1, 10001, "test002", 1, 4, "还不错的服务", 1439135030, 1439135030, 0, 0);
INSERT INTO bm_service_comment VALUES (2, 10002, "zero91", 1, 4.5, "nice", 1439135039, 1439135230, 0, 0);

DROP TABLE IF EXISTS bm_service_comment_op;
CREATE TABLE bm_service_comment_op (
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID号',
  `comment_id` int(10) NOT NULL COMMENT '服务评论ID号',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `operation` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0:有用, 1:无用',

  PRIMARY KEY (`uid`,`comment_id`),
  KEY `uid` (`uid`),
  KEY `comment_id` (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT="评论操作表";

/*TODO*/
/* 服务购买历史表 */

DROP TABLE IF EXISTS bm_message;
CREATE TABLE bm_message (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '私信ID号',
  `from_uid` int(10) unsigned NOT NULL COMMENT '发信用户ID号',
  `from_nickname` char(16) NOT NULL DEFAULT '' COMMENT '发信用户昵称',
  `to_uid` int(10) unsigned NOT NULL COMMENT '收信用户ID号',
  `to_nickname` char(16) NOT NULL DEFAULT '' COMMENT '收信用户昵称',
  `new` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否已被阅读',
  `content` text NOT NULL COMMENT '私信内容',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '发送时间',
  `read_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '阅读时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '私信状态(例如为是否违规等)',

  PRIMARY KEY (`id`),
  KEY `to_uid` (`to_uid`),
  KEY `from_uid` (`from_uid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT="用户私信表";

INSERT INTO bm_message VALUES (1, 10002, "zero91", 10001, "test002", 1, "Testing Message", 1380173180, 0, 0);
INSERT INTO bm_message VALUES (2, 10002, "zero91", 10001, "test002", 1, "Testing Message 2", 1380173182, 0, 0);
INSERT INTO bm_message VALUES (3, 0, "系统管理员", 10001, "test002", 1, "System Message", 1380173982, 0, 0);

DROP TABLE IF EXISTS bm_latest_message;
CREATE TABLE bm_latest_message (
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID号',
  `to_uid` int(10) unsigned NOT NULL COMMENT '对象用户ID号',
  `to_nickname` char(16) NOT NULL DEFAULT '' COMMENT '对象用户昵称',
  `content` text NOT NULL COMMENT '私信内容',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `new_num` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '未阅读信息数量',

  PRIMARY KEY (`uid`, `to_uid`),
  KEY `uid` (`uid`),
  KEY `to_uid` (`to_uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT="用户最新私信表";

INSERT INTO bm_latest_message VALUES (10001, 10002, "zero91", "Testing Message 2", 1380173182, 2);
INSERT INTO bm_latest_message VALUES (10001, 0, "系统管理员", "System Message", 1380173982, 1);

CREATE TABLE trade (
  `trade_no` varchar(32) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `username` char(18) NOT NULL,
  `tot_price` double NOT NULL,
  `status` smallint(3) NOT NULL,
  `goods_num` mediumint(8) NOT NULL, 
  `trade_total_fee` double NOT NULL DEFAULT '0',
  `trade_discount` double DEFAULT NULL,
  `trade_type` varchar(32) DEFAULT NULL, /* 支付类型：alipay、tenpay等 */
  `trade_mode` smallint(2) NOT NULL, /* 支付方式：即时到账、担保交易等 */
  `transaction_id` varchar(32) DEFAULT NULL,
  `pay_account` varchar(64) DEFAULT NULL, /* 用户付款账户 */
  `time` int(10) NOT NULL,

DROP TABLE IF EXISTS bm_trade;
CREATE TABLE bm_trade (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT '订单号',
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID号',
  `username` char(16) NOT NULL COMMENT '用户名',
  `price` double NOT NULL DEFAULT '0' COMMENT '订单价格',
  `item_num` mediumint(8) NOT NULL DEFAULT '0' COMMENT '订单内项目数量',
  `status` smallint(4) NOT NULL DEFAULT '0' COMMENT '订单状态',

  `trade_total_fee` double NOT NULL DEFAULT '0' COMMENT '订单付费收入',
  `trade_discount` double DEFAULT NULL COMMENT '订单支付折扣',
  `trade_type` smallint(4) NOT NULL DEFAULT 0 COMMENT '支付类型(1:alipay, 2:tenpay)',
  `trade_mode` smallint(4) NOT NULL DEFAULT 0 COMMENT '支付方式(1:即时到账, 2:担保交易)',
  `transaction_id` varchar(32) DEFAULT NULL COMMENT '支付对象中该笔交易的ID号',
  `pay_account` varchar(64) DEFAULT NULL COMMENT '用户付款账户',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',

  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `pay_account` (`pay_account`)
) ENGINE=MyISAM AUTO_INCREMENT=100000 DEFAULT CHARSET=utf8 COMMENT '订单表';

INSERT INTO bm_trade VALUES (100001, 10002, 'zero91', 0.1, 2, 0, 0, NULL, 0, 0, NULL, NULL, 0, 0);
INSERT INTO bm_trade VALUES (100002, 10002, 'zero91', 0.2, 1, 4, 0.2, 0, 1, 2, '2015012537564096', 'jianzhang9102@gmail.com', 1422170253, 1422189263);
INSERT INTO bm_trade VALUES (100003, 10001, 'test002', 0.01, 1, 5, 0.01, 0, 1, 2, '2014120850646364', '531322317@qq.com', 1418050776, 1418053973);

DROP TABLE IF EXISTS bm_trade_info;
CREATE TABLE bm_trade_info (
  `trade_id` bigint(20) NOT NULL COMMENT '订单号',
  `item_id` int(10) unsigned NOT NULL COMMENT '物品ID号',
  `item_type` mediumint(8) NOT NULL COMMENT '商品类型(1:资料, 2:咨询, 3:验证支付宝账户)',
  `quantity` mediumint(8) NOT NULL DEFAULT '1' COMMENT '购买数量',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',

  PRIMARY KEY (`trade_id`, `item_id`, `item_type`),
  KEY (`item_id`, `item_type`),
  KEY (`trade_id`),
  KEY (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT '订单详情表';

INSERT INTO bm_trade_info VALUES (100002, 1, 2, 1, 1439523848, 1439523848);
INSERT INTO bm_trade_info VALUES (100002, 2, 2, 1, 1439523889, 1439523889);


DROP TABLE IF EXISTS bm_withdraw;
CREATE TABLE bm_withdraw (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '套现ID号',
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID号',
  `money` DOUBLE NOT NULL COMMENT '套现金额',
  `ebank_type` smallint(4) NOT NULL DEFAULT '0' COMMENT '账户类型(1:支付宝, 2:财付通)',
  `ebank_account` varchar(64) NOT NULL COMMENT '电子账户：支付宝账号、财付通账号等',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '套现状态',
  `operator_uid` int(10) unsigned DEFAULT NULL COMMENT '打款操作用户ID号',
  `operator_username` char(18) DEFAULT NULL COMMENT '打款操作用户名',

  PRIMARY KEY (`id`),
  KEY `uid`(`uid`),
  KEY `ebank_account` (`ebank_account`),
  KEY `operator_uid` (`operator_uid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '用户套现表';

DROP TABLE IF EXISTS bm_user_ebank;
CREATE TABLE bm_user_ebank (
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID号',
  `ebank_type` smallint(4) NOT NULL DEFAULT '0' COMMENT '账户类型(1:支付宝,2:财付通)',
  `ebank_account` varchar(64) NOT NULL COMMENT '电子账户：支付宝账号、财付通账号等',
  `isdefault` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否是默认账户',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '绑定时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',

  PRIMARY KEY (`uid`,`ebank_type`,`ebank_account`),
  KEY `uid` (`uid`),
  KEY `ebank_account` (`ebank_account`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS bm_attachment;
CREATE TABLE bm_attachment (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户ID号',
  `filename` char(100) NOT NULL DEFAULT '' COMMENT '附件名',
  `filetype` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '附件类型',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0',
  `location` char(100) NOT NULL DEFAULT '',
  `downloads` int(10) unsigned NOT NULL DEFAULT '0',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后更新时间',

  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='附件表';

DROP TABLE IF EXISTS bm_posts;
CREATE TABLE bm_posts (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID号',
  `nickname` char(16) NOT NULL COMMENT '用户昵称',
  `title` varchar(64) NOT NULL COMMENT '主题',
  `content` text NOT NULL COMMENT '详细内容',
  `answers` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '回复数',
  `collects` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '收藏量',
  `views` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '阅读量',
  `ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '发帖IP',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '帖子状态',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',

  PRIMARY KEY (`id`),
  KEY `create_time` (`create_time`),
  KEY `update_time` (`update_time`),
  KEY `uid` (`uid`),
  KEY `answers` (`answers`),
  KEY `collects` (`collects`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '发帖表';

INSERT INTO bm_posts VALUES (1, 10002, "zero91", "测试帖子", "Just测试", 0, 0, 0, 0, 0, 1439285635, 1439285637);
INSERT INTO bm_posts VALUES (2, 10001, "test002", "test002的测试帖子", "I'm test002!", 0, 0, 0, 0, 0, 1439285239, 1439285618);

DROP TABLE IF EXISTS bm_post_answer;
CREATE TABLE bm_post_answer (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID号',
  `nickname` char(16) NOT NULL COMMENT '用户昵称',
  `content` mediumtext NOT NULL COMMENT '回复内容',
  `pid` int(10) unsigned NOT NULL COMMENT '帖子ID号',
  `ptitle` varchar(64) NOT NULL COMMENT '帖子主题',
  `comments` int(10) NOT NULL DEFAULT '0' COMMENT '评论数量',
  `ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '回复IP',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',

  PRIMARY KEY (`id`),
  KEY `uid` (`uid`),
  KEY `pid` (`pid`),
  KEY `create_time` (`create_time`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT '帖子回复表';

DROP TABLE IF EXISTS bm_post_collect;
CREATE TABLE bm_post_collect (
  `pid` int(10) NOT NULL COMMENT '帖子ID号',
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID号',
  `nickname` char(16) NOT NULL COMMENT '用户昵称',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  `update_time` int(10) NOT NULL COMMENT '最后更新时间',
  `valid` int(10) NOT NULL DEFAULT '1' COMMENT '该收藏信息是否有效',

  PRIMARY KEY (`pid`,`uid`),
  KEY `followerid`(`uid`),
  KEY `pid`(`pid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS bm_answer_comment;
CREATE TABLE bm_answer_comment (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '帖子回复评论ID号',
  `uid` int(10) unsigned NOT NULL COMMENT '用户ID号',
  `nickname` char(16) NOT NULL COMMENT '用户昵称',
  `content` varchar(100) NOT NULL,
  `aid` int(10) unsigned NOT NULL COMMENT '帖子回复ID号',
  `ip` bigint(20) NOT NULL DEFAULT '0' COMMENT '评论IP',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '帖子回复评论创建时间',

  PRIMARY KEY (`id`),
  KEY `aid` (`aid`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

