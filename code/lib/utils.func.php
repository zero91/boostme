<?php

// 翻页函数：获取翻页的html片段
function split_page($tot_num, $page_size, $cur_page, $operation, $ajax = 0) {
    $html = "";
    if ($tot_num > $page_size) {
        $max_show_page = 8;
        $offset = 2;

        $tot_page_num = @ceil($tot_num / $page_size);
        $from = max($cur_page - $offset, 1);

        $to = min($from + $max_show_page - 1, $tot_page_num);
        $from = max(1, $to - $max_show_page + 1);

        if ($ajax) {
            if ($from > 1) {
                $html .= sprintf("<a onclick=\"$operation\" href=\"javascript:void(0)\">首页</a>\n", 1);
            }
            if ($cur_page > 1) {
                $html .= sprintf("<a onclick=\"$operation\" href=\"javascript:void(0)\">上一页</a>\n", $cur_page - 1);
            }
            for ($i = $from; $i <= $to; ++$i) {
                if ($i == $cur_page) {
                    $html .= "<strong>$i</strong>\n";
                } else {
                    $html .= sprintf("<a onclick=\"$operation\" href=\"javascript:void(0)\">$i</a>\n", $i);
                }
            }
            if ($cur_page < $tot_page_num) {
                $html .= sprintf("<a onclick=\"$operation\" href=\"javascript:void(0)\">下一页</a>\n", $cur_page + 1);
            }
            if ($to < $tot_page_num) {
                $html .= sprintf("<a onclick=\"$operation\" href=\"javascript:void(0)\">最后一页</a>\n", $tot_page_num);
            }

        } else {
            if ($from > 1) {
                $html .= sprintf("<a href=\"{$operation}\">首页</a>\n", 1);
            }
            if ($cur_page > 1) {
                $html .= sprintf("<a href=\"{$operation}\">上一页</a>\n", ($cur_page - 1));
            }
            for ($i = $from; $i <= $to; ++$i) {
                if ($i == $cur_page) {
                    $html .= "<strong>$i</strong>\n";
                } else {
                    $html .= sprintf("<a href=\"{$operation}\">$i</a>\n", $i);
                }
            }
            if ($cur_page < $tot_page_num) {
                $html .= sprintf("<a href=\"{$operation}\">下一页</a>\n", $cur_page + 1);
            }
            if ($to < $tot_page_num) {
                $html .= sprintf("<a href=\"{$operation}\">最后一页</a>\n", $tot_page_num);
            }
        }
    }
    return $html;
}

// 生成唯一的trade ID号
function generate_tradeno($token) {
    $time = time();
    $trade_no = substr("{$time}{$token}" . random(32), 0, 32); 
    return $trade_no;
}

// 检查邮件地址是否合法
function check_email_format($email) {
    return preg_match("/^[a-z'0-9]+([._-][a-z'0-9]+)*@([a-z0-9]+([._-][a-z0-9]+))+$/", $email);
}

?>
