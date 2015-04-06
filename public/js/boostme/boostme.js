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
    add_service : function(req_data_dict, callback_func) {
        var req_url = this.server() + "/service/ajax_add";
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
    }
}
Material.prototype.constructor = Service;


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
