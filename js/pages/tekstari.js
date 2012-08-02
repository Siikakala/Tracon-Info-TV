function send(form){
    var container = $("#feedback");
    fetch = baseurl+'ajax/tekstari_send/'
    $.post(fetch,$("#"+form).serialize(),function(data) {
        inform(container,data.ret);
    },"json");
    return false;
}
$(function(){
    $("#tekstari-accord").accordion({autoHeight: false});

    var uploader = new qq.FileUploader({
            element: document.getElementById('fileupload'),
            action: baseurl+'ajax/tekstari_file/',
            onComplete: function(id, fileName, responseJSON){
                inform($("#feedback"),responseJSON.ret);
            },
        });
});

$("form").submit(function(e) {
    e.preventDefault();
});

