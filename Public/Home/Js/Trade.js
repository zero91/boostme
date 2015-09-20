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
        var item_target_id = item_target.attr("id");
        var item_id = item_target_id.split("_")[2];
        var item_type = item_target_id.split("_")[3];

        trade.remove_item({"trade_id" : g_trade_id,
                           "item_id" : item_id,
                           "item_type" : item_type}, function(response) {
            var error_dict = {
                101 : "用户尚未登录",
                102 : "订单号不存在",
                103 : "用户无权操作该订单",
                104 : "订单号中不存在该项产品",
                105 : "订单已锁定，不能更改"
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

        var delta = 0;
        if (operation == "-") {
            if (quantity <= 1) {
                return false;
            }
            target_quantity = quantity - 1;
            delta = -1;
        } else if (operation == "+") {
            target_quantity = quantity + 1;
            delta = 1;
        } else {
            alert("非法操作");
            return false;
        }
        var target_elem = $(this).parent().find("input.bm_trade_quantity");
        trade.add_item({"item_id" : target_id,
                        "item_type" : type,
                        "quantity" : delta}, function(response) {
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
        var price_info = $(this).find(".item_price").html();
        var price = parseFloat(price_info.split("/")[0].substr(1));
        var quantity = parseInt($(this).find("input.bm_trade_quantity").val());
        var item_cost = price * quantity;
        trade_cost += item_cost;
        $(this).find(".bm_trade_summary").html(item_cost.toFixed(2));
    });
    $("#total_price").html("¥&nbsp;" + trade_cost.toFixed(2));
}

function buy_item(item_id, item_type, quantity) {
    var trade = new Trade();
    trade.add_item({"item_id" : item_id,
                    "item_type" : item_type, "quantity" : quantity}, function(response) {
        var error_dict = {
            101 : "尚未登录",
            102 : "无效参数",
            103 : "添加失败",
            104 : "生成订单失败",
            105 : "无权从左该订单号",
            106 : "订单已锁定，不能更改",
            107 : "操作失败",
            108 : "购买物品数量必须大于0"
        };
        if (response.success) {
            window.location.href = g_site_url + "/Trade/view?id=" + response.trade_id;
        } else {
            errno_alert(response.error, error_dict);
        }
    });
}
