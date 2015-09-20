<?php

// @brief  get_user_avatar  获取用户头像地址
//
// @param  integer  $uid   用户ID号
// @param  string   $type  图片大小类型，可为"small", "medium", "large"
//
// @return  string  头像地址字符串
//
function get_user_avatar($uid, $type = "small") {
    $uid = sprintf("%010d", $uid);
    $dir1 = substr($uid, 0, 3);
    $dir2 = substr($uid, 3, 3);
    $dir3 = substr($uid, 6, 2);

    $avatar_dir = C('USER_AVATAR_UPLOAD_PATH') . "$dir1/$dir2/$dir3/{$type}_{$uid}";
    $image_suffix_arr = array(".jpg", ".jepg", ".gif", ".png");

    foreach ($image_suffix_arr as $suffix) {
        if (file_exists(WEB_ROOT . $avatar_dir . $suffix)) {
            return $avatar_dir . $suffix;
        }
    }
    return C('USER_DEFAULT_AVATAR');
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


/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function check_verify($code, $id = 1){
    return true;
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

/**
 * 获取列表总行数
 * @param  string  $category 分类ID
 * @param  integer $status   数据状态
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_list_count($category, $status = 1){
    static $count;
    if(!isset($count[$category])){
        $count[$category] = D('Document')->listCount($category, $status);
    }
    return $count[$category];
}

/**
 * 获取段落总数
 * @param  string $id 文档ID
 * @return integer    段落总数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_part_count($id){
    static $count;
    if(!isset($count[$id])){
        $count[$id] = D('Document')->partCount($id);
    }
    return $count[$id];
}

/**
 * 获取导航URL
 * @param  string $url 导航URL
 * @return string      解析或的url
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_nav_url($url){
    switch ($url) {
        case 'http://' === substr($url, 0, 7):
        case '#' === substr($url, 0, 1):
            break;        
        default:
            $url = U($url);
            break;
    }
    return $url;
}
