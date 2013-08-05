function send(form){
    var container = $("#feedback");
    fetch = baseurl+'ajax/tekstari_send/'
    $.post(fetch,$("#"+form).serialize(),function(data) {
        inform(container,data.ret);
    },"json");
    return false;
}
$(function(){
    $("#tekstari-accord").accordion({heightStyle: 'content'});
    $("#inbox-accord").accordion({heightStyle: 'content', active: "false", collapsible: "true"});
    $("#valitystieto-accord").accordion({heightStyle: 'content', active: "false", collapsible: "true"});

    update_balance();

    var uploader = new qq.FileUploader({
            element: document.getElementById('fileupload'),
            action: baseurl+'ajax/tekstari_file/',
            onComplete: function(id, fileName, responseJSON){
                if(responseJSON.timeout != undefined){
                    inform($("#feedback"),responseJSON.ret,responseJSON.timeout);
                }else{
                    inform($("#feedback"),responseJSON.ret);
                }
            },
        });
});

function update_balance(){
    var container = $("#saldo");
    fetch = baseurl+"ajax/tekstari_balance/";
    $.getJSON(fetch,function(data){
        container.html(data.ret);
    });
    window.setTimeout(function(){
        update_balance();
    },120000);
}

$("form").submit(function(e) {
    e.preventDefault();
});

