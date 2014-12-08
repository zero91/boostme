function Material(site_url) {
    this.site_url = site_url;
}

Material.prototype.get_site_url = function() {
    return this.site_url;
}

Material.prototype.get_material_list = function(req_data_dict) {
    var req_url = this.site_url + "material/fetch_list";
    var material_list = {};

    $.ajax({
        type : "post",  
        url : req_url,
        data : req_data_dict,
        dataType: "json",
        async : false, // 同步获取
        success : function(ret_material_list) {  
            material_list = ret_material_list;
        }
    });
    return material_list;
}

Material.prototype.create_div = function(material) {
    var html = '<div class="col-sm-6 col-md-4">';
    html    += '<a href="' + this.site_url + 'material/view/' + material.id + '" class="job-item-wrap" target="_blank">';
    html    += '    <div class="job-item">';
    html    += '        <div class="job-source light-green">';
    html    += '            <img class="img-responsive" src="' + material.picture + '">';
    html    += '        </div>';
    html    += '        <div class="job-company">' + material.username + '</div>';
    html    += '        <div class="job-title">' + material.title + '</div>';
    html    += '        <div class="job-salary">价格：¥' +  parseFloat(material.price,2) + '</div>';
    html    += '        <div class="job-comments">';
    html    += '            <p>';
    html    += '                <span class="label label-default">已售' + material.sold_num + '份</span>';
    html    += '                <span class="label label-default">浏览' + material.view_num + '次</span>';
    html    += '                <span class="label label-default">评论' + material.comment_num + '条</span>';
    html    += '                <span class="label label-default">平均' + parseFloat(material.avg_score).toFixed(2) + '分</span>';
    html    += '            </p>';
    html    += '        </div>';
    html    += '        <div class="job-meta">';
    html    += '            <span class="job-publish-time">' + material.format_time + '</span>';
    html    += '        </div>';
    html    += '    </div>';
    html    += '</a>';
    html    += '</div>';
    return html;
}
