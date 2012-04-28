var id = 500;

function load(id){
    var container = $("#stream_content");

    container.html(\'<a href="' + $("#"+id).val() + '" style="display:block;width:700px;height:390px;" id="player"></a><br/><a href="javascript:;" onclick="$f().stop(); window.setTimeout($(this).parent().hide(\\'blind\\',3000),800);">[Sulje]</a>');

    window.setTimeout(function(){
        flowplayer("player", baseurl+"flowplayer/flowplayer.swf", {
            clip : {
                autoPlay: true,
                autoBuffering: true,
                live:true,
                provider:'influxis'
            },
            plugins:{
                influxis:{
                    url:baseurl+'flowplayer/flowplayer.rtmp-3.2.3.swf',
                    netConnectionUrl:$("#"+id).val()
                },
                controls: null
            }
        });
    },700);
    container.show(700);

}

function dele(id){
    var sure = confirm("Oletko varma että haluat poistaa tämän streamin?");
    var container = $("#feedback");
    if(sure){
        fetch = baseurl+'ajax/stream_delete/' + id;
        $.getJSON(fetch, function(data) {
            if(data.ret == true){
                $("."+id).remove();
                inform(container,"Rivi poistettu. Muista tallentaa muutoksesi!");
            }else{
                inform(container,data.ret);
            }
        });

    }
}

function addrow(){
    $('#streamit').append('<tr class="new"><td><input type="text" name="ident-' + id + '" value="" size="15" /></td><td><input type="text" name="url-' + id + '" value="" size="35" id="' + id + '" /></td><td><input name="jarkka-' + id + '" size="1" type="text"></td><td style="border:0px; border-bottom-style: none; padding: 0px; width:2px;"><a href="javascript:;" class="del" >X</a></td><td style="border:0px; border-bottom-style: none; padding: 0px;"><a href="javascript:;" onclick="load('+id+');">&nbsp;Esikatsele</a></td></tr>');
    id = id + 1;
}

function save(){
    var container = $("#feedback");
    container.hide(0);
    fetch = baseurl+'ajax/stream_save/'
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
    fetch = baseurl+'ajax/stream_load/'
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
    },
    click: function(){
        if(!$(this).hasClass("ignore")){
            $(this).parent().parent().remove();
            inform($("#feedback"),"Rivi poistettu. Muista tallentaa muutoksesi!");
        }
    }
});