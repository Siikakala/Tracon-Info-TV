$(document).ready(function() {
    var resize = $(window).width() - 100;
    widen();
    ref();

    $('#text').append('<div id="templates"></div>');
    $("#templates").hide();
    $("#templates").load(baseurl+"dashboard/templates.html", initDashboard);


    function initDashboard() {
        var dashboard = $("#dashboard").dashboard({
            layoutClass:'layout',
            debuglevel:5,
            json_data : {
              url: baseurl+"dashboard/jsonfeed/mywidgets.json"
            },
            addWidgetSettings: {
              widgetDirectoryUrl:baseurl+"dashboard/jsonfeed/widgetcategories.json"
            },
            layouts :
              [
                {   title: "Layout1",
                    id: "layout1",
                    image: "layouts/layout5.png",
                    html: '<div class="layout layout-aa"><div class="column first column-first"></div><div class="column second column-second"></div></div>',
                    classname: 'layout-aa'
                }
              ]
        });
        dashboard.element.live('dashboardAddWidget',function(e, obj){
            var widget = obj.widget;

            dashboard.addWidget({
              "id":startId++,
              "title":widget.title,
              "url":widget.url,
              "metadata":widget.metadata
            }, dashboard.element.find('.column:first'));
        });
        dashboard.init();
    }
});

function ref(){
    refresh();
    window.setTimeout(function(){
        ref();
    },5000);
};

function refresh(){
    var container = $("#table");
    fetch = baseurl+'ajax/todo_refresh/'
    $.getJSON(fetch,function(data) {
        container.html(data.ret);
    });
};