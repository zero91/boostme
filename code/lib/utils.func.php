<?php

// 翻页函数：获取翻页的html片段
function split_page($tot_num, $page_size, $cur_page, $operation, $ajax = 0) {
    $html = "";
    $req_url = SITE_URL . "$operation";
    if (strpos($req_url, "?")) {
        $req_url .= "&";
    } else {
        $req_url .= "?";
    }

    if ($tot_num > $page_size) {
        $max_show_page = 8;
        $offset = 2;

        $tot_page_num = @ceil($tot_num / $page_size);
        $from = max($cur_page - $offset, 1);

        $to = min($from + $max_show_page - 1, $tot_page_num);
        $from = max(1, $to - $max_show_page + 1);

        if ($ajax) {
            if ($cur_page < $tot_page_num) {
                $html = "<a href=\"{$req_url}page=" . ($cur_page + 1) . '">查看更多</a>';
            }

        } else {
            if ($from > 1) {
                $html .= "<a href=\"{$req_url}page=1\">首页</a>\n";
            }
            if ($cur_page > 1) {
                $html .= "<a href=\"{$req_url}page=" . ($cur_page - 1) . "\">上一页</a>\n";
            }
            for ($i = $from; $i <= $to; ++$i) {
                if ($i == $cur_page) {
                    $html .= "<strong>$i</strong>\n";
                } else {
                    $html .= "<a href=\"{$req_url}page=$i\">$i</a>\n";
                }
            }
            if ($cur_page < $tot_page_num) {
                $html .= "<a href=\"{$req_url}page=" . ($cur_page + 1) . "\">下一页</a>\n";
            }
            if ($to < $tot_page_num) {
                $html .= "<a href=\"{$req_url}page=" . $tot_page_num . "\">最后一页</a>\n";
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

?>
