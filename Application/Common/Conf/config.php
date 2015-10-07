<?php
return array(
    'DB_TYPE'            =>    'mysql',
    'DB_HOST'            =>    'localhost',
    'DB_NAME'            =>    'development', //需要新建一个数据库！名字叫
    'DB_USER'            =>    'test', //数据库用户名    
    'DB_PWD'             =>    'test', //数据库登录密码
    'DB_PORT'            =>    '3306',
    'DB_PREFIX'          =>    'bm_',        //数据库表名前缀
    'SHOW_PAGE_TRACE'    =>    true,

    'USER_AUTH_KEY'      =>    'authId',
    'USER_ALLOW_REGISTER' =>   true,
    'USER_MAX_CACHE'     =>    1000, //最大缓存用户数

    'USER_AVATAR_IMG_TYPE'    => array(".jpg", ".jpeg", ".gif", ".png"), // 头像允许的图片类型
    'USER_AVATAR_UPLOAD_PATH' => '/Uploads/Avatar/',      // 头像上传存放地址，使用服务器绝对地址
    'USER_DEFAULT_AVATAR'     => '/Public/Common/Images/avatar.png', // 默认头像，使用服务器绝对地址
    'PRODUCT_NAME'       =>    '帮考研', // 产品显示名称
);
?>
