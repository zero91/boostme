function Trade(site_url) {
    this.site_url = site_url;
}

Trade.prototype.get_site_url = function() {
    return this.site_url;
}

Trade.prototype.add_service = function(req_data_dict, callback_func) {
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

Trade.prototype.delete_goods = function(trade_no, target_id, type, callback_func) {
    $.ajax({
        type : "post",  
        url  : "/trade/delete_goods",
        data : {trade_no : trade_no, target_id : target_id, type : type},
        dataType: "json",
        async : true,
        success : function(data) {  
            callback_func(data);
        }
    });
}
