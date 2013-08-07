var gid = 0;

function load(id){
    widen(1160);
    var container = $("#edit");
    var identti = $("#ident_");
    var ident = $("#ident");
    container.hide(80);
    identti.hide(80);
    $('textarea.tinymce').tinymce().remove();
    if(id == 0){
        container.html("Valitse jokin dia. Mikäli haluamasi dia ei ole listauksessa, lataa sivu uudelleen.");
        container.show("medium");
    }else{
        container.html("Ladataan dia id "+id+", odota hetki.");
        container.show("medium");
        fetch = baseurl+'ajax/dia_load/' + id;
        $.getJSON(fetch, function(data) {
            if(data.ret){
                gid = id;
                container.hide(500);
                window.setTimeout(function(){
                    container.html(data.ret);
                    ident.val(data.tunniste);
                    tinymce_setup();
                    container.show("medium");
                    identti.show("medium");
                },700);
            }
        });
    }
}

function uusi(){
    widen(1160);
    var container = $("#edit");
    var identti = $("#ident_");
    var ident = $("#ident");
    container.hide(0);
    identti.hide(0);
    container.html('<br/><textarea id="loota" name="loota-new" cols="50" rows="10" class="tinymce"></textarea>');
    ident.val("");
    ident.removeClass("new");
    tinymce_setup();
    $("#loota_cancel_voice").html("Poista dia");
    container.show("medium");
    identti.show("medium");
    gid = 0;
    return false;
}

function tinymce_tallenna(){
    var m = $("#loota");
    var cont = m.tinymce().getContent();
    var ident = $("#ident").val();
    if(ident == ""){
        alert("Et määritellyt dialle tunnistetta!");
        $("#ident").addClass("new");
    }else{
        m.tinymce().setProgressState(1); // Show progress
        fetch = baseurl+'ajax/dia_save/'
        $.post(fetch, { "cont": cont, "ident": ident, "id": gid }, function(data){
            if(data.ret == true){
                //kaikki ok.
            }else{
                alert(data.ret);
            }
            window.setTimeout(function(){
                m.tinymce().setProgressState(0); // Hide progress
                $("#ident").removeClass("new");
            },500);
        },"json");
    }
}

function tinymce_poista(){
    if(gid == 0){
        $("#loota").html("");
        $("#ident").val("");
    }else{
        var sure = confirm("Oletko varma että haluat poistaa dian " + $("#ident").val() + "?");
        if(sure){
            fetch = baseurl+'ajax/dia_delete/' + gid;
            $.getJSON(fetch, function(data) {
                if(data.ret == true){
                    $("#edit").hide(500);
                    $("#ident_").hide(500);
                    window.setTimeout(function(){
                        $("#edit").html("Dia poistettu.");
                        $("#edit").show("medium");
                        $("#ident").val("");
                    },500);
                }else{
                    alert(data.ret);
                }
            });
        }
    }
}
