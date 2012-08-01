function send(){
    var container = $("#feedback");
    fetch = baseurl+'ajax/tekstari_send/'
    $.post(fetch,$("#tekstari").serialize(),function(data) {
        inform(container,data.ret);
    },"json");
    return false;
}

$("form").submit(function(e) {
    e.preventDefault();
});