function Problem(site_url) {
    this.site_url = site_url;
}

Problem.prototype.get_site_url = function() {
    return this.site_url;
}

Problem.prototype.get_problem_list = function(req_data_dict) {
    var req_url = this.site_url + "problem/fetch_list";
    var problem_list = {};

    $.ajax({
        type : "post",  
        url : req_url,
        data : req_data_dict,
        dataType: "json",
        async : false, // 同步获取
        success : function(ret_problem_list) {  
            problem_list = ret_problem_list;;
        }
    });
    return problem_list;
}

Problem.prototype.add_problem = function(req_data_dict, callback_func) {
    var category = req_data_dict['category'];

    $.ajax({
        url: '/problem/register',
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

Problem.prototype.create_div = function(problem) {
    var phone = problem.phone;
    if (!phone) {
        phone = "";
    }

    var wechat = problem.wechat;
    if (!wechat) {
        wechat = "";
    }

    var qq = problem.qq;
    if (!qq) {
        qq = "";
    }

    var html = '<div class="col-sm-6 col-md-4">';
    html    += '<a class="job-item-wrap" target="_blank">';
    html    += '    <div class="job-item">';
    html    += '        <div class="job-source light-green">';
    html    += '            <img class="img-responsive" src="' + problem.author_avatar + '">';
    html    += '        </div>';
    html    += '        <div class="job-company">' + problem.author + '</div>';
    html    += '        <div class="job-title">' + problem.title + '</div>';
    html    += '        <div class="job-comments">';
    if (phone) {
        html    += '            <p>手机：' + phone + '</p>';
    }

    if (wechat) {
        html    += '            <p>微信：' + wechat + '</p>';
    }

    if (qq) {
        html    += '            <p>QQ：  ' + qq + '</p>';
    }

    html    += '        </div>';
    html    += '        <div class="job-meta">';
    html    += '            <span class="job-location">[回报：' + problem.price + ']</span><span class="job-publish-time">' + problem.format_time + '</span>';
    html    += '        </div>';
    html    += '    </div>';
    html    += '</a>';
    html    += '</div>';
    return html;
}
