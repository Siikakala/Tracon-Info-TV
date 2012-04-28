$(function() {
    $("#feedback").html("");
    $("#login").submit(function(e){
        e.preventDefault();
    });
});
function login(){
	var user = $("#user").val();
	var pass = MD5($("#pass").val());
	fetch = baseurl+'ajax/login/'
    $.post(fetch,{'user':user,'pass':pass},function(data) {
        if(data.ret == true){
            location.reload(true);
        }else{
            inform($("#feedback"),data.ret);
        }
    },"json");
}