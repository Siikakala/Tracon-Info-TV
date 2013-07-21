$(document).ready(function() {
    $(document).on("contextmenu",function(e){
      return false; //tapetaan selaimen oma context menu koko sivulta
   });
   widen();
   ref();
});

$("form").submit(function(e) {
    e.preventDefault();
});

var row = 0;

$(function(){
    $(".datepick").datepicker({
		defaultDate: begindate,
		firstDay: 1,
		minDate:-7,
		maxDate:"+1Y +6M",
		dateFormat:"dd.mm.yy",
		changeMonth: true,
		numberOfMonths: 1,
		changeYear: true
	});
    $("#dialog-confirm-del").dialog({
		resizable: false,
		autoOpen: false,
		height:140,
		modal: true,
		buttons: {
			"Poista": function() {
				fetch = baseurl+'ajax/tuotanto_del/';
                $.post(fetch, { "row": row }, function(data){
                    if(data.ret == true){
                        $('#'+row).remove();
                    }else{
                        alert("Rivin poisto poisto epäonnistui!\n\n"+data.ret);
                    }
                },"json");
                $(this).dialog( "close" );
			},
			"Peruuta": function() {
				$(this).dialog( "close" );
			}
		}
	});
    $("#dialog-edit").dialog({
		resizable: false,
		autoOpen: false,
		height:460,
		width:550,
		modal: true,
		buttons: {
			"Päivitä": function() {
				fetch = baseurl+'ajax/tuotanto_edit/';
                $.post(fetch, $("#tuotanto_edit").serialize(), function(data){
                    if(data.ret == true){
                        inform($("#dialog-edit-feedback"),"Rivin muokattu!");
                    }else{
                        alert("Rivin muokkaus poisto epäonnistui!\n\n"+data.ret);
                    }
                },"json");
			},
			"Sulje": function() {
    			refresh();
				$(this).dialog( "close" );
			}
		}
	});
});

function refresh(){
    var container = $("#refresh");
    fetch = baseurl+'ajax/tuotanto_refresh/'
    $.getJSON(fetch,function(data) {
        container.html(data.rivit);
    });
};

function ref(){
    refresh();
    window.setTimeout(function(){
        ref();
    },5000);
};

/**
 * Tallentaa uudet rivit
 * @access public
 * @return boolean false
 **/
function add(){
    fetch = baseurl+'ajax/tuotanto_save/'
    $.post(fetch,$("#tuotanto_add").serialize(),function(data) {
        if(data.ret == true){
            $("form")[0].reset();
            inform($("#feedback"),"Tallennettu");
        }else{
            inform($("#feedback"),data.msg);
        }
    },"json");
    return false;
}

$(document).on("click", "body", function(){
   $(".contextMenu").hide();
});

$(document).on("mouseup", "td", function (e){
    row = $(this).parent().attr('id');
    //pitää tehdä näin koska muuten ei saa arvoa jos sattuukin klikkaan sen eventtitekstin päältä
    var eventti = $(this).parent().children('td[type="eventti"]');
    var rivi = $(this).parent();
    switch(e.which){
        case 3:
            $("#myMenu").css({ top: e.pageY, left: e.pageX }).show('fast');
            $("#myMenu").find('a').click(function(){
                $(".contextMenu").hide();
                $(".drowi").text(eventti.text());
                switch($(this).attr('href').substr(1)){
                    case "edit":
                        $("#tuotanto_edit")[ 0 ].reset();
                        $("#tuotanto-edit-feedback").html('');
                        $("#edit-id").val(row);
                        fetch = baseurl+'ajax/tuotanto_populate';
                        $.post(fetch,{ "row": row },function(data){
                            $.each(data.ret, function(field,value){
                                $("#" + field).val(value);
                            });
                        },"json");
                        $("#dialog-edit").dialog('open');
                        break;
                    case "del":
                        $("#dialog-confirm-del").dialog('open');
                        break;
                }
            });
            break;
    }
});