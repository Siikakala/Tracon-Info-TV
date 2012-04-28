$(document).ready(function() {
    $(document).on("contextmenu",function(e){
      return false; //tapetaan selaimen oma context menu koko sivulta
   });

});
var row = 0;
var passerror = 0;

$(function(){
    $("#dialog-confirm-del").dialog({
		resizable: false,
		autoOpen: false,
		height:140,
		modal: true,
		buttons: {
			"Poista": function() {
				fetch = baseurl+'ajax/user_del/';
                $.post(fetch, { "row": row }, function(data){
                    if(data.ret == true){
                        $('#'+row).remove();
                    }else{
                        alert("Käyttäjän poisto epäonnistui!\n\n"+data.ret);
                    }
                },"json");
                $(this).dialog( "close" );
			},
			"Peruuta": function() {
				$(this).dialog( "close" );
			}
		}
	});
    $("#dialog-pass").dialog({
		resizable: false,
		autoOpen: false,
		height:210,
		width: 300,
		modal: true,
		buttons: {
			"Vaihda": function() {
				if(passerror == 0){
    				$("#dialog-pass-feedback").css('color','white');
					fetch = baseurl+'ajax/user_pass/';
                    $.post(fetch, { "row": row, "pass": MD5($("#pass1").val()) }, function(data){
                        if(data.ret == true){
                            inform($("#dialog-pass-feedback"),"Salasana vaihdettu");
                            $( "form" )[ 0 ].reset();
                        }else{
                            inform($("#dialog-pass-feedback"),"Käyttäjän salasanan vaihto epäonnistui!<br/>"+data.ret);
                        }
                    },"json");
                }else{
                    $("#dialog-pass-feedback").css('color','red');
                }
			},
			"Sulje": function() {
				$(this).dialog( "close" );
			}
		}
	});
	$("#dialog-level").dialog({
		resizable: false,
		autoOpen: false,
		height:200,
		width: 250,
		modal: true,
		buttons: {
			"Vaihda": function() {
				fetch = baseurl+'ajax/user_level/';
                $.post(fetch, { "row": row, "level": $("#level").val() }, function(data){
                    if(data.ret == true){
                        inform($("#dialog-level-feedback"),"Käyttäjätaso vaihdettu, uusi taso on "+data.newlevel);
                    }else{
                        inform($("#dialog-level-feedback"),"Käyttäjätason vaihto epäonnistui!<br/>"+data.ret);
                    }
                },"json");
			},
			"Sulje": function() {
				$(this).dialog( "close" );
				location.reload(true);
			}
		}
	});
	$("#dialog-newuser").dialog({
		resizable: false,
		autoOpen: false,
		height:260,
		width: 300,
		modal: true,
		buttons: {
			"Lisää": function() {
				if(passerror == 0){
    				$("#dialog-newuser-feedback").css('color','white');
					fetch = baseurl+'ajax/user_new/';
                    $.post(fetch, { "user": $("#user").val(), "pass": MD5($("#u_pass1").val()), "level": $("#u_level").val() }, function(data){
                        if(data.ret == true){
                            inform($("#dialog-newuser-feedback"),"Käyttäjä lisätty.");
                            $( "form" )[ 0 ].reset();
                        }else{
                            inform($("#dialog-pass-feedback"),"Käyttäjän lisääminen epäonnistui!<br/>"+data.ret);
                        }
                    },"json");
                }else{
                    $("#dialog-pass-feedback").css('color','red');
                }
			},
			"Sulje": function() {
				$(this).dialog( "close" );
				location.reload(true);
			}
		}
	});
});


$("body").live("click",function(){
   $(".contextMenu").hide();
});

$("td").live("mouseup",function (e){
    row = $(this).attr('row');
    var user = $(this).parent().attr('usr');
    switch(e.which){
        case 3:
            $("#myMenu").css({ top: e.pageY, left: e.pageX }).show('fast');
            $("#myMenu").find('a').click(function(){
                $(".contextMenu").hide();
                $(".useri").html(user);
                switch($(this).attr('href').substr(1)){
                    case "pass":
                        $( "form" )[ 0 ].reset();
                        $("#dialog-pass-feedback").html("");
                        passerror = 0;
                        $("#dialog-pass").dialog('open');
                        break;
                    case "del":
                        $("#dialog-confirm-del").dialog('open');
                        break;
                    case "chg":
                        $("#dialog-level").dialog('open');
                        break;
                }
            });
            break;
    }
});

$("#pass2").live("keyup",function (e){
    if($("#pass1").val() != $("#pass2").val()){
        $("#dialog-pass-feedback").html("Salasanat eivät täsmää!");
        passerror = 1;
    }else{
        $("#dialog-pass-feedback").html("");
        passerror = 0;
    }
});
$("#u_pass2").live("keyup",function (e){
    if($("#u_pass1").val() != $("#u_pass2").val()){
        $("#dialog-newuser-feedback").html("Salasanat eivät täsmää!");
        passerror = 1;
    }else{
        $("#dialog-newuser-feedback").html("");
        passerror = 0;
    }
});