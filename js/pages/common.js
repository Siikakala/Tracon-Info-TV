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
    update_load();
    update_clock();
});

/**
 *
 * @access public
 * @return void
 **/
function set_instance(instance){
    fetch = baseurl+'ajax/instance/'
    $.post(fetch,{"instance":instance},function(data) {
        if(data.ret == true){}
        else{
            alert("Instanssin vaihto epäonnistui!");
        }
    },"json");
}

/**
 *
 * @access public
 * @return void
 **/
function update_load(){
    if(usrlvl >= 3){
        var container = $("#show");
        fetch = baseurl+'ajax/loads/'
        $.getJSON(fetch,function(data) {
            container.html("Serverin loadit: "+data.ret);
        });
        window.setTimeout(function(){
            update_load();
        },2500);
    }
}

function update_clock(){
    var kello = $("#kello");
    var Digital = new Date();
    var hours = Digital.getHours();
    var minutes = Digital.getMinutes();
    if(minutes < 10){
        minutes = "0" + minutes;
    }
    if(hours < 10){
        hours = "0" + hours;
    }
    kello.html(hours + ":" + minutes);
    window.setTimeout(function(){
        update_clock();
    },1000);
}

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