$(function() {
    $("#query_submit_btn").click(function() {
        var query = $("#search_query").val();
        if ($.trim(query) == "") {
            query = "";
        }
        query_search(query, 1);
    });

    $("#search_query").keydown(function(e) {
        var ev = document.all ? window.event : e;
        if (ev.which == 13) {
            $("#query_submit_btn").click();
            return false;
        }
    });

    $("#search_query").focus();
});

// 搜索资料
function query_search(query, page) {
    var material = new Material();

    material.search({"query" : query, "page" : page}, function(response) {
        if (response.success) {
            var template_content = _.template($("#material_search_list_template").html());
            $("#query_result").html(template_content({material_list : response.material_list,
                                                      tot_num : response.tot_num,
                                                      departstr : response.departstr}));
        }
    });
}

function request_show_data(req_data_dict) {
    var material = new Material();
    material.fetch_list(req_data_dict, function(response) {
        var template_content = _.template($("#material_category_list_template").html());
        $("#board_show").append(template_content({"material_list" : response["material_list"]}));

        if (response["material_list"].length == 0) {
            $("#more").hide();
        } else {
            $("#more").show();
        }
    });
}
