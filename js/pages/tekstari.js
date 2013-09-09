var timeout;

function send(form){
    var container = $("#feedback");
    if($("#"+form+"-message").val() == ""){
        inform(container,"<span style='color:red; font-size:20px; font-weight:bolder;'>VIESTI PUUTTUU!</span> Kirjoita viesti ja paina vasta sitten lähetä.",10000);
    }else{
        fetch = baseurl+'ajax/tekstari_send/'
        $.post(fetch,$("#"+form).serialize(),function(data) {
            inform(container,data.ret,6000);
        },"json");
        update_progress();
    }
    return false;
}
$(function(){
    $("#tekstari-accord").accordion({heightStyle: 'content'});
    $("#inbox-accord").accordion({heightStyle: 'content', active: "false", collapsible: "true"});
    $("#valitystieto-accord").accordion({heightStyle: 'content', active: "false", collapsible: "true"});

    update_balance();
    check_progress();

    var uploader = new qq.FileUploader({
            element: document.getElementById('fileupload'),
            action: baseurl+'ajax/tekstari_file/',
            onComplete: function(id, fileName, responseJSON){
                if(responseJSON.timeout != undefined){
                    inform($("#feedback"),responseJSON.ret,responseJSON.timeout);
                }else{
                    inform($("#feedback"),responseJSON.ret);
                }
                update_progress();
            },
        });

    $("#dialog-tekstari-help").dialog({
        resizable: true,
        autoOpen: false,
        height:750,
        width:700,
        modal: true,
        buttons: {
            "Ok": function() {
                $(this).dialog( "close" );
            }
        }
    });
    $("#dialog-tekstari-sent").dialog({
        resizable: true,
        autoOpen: false,
        height:750,
        width:700,
        modal: true,
        open: function(e,ui){
            $.getJSON(baseurl+"ajax/tekstari_sent",function(data){
                $("#dialog-tekstari-sent.ui-dialog-content").html(data.ret);
            });
        },
        buttons: {
            "Ok": function() {
                $(this).dialog( "close" );
            }
        }
    });
    $("#dialog-tekstari-received").dialog({
        resizable: true,
        autoOpen: false,
        height:750,
        width:700,
        modal: true,
        open: function(e,ui){
            $.getJSON(baseurl+"ajax/tekstari_received",function(data){
                $("#dialog-tekstari-received.ui-dialog-content").html(data.ret);
            });
        },
        buttons: {
            "Ok": function() {
                $(this).dialog( "close" );
            }
        }
    });
});

function update_balance(){
    var container = $("#saldo");
    fetch = baseurl+"ajax/tekstari_balance/";
    $.getJSON(fetch,function(data){
        container.html(data.ret);
    });
    window.setTimeout(function(){
        update_balance();
    },120000);
}

function check_progress(){
    fetch = baseurl+"ajax/tekstari_progress/";
    $.getJSON(fetch,function(data){
        if(data.ret > 0){
            update_progress();
        }
    });
}

function update_progress(){
    var container = $("#progress");
    fetch = baseurl+"ajax/tekstari_progress/";
    $.getJSON(fetch,function(data){
        container.html("Jonossa vielä " + data.ret + " viestiä.");
        if(container.is(":hidden")){
            container.show('drop',{ direction: "right", distance: "-50px" },500);
        }
        if(data.ret > 0){
            window.setTimeout(function(){
                update_progress();
            },1000);
        }
        window.clearTimeout(timeout);
        timeout = window.setTimeout(function () {
            container.hide('drop',{ direction: "right", distance: "100px" },1000);
        },5000);
    });
}

$("form").submit(function(e) {
    e.preventDefault();
});

