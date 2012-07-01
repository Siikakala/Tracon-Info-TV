$(document).ready(function() {
    ref();
    $(document).on("contextmenu",function(e){
      return false; //tapetaan selaimen oma context menu koko sivulta
   });
   $(window).bind("keydown",function(e){
        switch(e.which){
            case 112://F1
            case 114://F3
            case 116://F5
            case 122://F11
                e.preventDefault();
                return false;
                break;
            case 113://F2
                e.preventDefault();
                $("#filter").focus().select();
                return false;
                break;
            case 117://F6
                e.preventDefault();
                $("#tag").val("tiedote");
                $("#com").focus();
                return false;
                break;
            case 118://F7
                e.preventDefault();
                $("#tag").val("ongelma");
                $("#com").focus();
                return false;
                break;
            case 119://F8
                e.preventDefault();
                $("#tag").val("kysely");
                $("#com").focus();
                return false;
                break;
            case 120://F9
                e.preventDefault();
                $("#tag").val("löytötavara");
                $("#com").focus();
                return false;
                break;
            case 121://F10
                e.preventDefault();
                $("#tag").val("muu");
                $("#com").focus();
                return false;
                break;
        }
   });
});

var row = 0;
var tag = "";
var timeout;

$(function() {
    $("#filter_cont").delegate("input","keyup",function(event) {
        window.clearTimeout(timeout);
        timeout = window.setTimeout(function(){
            search();
        },200);
    })
});

function search(){
    var container = $("#table");
    var search = $("#filter").val();
    fetch = baseurl+'ajax/todo_search/'
    $.post(fetch,{ "search": search},function(data) {
        container.html(data.ret);
        if(data.profiler != undefined){
            $("#profiler").html(data.profiler);
        }
    },"json");
    return false;
};

function save(){
    var container = $("#feedback");
    container.hide('fast');
    fetch = baseurl+'ajax/todo_save/'
    $.post(fetch,$("#form").serialize(),function(data) {
        container.html(data.ret);
        if(data.ok){
            var adder = $("#adder").val();
            $( "form" )[ 0 ].reset();
            $("#adder").val(adder);
        }
    },"json");
    container.show('drop',{ direction: "right", distance: "-50px" },500);
    search();

    window.setTimeout(function(){
        container.hide('drop',{ direction: "right", distance: "100px" },1000);
    },4000);
    return false;
};

function ref(){
    search();
    window.setTimeout(function(){
        ref();
    },5000);
};

$(function() {
    $("#dialog-confirm").dialog({
		resizable: false,
		autoOpen: false,
		height:140,
		modal: true,
		buttons: {
			"Poista": function() {
				fetch = baseurl+'ajax/todo_unack/'
                $.post(fetch, { "row": row }, function(data){
                    if(data.ret == true){
                        $('#'+row).removeClass("type-"+tag+"-kuitattu");
                    }else{
                        alert("Kuittauksen poisto epäonnistui!");
                    }
                },"json");
                $(this).dialog( "close" );
			},
			"Peruuta": function() {
				$(this).dialog( "close" );
			}
		}
	});

	$("#dialog-confirm-del").dialog({
		resizable: false,
		autoOpen: false,
		height:140,
		modal: true,
		buttons: {
			"Poista": function() {
				fetch = baseurl+'ajax/todo_del/'
                $.post(fetch, { "row": row }, function(data){
                    if(data.ret == true){
                        $('#'+row).remove();
                    }else{
                        alert("Rivin poisto epäonnistui!");
                    }
                },"json");
                $(this).dialog( "close" );
			},
			"Peruuta": function() {
				$(this).dialog( "close" );
			}
		}
	});
});

$("body").live("click",function(){
	$(".contextMenu").hide();
});

$("td").live(
    "mouseup",function (e){
        row = $(this).attr("row");
        tag = $(this).parent().attr("tag");
        switch(e.which){
          //left click
          case 1:
            if($('#'+row).is(".type-löytötavara-kuitattu,.type-ongelma-kuitattu,.type-tiedote-kuitattu,.type-kysely-kuitattu,.type-muu-kuitattu")){
                $("#dialog-confirm").dialog('open');
            }else{
                fetch = baseurl+'ajax/todo_ack/'
                $.post(fetch, { "row": row }, function(data){
                    if(data.ret == true){
                        $('#'+row).addClass("type-"+tag+"-kuitattu");
                    }else{
                        alert("Kuittaus epäonnistui!");
                    }
                },"json");
            }
            break;
          //right click
          case 3:
            $("#myMenu").css({ top: e.pageY, left: e.pageX }).show('fast');
            $("#myMenu").find('a').click(function(){
                $(".contextMenu").hide();
                switch($(this).attr('href').substr(1)){
                    case "check":
                        fetch = baseurl+'ajax/todo_ack/'
                        $.post(fetch, { "row": row }, function(data){
                            if(data.ret == true){
                                $('#'+row).addClass("type-"+tag+"-kuitattu");
                            }else{
                                alert("Kuittaus epäonnistui!");
                            }
                        },"json");
                        break;
                    case "del":
                        $("#dialog-confirm-del").dialog('open');
                        break;
                }
            });
            break;
        }
    }
);

$("form").submit(function(e) {
    e.preventDefault();
});