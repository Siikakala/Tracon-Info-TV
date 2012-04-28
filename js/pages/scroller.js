var id = 500;

function addrow(){
    $('#scroller').append('<tr class="new"><td><input type="text" name="pos-' + id + '" value="" size="1" /></td><td><input type="text" name="text-' + id + '" value="" size="45" /></td><td><input name="hidden-' + id + '" value="1" type="checkbox"></td><td style="border:0px; border-bottom-style: none; padding: 0px; width:2px;"><a href="javascript:;" class="del" >X</a></td></tr>');
    id = id + 1;
}

$("#form").submit(function(e) {
    e.preventDefault();
});

function save(){
    var container = $("#feedback");
    container.hide(0);
    fetch = baseurl+'ajax/scroller_save/'
    $.post(fetch,$("#form").serialize(),function(data) {
        $.each(data,function(key,val){
            inform(container,val)
        });
    },"json");
    window.setTimeout(function(){
        $("#formidata").hide("explode",{pieces:8},1000);
    },2500);
    window.setTimeout(function(){
        refresh_data();
    },3500);
    return false;
}

function refresh_data(){
    var container = $("#formidata");
    fetch = baseurl+'ajax/scroller_load/'
    $.getJSON(fetch, function(data) {
        container.html(data.data);
    });
    container.show("clip",1000);
}

function dele(row){
    var container = $("#feedback");
    container.hide(0);
    var sure = confirm("Oletko varma, että haluat poistaa tämän scrollerinpalan?")
    if(sure){
        fetch = baseurl+'ajax/scroller_delete/' + row;
        $.getJSON(fetch, function(data) {
            if(data.ret == true){
                $("."+row).remove();
                inform(container,"Palanen poistettu. Muista tallentaa muutoksesi!");
            }
        });
    }
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
            var container = $("#feedback");
            inform(container,"Rivi poistettu. Muista tallentaa muutoksesi!");
        }
    }
});