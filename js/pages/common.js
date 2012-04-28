$(function() {
    $( 'button, input:submit' ).button();
    $("#links .btn").button({
        icons:{
            primary: "ui-icon-triangle-1-e"
        }
    });
    $("#links a.head-links").click(function(event){
        event.preventDefault();
    });
    $("#links .btn").click(function(){
        normalize($(this).attr("value"));
    });
    $("#accord").accordion({active:".$active.",autoHeight: false,icons:{ 'header': 'ui-icon-plus', 'headerSelected': 'ui-icon-minus' }});


});

function inform(container,message){
    container.hide(0);
    container.html(message);
    container.show('drop',{ direction: "right", distance: "-50px" },500);
    window.setTimeout(function(){
        container.hide('drop',{ direction: "right", distance: "100px" },1000);
    },4000);
}

function normalize(href){
    if($("#main").width() != 960){
        $('#main').animate({width:'960px'},300,'easeInOutCubic');
        setTimeout(function () {
            window.location = href;
        }, 310);
    }else{
        window.location = href;
    }
    console.log(href);
}

function widen(){
    if($(window).width() > 1060){
        var resize = $(window).width() - 100;
        window.setTimeout(function(){
            if($(window).width() > 1060){
                $('#main').animate({width:resize+'px'},2000,'easeInOutCubic');
            }
        },300);
    }
}