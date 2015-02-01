function Service(site_url) {
    this.site_url = site_url;
}

Service.prototype.get_site_url = function() {
    return this.site_url;
}

Service.prototype.get_service_list = function(req_data_dict) {
    var req_url = this.site_url + "service/ajax_fetch_list";
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

Service.prototype.add_service = function(req_data_dict, callback_func) {
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

Service.prototype.create_div = function(service) {
    var html = '<div class="col-sm-6 col-md-4">';
    html    += '<a href="' + this.site_url + 'service/view/' + service.id + '" class="job-item-wrap" target="_blank">';
    html    += '    <div class="job-item">';
    html    += '        <div class="job-source light-green">';
    html    += '            <img class="img-responsive" src="' + service.picture + '">';
    html    += '        </div>';
    html    += '        <div class="job-company">' + service.username + '</div>';
    html    += '        <div class="job-title">' + service.profile + '</div>';
    html    += '        <div class="job-salary">价格：¥' +  parseFloat(service.price,2) + '</div>';
    html    += '        <div class="job-comments">';
    html    += '            <p>';
    html    += '                <span class="label label-default">服务' + service.service_num + '人</span>';
    html    += '                <span class="label label-default">浏览' + service.view_num + '次</span>';
    html    += '                <span class="label label-default">评论' + service.comment_num + '条</span>';
    html    += '                <span class="label label-default">平均' + parseFloat(service.avg_score).toFixed(2) + '分</span>';
    html    += '            </p>';
    html    += '        </div>';
    html    += '        <div class="job-meta">';
    html    += '            <span class="job-publish-time">' + service.format_time + '</span>';
    html    += '        </div>';
    html    += '    </div>';
    html    += '</a>';
    html    += '</div>';
    return html;
}
