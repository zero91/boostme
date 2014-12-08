function Category(site_url) {
    this.site_url = site_url;
}

Category.prototype.get_site_url = function() {
    return this.site_url;
}

Category.prototype.get_service_list = function(req_data_dict) {
    var req_url = this.site_url + "service/fetch_list";
    var service_list = {};

    /* // 异步
    $.post(req_url, req_data_dict, function(ret_service_list) {
        service_list = ret_service_list;
    }); */

    $.ajax({
        type : "post",  
        url : req_url,
        data : req_data_dict,
        dataType: "json",
        async : false, // 同步获取
        success : function(ret_service_list) {  
            service_list = ret_service_list;
        }
    });
    return service_list;
}

Category.prototype.create_div = function() {
}
