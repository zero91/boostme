$(function() {
    var trade = new Trade();
    $("#buy_service_btn").click(function() {
        buy_item(g_service_id, 2, 1);
    });

    $("#buy_material_btn").click(function() {
        buy_item(g_material_id, 1, 1);
    });

    $(".item_remove").click(function() {
        if (confirm('确认删除?') === false) {
            return false;
        }
        var item_target = $(this).parents(".bm_trade_table_row");
        var item_id = item_target.attr("id");
        var target_id = item_id.split("_")[2];
        var type = item_id.split("_")[3];

        trade.remove_item({"trade_no" : g_trade_no,
                           "target_id" : target_id,
                           "type" : type}, function(response) {
            var error_dict = {
                101 : "尚未登录",
                102 : "无效参数",
                103 : "用户无权操作",
                104 : "删除失败"
            };
            if (response.success) {
                item_target.remove();
                calc_trade_price();
            } else {
                errno_alert(response.error, error_dict);
            }
        });
    });

    $(".bm_quantity_op_btn").click(function() {
        var operation = $(this).html();
        var quantity = parseInt($(this).parent().find("input.bm_trade_quantity").val());

        var item_id = $(this).parents(".bm_trade_table_row").attr("id");

        var target_id = item_id.split("_")[2];
        var type = item_id.split("_")[3];
        var target_quantity = 0;

        if (operation == "-") {
            if (quantity <= 1) {
                return false;
            }
            target_quantity = quantity - 1;
        } else if (operation == "+") {
            target_quantity = quantity + 1;
        } else {
            alert("非法操作");
            return false;
        }
        var target_elem = $(this).parent().find("input.bm_trade_quantity");
        trade.update_quantity({"trade_no" : g_trade_no, 
                               "target_id" : target_id,
                               "type" : type,
                               "quantity" : target_quantity}, function(response) {
            var error_dict = {
                101 : "尚未登录",
                102 : "无效参数",
                103 : "用户无权操作",
                104 : "更新失败"
            };
            if (response.success) {
                target_elem.val(target_quantity);
                calc_trade_price();
            } else {
                errno_alert(response.error, error_dict);
            }
        });
    });

    calc_trade_price();
});

function calc_trade_price() {
    var trade_cost = 0;
    $("tr[id^='item_info_']").each(function() {
        var price = parseFloat($(this).find(".item_price").html());
        var quantity = parseInt($(this).find("input.bm_trade_quantity").val());
        var item_cost = price * quantity;
        trade_cost += item_cost;
        $(this).find(".bm_trade_summary").html(item_cost.toFixed(2));
    });
    $("#total_price").html("¥&nbsp;" + trade_cost.toFixed(2));
}

function buy_item(target_id, type, quantity) {
    var trade = new Trade();
    trade.add_item({"target_id" : target_id,
                    "type" : type, "quantity" : quantity}, function(response) {
        var error_dict = {
            101 : "尚未登录",
            102 : "无效参数",
            103 : "购买失败"
        };
        if (response.success) {
            window.location.href = g_site_url + "/trade/view?trade_no=" + response.trade_no;
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}
