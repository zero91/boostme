<?php

// @brief  get_user_avatar  获取用户头像地址
//
// @param  integer  $uid   用户ID号
// @param  string   $type  图片大小类型，可为"s", "m", "l"
//
// @return  string  头像地址字符串
//
function get_user_avatar($uid, $type = "s") {
    $uid_str = sprintf("%010d", $uid);
    $dir1 = substr($uid_str, 0, 5);
    $dir2 = substr($uid_str, 5, 5);

    $avatar_dir = C('USER_AVATAR_UPLOAD_PATH') . "/$dir1/$dir2/{$type}_{$uid}";
    $avatar_dir = str_replace('//', '/', $avatar_dir);
    foreach (C('USER_AVATAR_IMG_TYPE') as $suffix) {
        if (file_exists(WEB_ROOT . $avatar_dir . $suffix)) {
            return $avatar_dir . $suffix;
        }
    }
    return C('USER_DEFAULT_AVATAR');
}

// @brief  get_avatar_dir  获取用户头像存放目录
// @param  integer  $uid   用户ID号
//
// @return  string  头像存放目录
//
function get_avatar_dir($uid) {
    $uid_str = sprintf("%010d", $uid);
    $dir1 = substr($uid_str, 0, 5);
    $dir2 = substr($uid_str, 5, 5);

    $avatar_dir = WEB_ROOT . C('USER_AVATAR_UPLOAD_PATH') . "/$dir1/$dir2";
    $avatar_dir = str_replace('//', '/', $avatar_dir);
    return $avatar_dir;
}

// @brief  extname  获取文件扩展名
// @param  string  $filename  文件名
//
// @return  string  扩展名称
//
function extname($filename) {
    $pathinfo = pathinfo($filename);
    return strtolower($pathinfo['extension']);
}

// @brief  forcemkdir  创建新目录
// @param  string  $path  待创建的新目录
//
function forcemkdir($path) {
    if (!file_exists($path)) {
        forcemkdir(dirname($path));
        mkdir($path, 0777);
    }
}

// @brief  format_date  日期格式显示
//
// @param  integer  $time      待转化的时间数值
// @param  integer  $type      显示时间的部分，年月日、小时分钟
// @param  boolean  $friendly  是否转化为对用户友好的格式
//
// @return  string  格式化后的时间字符串
//
function format_date($time, $type = 3, $friendly = true) {
    $format[] = $type & 2 ? 'Y-n-j' : '';
    $format[] = $type & 1 ? 'H:i' : '';
    $timestring = gmdate(implode(' ', $format), $time);
    if ($friendly) {
        $time = time() - $time;
        if ($time <= 24 * 3600) {
            if ($time > 3600) {
                $timestring = intval($time / 3600) . '小时前';
            } elseif ($time > 60) {
                $timestring = intval($time / 60) . '分钟前';
            } elseif ($time > 0) {
                $timestring = $time . '秒前';
            } else {
                $timestring = '现在前';
            }
        }
    }
    return $timestring;
}

// @brief  check_verify  检测验证码
//
// @param  string   $code  验证码
// @param  integer  $id    验证码ID
//
// @return boolean     检测结果
//
function check_verify($code, $id = 1, $reset = true) {
    $config = array(
        //"fontSize" => 36,
        "reset" => $reset
    );
    $verify = new \Think\Verify($config);
    return $verify->check($code, $id);
}
