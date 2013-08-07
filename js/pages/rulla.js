var id = 500;

function addrow(){
    fetch = baseurl+'ajax/rulla_row/' + id
    $.getJSON(fetch, function(data) {
        $('#rulla').append(data.ret);
    });
    id = id + 1;

}

$("#form").submit(function(e) {
    e.preventDefault();
});

function save(){
    var container = $("#feedback");
    container.hide(0);
    fetch = baseurl+'ajax/rulla_save/'
    $.post(fetch,$("#form").serialize(),function(data) {
        $.each(data,function(key,val){
            inform(container,val);
        });
    },"json");
    window.setTimeout(function(){
        $("#formidata").hide("explode",{pieces:8},1000);
    },100);
    window.setTimeout(function(){
        refresh_data();
    },300);
    return false;
}

function refresh_data(post){
    var container = $("#formidata");
    fetch = baseurl+'ajax/rulla_load/'
    $.getJSON(fetch,function(data) {
        container.html(data.data);
    });
    window.setTimeout(function(){
        container.show("clip",1000);
    },100);
}

function dele(row){
    var container = $("#feedback");
    container.hide(0);
    var sure = confirm("Oletko varma, että haluat poistaa tämän dian diashowsta?")
    if(sure){
        fetch = baseurl+'ajax/rulla_delete/' + row;
        $.getJSON(fetch, function(data) {
            if(data.ret == true){
                $("."+row).remove();
                inform(container,"Rivi poistettu. Muista tallentaa muutoksesi!");
            }else{
                inform(container,"Rivin poisto ei tapahtunut ongelmitta. Lataa sivu uudelleen ja yritä uudelleen.");
            }
        });

    }
}

$(document).on("mouseenter",".del",function() { $(this).addClass('hover'); });
$(document).on("mouseleave", ".del",function(){ $(this).removeClass('hover'); });
$(document).on("click", ".del",function(){
    if(!$(this).hasClass("ignore")){
        $(this).parent().parent().remove();
        var container = $("#feedback");
        inform(container,"Rivi poistettu. Muista tallentaa muutoksesi!");
    }
});