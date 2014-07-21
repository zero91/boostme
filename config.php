<?php 
define('DB_HOST', 'localhost');
define('DB_USER', 'boostme');
define('DB_PW', 'boostme');
define('DB_NAME', 'boostme');
define('DB_CHARSET', 'utf8');
define('DB_CONNECT', 0);
define('WEB_CHARSET', 'UTF-8');

// problem
define('PB_STATUS_UNAUDIT', 0);    // 未审核
define('PB_STATUS_UNSOLVED', 1);   // 未解决
define('PB_STATUS_SOLVED', 2);     // 已经解决
define('PB_STATUS_CLOSED', 4);     // 需求已经关闭
define('PB_STATUS_AUDIT', 8);      // 已经审核，但未通过

// demand
define('DEMAND_STATUS_QUEUE', 0);   // 请求正在排队
define('DEMAND_STATUS_ACCEPT', 1);  // 达成合作
define('DEMAND_STATUS_DENIED', 2);  // 请求被拒绝
define('DEMAND_STATUS_EXPIRED', 4); // 请求已过期，发生在需求已经被解决

// message
define('DEFAULT_DEMAND_MESSAGE', "您好，我对您的需求十分感兴趣，希望能够帮助您!");
define('MSG_STATUS_NODELETED', 0);    // 都没有删除
define('MSG_STATUS_FROM_DELETED', 1); // 发送方删除
define('MSG_STATUS_TO_DELETED', 2);   // 接受方删除

// charge
define('CHARGING_STATUS_DEMAND', 0);  // 尚未达成交易
define('CHARGING_STATUS_DEAL', 1);    // 达成交易，但尚未付款。发生在交易达成后的一段时间内
define('CHARGING_STATUS_SUCCEED', 2); // 达成交易，并付款
define('CHARGING_STATUS_INVALID', 4); // 交易无效

// index page
define('INDEX_PROBLEM_LIST_SIZE', 10); // 首页单页求助数量
