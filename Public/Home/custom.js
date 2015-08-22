// 这是一个管理着 视图/控制/模型 的全局类
var App = {
    Models: {},
    Views: {},
    Controllers: {},
    Collections: {},
    initialize: function() {
        new App.Controllers.Routes();
        Backbone.history.start() // 要驱动所有的Backbone程序，Backbone.history.start()是必须的。
    }
};
App.Models.Hello = Backbone.Model.extend({
    url: function() {
        return '/api.php'; // 获得数据的后台地址。
    },
    initialize: function() {
        this.set({'message':'hello world'}); // 前端定义一个message字段，name字段由后端提供。
    }
});
App.Views.Hello = Backbone.View.extend({
    el: $("body"),
    template: $("#hello-container-template").html(),
    initialize: function(options){
        this.options = options;
        this.bind('change', this.render);
        this.model = this.options.model;
    },
    render: function(){ // render方法，目标只有两个：填充this.el，返回this以便链式操作。
        $(this.el).html(_.template($(this.el).template, this.model.toJSON()));
        return this
    }
});
App.Controllers.Routes = Backbone.Collections.extend({
    routes: {
        "!/hello" : "hello",//使用#!/hello驱动路由
    },
    hello : function() {
        //新建一个模型，模型向后端请求更新内容成功后根据模型渲染新页面
        var helloModel = new App.Models.Hello;
        helloModel.fetch({
            success: function(model){
                var helloView = new App.Views.Hello({model: model});
                helloView.trigger('change');
            }
        })
    }
});
App.initialize();
