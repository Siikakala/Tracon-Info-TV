function check(show,id){
    if(show == 1){
        $('#' + id + '-stream').show("medium");
        $('#' + id + '-dia').hide("medium");
        $('#' + id + '-inst').hide("medium");
    }else if(show == 2){
        $('#' + id + '-stream').hide("medium");
        $('#' + id + '-dia').show("medium");
        $('#' + id + '-inst').hide("medium");
    }else{
        $('#' + id + '-stream').hide("medium");
        $('#' + id + '-dia').hide("medium");
        $('#' + id + '-inst').show("medium");
    }
}


function dele(id){
    var sure = confirm("Oletko varma että haluat poistaa tämän frontendin? Se nollaa frontendin asetukset ja nimen.");
    var container = $("#feedback");
    if(sure){
        fetch = baseurl+'ajax/fronted_delete/' + id;
        $.getJSON(fetch, function(data) {
            if(data.ret == true){
                $("."+id).remove();
                inform(container,"Frontend poistettu. Muista tallentaa muutoksesi!");
            }else{
                inform(container,data.ret);
            }
        });

    }
}

function save(){
    var container = $("#feedback");
    container.hide(0);
    fetch = baseurl+'ajax/frontend_save/'
    $.post(fetch,$("#form").serialize(),function(data) {
        $.each(data,function(key,val){
            inform(container,val);
        });
    },"json");
    window.setTimeout(function(){
        $("#formidata").hide("explode",{pieces:8},1000);
    },2500);
    window.setTimeout(function(){
        refresh_data();
    },3470);
    return false;
}

function refresh_data(post){
    var container = $("#formidata");
    fetch = baseurl+'ajax/frontend_load/'
    $.getJSON(fetch,function(data) {
        container.html(data.ret);
    });
    window.setTimeout(function(){
        container.show("clip",1000);
    },100);
}

$(".del").on({
    mouseenter: function() {
        $(this).addClass('hover');
    },
    mouseleave: function(){
        $(this).removeClass('hover');
    }
});

$(function() {
    if(show){
        $("#show_stream").show("medium");
    }
});

function check_show(show){
    if(show == 1){
        $("#show_stream").show("medium");
    }else{
        $("#show_stream").hide("medium");
    }
}

function show_save(){
    var container = $("#show_feed");
    container.hide(0);
    var nayta = $("#show_tv").val();
    var stream = $("#show_stream").val();
    fetch = baseurl+'ajax/tv/'
    $.post(fetch, { "nayta": nayta, "stream": stream }, function(data){
        if(data.ret == true){
            container.html("Muutettu.");
            $("#show_stream").removeClass("new");
            $("#show_tv").removeClass("new");
        }else{
            container.html(data.ret);
        }
    },"json");
    window.setTimeout(function(){
        container.show('drop', { direction:"right", distance: "-50px" },600);
        window.setTimeout(function(){
            container.hide('drop', { direction:"right", distance: "80px" },1400);
        },1000);
    },200);
}