function Question(host) {
    this.host = host || "http://www.boostme.cn:80";
    this.host = "";
}
Question.prototype = {
    server : function() {
        return this.host;
    },
    fetch_list : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/question/ajax_fetch_list";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    fetch_info : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/question/ajax_fetch_info";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    add : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/question/ajax_add";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    answer : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/question/ajax_answer";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    }
}
Question.prototype.constructor = Question;


function Answer(host) {
    this.host = host || "http://www.boostme.cn:80";
    this.host = "";
}
Answer.prototype = {
    server : function() {
        return this.host;
    },
    fetch_list : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/anwser/ajax_fetch_list";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    fetch_info : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/answer/ajax_fetch_info";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    add_comment : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/answer/ajax_add_comment";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    fetch_comment_list : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/answer/ajax_fetch_comment_list";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    has_support : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/answer/ajax_has_support";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    add_support : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/answer/ajax_add_support";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    get_support : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/answer/ajax_get_support";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    }
}
Answer.prototype.constructor = Answer;


function Service(host) {
    this.host = host || "http://www.boostme.cn:80";
    this.host = "";
}
Service.prototype = {
    server : function() {
        return this.host;
    },
    fetch_list : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/service/ajax_fetch_list";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    fetch_info : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/service/ajax_fetch_info";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    fetch_category : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/service/ajax_fetch_category";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    fetch_comment : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/service/ajax_fetch_comment";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    fetch_user_comment : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/service/ajax_fetch_user_comment";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    add_comment : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/service/ajax_add_comment";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    add_service : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/service/ajax_add";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    close_service : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/service/ajax_close";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    }
}
Service.prototype.constructor = Service;


function Material(host) {
    this.host = host || "http://www.boostme.cn:80";
    this.host = "";
}
Material.prototype = {
    server : function() {
        return this.host;
    },
    fetch_list : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/material/ajax_fetch_list";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    fetch_info : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/material/ajax_fetch_info";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    fetch_category : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/material/ajax_fetch_category";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    search : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/material/ajax_search";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    fetch_comment : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/material/ajax_fetch_comment";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    fetch_user_comment : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/material/ajax_fetch_user_comment";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    add_comment : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/material/ajax_add_comment";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    add : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/material/ajax_add";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    edit : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/material/ajax_edit";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    }
}
Material.prototype.constructor = Service;


function Trade(host) {
    this.host = host || "http://www.boostme.cn:80";
    this.host = "";
}
Trade.prototype = {
    server : function() {
        return this.host;
    },
    fetch_list : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/trade/ajax_fetch_list";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    add_item : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/trade/ajax_add_item";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    remove_item : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/trade/ajax_remove_item";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    fetch_tradeno : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/trade/ajax_fetch_tradeno";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    update_quantity : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/trade/ajax_update_quantity";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    }
}
Trade.prototype.constructor = Trade;


function EBank(host) {
    this.host = host || "http://www.boostme.cn:80";
    this.host = "";
}
EBank.prototype = {
    server : function() {
        return this.host;
    },
    alipay_transfer : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/ebank/ajax_alipay_transfer";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    fetch_account : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/ebank/ajax_fetch_account";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    fetch_history : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/ebank/ajax_fetch_history";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    add_withdraw : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/ebank/ajax_add_withdraw";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    }
}
EBank.prototype.constructor = EBank;


function User(host) {
    this.host = host || "http://www.boostme.cn:80";
    this.host = "";
}
User.prototype = {
    server : function() {
        return this.host;
    },
    login : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/user/ajax_login";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    register : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/user/ajax_register";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    check_username : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/user/ajax_username";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    check_email : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/user/ajax_email";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    check_code : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/user/ajax_check_code";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    add_easy_access : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/user/ajax_add_easy_access";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    remove_easy_access : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/user/ajax_remove_easy_access";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    fetch_easy_access : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/user/ajax_fetch_easy_access";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    update_passwd : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/user/ajax_uppass";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    update_info : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/user/ajax_update_info";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    update_resume : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/user/ajax_update_resume";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    fetch_edu : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/user/ajax_fetch_edu";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    }
}
User.prototype.constructor = User;


function Message(host) {
    this.host = host || "http://www.boostme.cn:80";
    this.host = "";
}
Message.prototype = {
    server : function() {
        return this.host;
    },
    fetch_system : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/message/ajax_fetch_system";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    fetch_personal : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/message/ajax_fetch_personal";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    userlist : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/message/ajax_userlist";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    usernum : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/message/ajax_usernum";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    read_msg : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/message/ajax_read_msg";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    send : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/message/ajax_send";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    remove : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/message/ajax_remove";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    },
    remove_dialog : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/message/ajax_remove_dialog";
        async_request(req_url, "post", req_data_dict, function(response) {
            callback_func(response);
        });
    }
}
Message.prototype.constructor = Message;


function Main(host) {
    this.host = host || "http://www.boostme.cn:80";
    this.host = "";
}
Main.prototype = {
    server : function() {
        return this.host;
    },
    image_size : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/main/ajax_image_size";
        async_request(req_url, "get", req_data_dict, function(response) {
            callback_func(response);
        });
    },
}
Main.prototype.constructor = Main;

