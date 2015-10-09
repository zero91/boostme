<?php

// 私信状态常量
define('MSG_STATUS_NOT_DELETED', 0);  // 都没有删除
define('MSG_STATUS_FROM_DELETED', 1); // 发送方删除
define('MSG_STATUS_TO_DELETED', 2);   // 接受方删除
define('MSG_STATUS_BOTH_DELETED', 3); // 双方都已经删除

// 订单状态常量
define('TRADE_STATUS_WAIT_BUYER_PAY', 1);           // 等待买家付款
define('TRADE_STATUS_WAIT_SELLER_SEND_GOODS', 2);   // 买家已付款,等待卖家发货
define('TRADE_STATUS_WAIT_BUYER_CONFIRM_GOODS', 3); // 卖家已发货,等待买家确认
define('TRADE_STATUS_FINISHED', 4);                 // 交易成功结束
define('TRADE_STATUS_CLOSED', 5);                   // 交易中途关闭(已结束,未成功完成)

// 服务状态信息
define('SERVICE_STATUS_APPLY', 1);    // 申请咨询，但未获取资格
define('SERVICE_STATUS_ACCEPTED', 2); // 获取咨询资格
define('SERVICE_STATUS_DENIED', 3);   // 审核不通过
define('SERVICE_STATUS_CLOSED', 4);   // 关闭咨询，用户暂时不对外提供咨询服务

// 交易商品类型
define('TRADE_TARGET_MATERIAL', 1); // 交易对象为：资料
define('TRADE_TARGET_SERVICE', 2);  // 交易对象为：服务
define('TRADE_TARGET_VERIFY_EBANK', 3); // 交易对象为：验证用户的电子银行、支付宝等的账户

// 学历
define('EDU_HIGH_SCHOOL', 4);
define('EDU_BACHELOR', 5);
define('EDU_MASTER', 6);
define('EDU_DOCTOR', 7);
define('EDU_POST_DOCTOR', 8);

return array(
    'CODE_REGISTER'                => true, // 注册时是否需要验证码
    'CODE_LOGIN'                   => true, // 登录时是否需要验证码
    'MESSAGE_NUM_PER_PAGE'         => 10,
    'SERVICE_NUM_PER_PAGE'         => 12,
    'SERVICE_COMMENT_NUM_PER_PAGE' => 10,
    'POSTS_NUM_PER_PAGE'           => 10,
    'POST_ANSWER_NUM_PER_PAGE'     => 10,
    'ANSWER_COMMENT_NUM_PER_PAGE'  => 10,
    'TRADE_NUM_PER_PAGE'           => 10,

    'PUBLIC_RESOURCE_PATH'         => './Public/', // 生成学校院系分类JS数据时使用目录

    'PAY_PRODUCT_NAME'             => '帮考研',    // 递交给第三方支付机构时显示的商品名

    'EDITOR_TOOLBARS'              => "'bold','forecolor','insertimage','autotypeset','attachment','link','unlink','map','insertcode','fullscreen'",

    // 文件上传相关配置
    'DOWNLOAD_UPLOAD' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 5*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg,zip,rar,tar,gz,7z,pdf,doc,docx,ppt,pptx,txt,xml', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/Download/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
        'fieldKey' => 'upfile', // 获取上传数据信息的key
    ), //下载模型上传配置（文件上传类配置）

    // 编辑器图片上传相关配置
    'EDITOR_UPLOAD' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 2*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'  => true, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/Editor/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => false, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
        'fieldKey' => 'upfile', // 获取上传数据信息的key
    ),

    // 编辑器图片上传相关配置
    'AVATAR_UPLOAD' => array(
        'mimes'    => '', //允许上传的文件MiMe类型
        'maxSize'  => 5*1024*1024, //上传的文件大小限制 (0-不做限制)
        'exts'     => 'jpg,gif,png,jpeg', //允许上传的文件后缀
        'autoSub'  => false, //自动子目录保存文件
        'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
        'rootPath' => './Uploads/Avatar/original/', //保存根路径
        'savePath' => '', //保存路径
        'saveName' => array('uniqid', ''), //上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
        'saveExt'  => '', //文件保存后缀，空则使用原后缀
        'replace'  => true, //存在同名是否覆盖
        'hash'     => true, //是否生成hash编码
        'callback' => false, //检测文件是否存在回调函数，如果存在返回文件信息数组
        'fieldKey' => 'upfile', // 获取上传数据信息的key
    ),
);

/*
// problem
define('PB_STATUS_INVALID', 0);    // 无效问题
define('PB_STATUS_UNAUDIT', 1);    // 未审核
define('PB_STATUS_UNSOLVED', 2);   // 未解决
define('PB_STATUS_SOLVED', 4);     // 已经解决
define('PB_STATUS_CLOSED', 8);     // 求助已经关闭
define('PB_STATUS_AUDIT', 16);      // 已经审核，但未通过

// demand
define('DEMAND_STATUS_QUEUE', 1);   // 请求正在排队
define('DEMAND_STATUS_ACCEPT', 2);  // 达成合作
define('DEMAND_STATUS_DENIED', 4);  // 请求被拒绝
define('DEMAND_STATUS_EXPIRED', 8); // 请求已过期，发生在需求已经被解决

// message
define('DEFAULT_DEMAND_MESSAGE', "您好，我对您的需求十分感兴趣，希望能够帮助您!");

// charge
define('CHARGING_STATUS_DEMAND', 0);  // 尚未达成交易
define('CHARGING_STATUS_DEAL', 1);    // 达成交易，但尚未付款。发生在交易达成后的一段时间内
define('CHARGING_STATUS_SUCCEED', 2); // 达成交易，并付款
define('CHARGING_STATUS_INVALID', 4); // 交易无效

// material
define('MATERIAL_STATUS_APPLY', 1);   // 申请中的资料，暂时未通过审核
define('MATERIAL_STATUS_PUBLISH', 2); // 资料通过了审核，并得到发布
define('MATERIAL_STATUS_DENIED', 3);  // 资料未通过审核

// index page
define('INDEX_PROBLEM_LIST_SIZE', 10); // 首页单页求助数量

// search page
define('SEARCH_PROBLEM_LIST_SIZE', 10); // 求助请求单页数量

define('RESUME_SAVE', 0);     // 仅仅保存资料，未做任何其他处理
define('RESUME_APPLY', 1);    // 申请资格
define('RESUME_ACCEPTED', 2); // 成功获取资格
define('RESUME_DENIED', 3);   // 获取资格失败

define('TRADE_STATUS_UNPAID', 10);       // 已生成订单，但订单尚未付款
define('TRADE_STATUS_PAID_SUCCEED', 11); // 订单付款成功，一般指钱款已经达到了财付通
define('TRADE_STATUS_PAID_FAILED', 12);  // 用户产生了付款的行为，但最终付款失败
define('TRADE_STATUS_DONE', 13);         // 订单交易已经结束
*/
