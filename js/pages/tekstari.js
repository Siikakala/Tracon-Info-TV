function send(form){
    var container = $("#feedback");
    fetch = baseurl+'ajax/tekstari_send/'
    $.post(fetch,$("#"+form).serialize(),function(data) {
        inform(container,data.ret);
    },"json");
    return false;
}
$(function(){
    $("#tekstari-accord").accordion();
});

$("form").submit(function(e) {
    e.preventDefault();
});