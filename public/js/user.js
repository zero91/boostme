function User(site_url) {
    this.site_url = site_url;
}

User.prototype.get_site_url = function() {
    return this.site_url;
}

User.prototype.login = function(req_data_dict) {
    var req_url = this.site_url + "user/ajax_login";

    var result;
    $.ajax({
        type : "post",  
        url : req_url,
        data : req_data_dict,
        dataType: "json",
        async : false, // 同步获取
        success : function(ret) {  
            result = ret;
        }
    });
    return result;
}

User.prototype.get_service_list = function(req_data_dict) {
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

User.prototype.add_service = function(req_data_dict, callback_func) {
    var category = req_data_dict['category'];

    $.ajax({
        url: '/service/register',
        type: 'POST',
        data: req_data_dict,
        dataType: 'json',
        timeout: 8000,
        error: function(){
            alert('提交超时！');
        },
        success: function(data){
            callback_func(data);
        }
    });
}
