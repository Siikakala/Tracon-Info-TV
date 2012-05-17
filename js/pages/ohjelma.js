$(document).ready(function() {
    $(document).on("contextmenu",function(e){
      return false; //tapetaan selaimen oma context menu koko sivulta
   });
   widen();
});
var row = 0;
var passerror = 0;

$(function(){
    e_dro();
    $("#dialog-add").dialog({
		resizable: false,
		autoOpen: false,
		height:460,
		width: 550,
		modal: true,
		buttons: {
			"Lisää": function() {
				fetch = baseurl+'ajax/ohjelma_add/';
                $.post(fetch, $("#ohjelma_add").serialize(), function(data){
                    if(data.ret == true){
                        $( "form" )[ 0 ].reset();
                    }else{
                        alert("Ohjelman lisäys epäonnistui!\n\n"+data.ret);
                    }
                },"json");
			},
			"Sulje": function() {
				$(this).dialog( "close" );
				location.reload(true);
			}
		}
	});

	$("#dialog-kategoria-add").dialog({
		resizable: false,
		autoOpen: false,
		height:200,
		width: 280,
		modal: true,
		buttons: {
			"Lisää": function() {
				fetch = baseurl+'ajax/kategoria_add/';
                $.post(fetch, $("#kategoria_add").serialize(), function(data){
                    if(data.ret == true){
                        $("#dialog-kategoria-add").dialog( "close" );
                    }else{
                        alert("Kategorian lisäys epäonnistui!\n\n"+data.ret);
                    }
                },"json");
			},
			"Peruuta": function() {
				$(this).dialog( "close" );
			}
		}
	});

	$("#dialog-slot-add").dialog({
		resizable: false,
		autoOpen: false,
		height:200,
		width: 280,
		modal: true,
		buttons: {
			"Lisää": function() {
				fetch = baseurl+'ajax/slot_add/';
                $.post(fetch, $("#slot_add").serialize(), function(data){
                    if(data.ret == true){
                        $("#dialog-slot-add").dialog( "close" );
                    }else{
                        alert("Aikaslotin lisäys epäonnistui!\n\n"+data.ret);
                    }
                },"json");
			},
			"Peruuta": function() {
				$(this).dialog( "close" );
			}
		}
	});

	$("#dialog-sali-add").dialog({
		resizable: false,
		autoOpen: false,
		height:200,
		width: 280,
		modal: true,
		buttons: {
			"Lisää": function() {
				fetch = baseurl+'ajax/sali_add/';
                $.post(fetch, $("#sali_add").serialize(), function(data){
                    if(data.ret == true){
                        $("#dialog-sali-add").dialog( "close" );
                    }else{
                        alert("Salin lisäys epäonnistui!\n\n"+data.ret);
                    }
                },"json");
			},
			"Peruuta": function() {
				$(this).dialog( "close" );
			}
		}
	});

	var dates = $( "#from, #to" ).datepicker({
		defaultDate: "+1w",
		firstDay: 1,
		minDate:-7,
		maxDate:"+1Y +6M",
		dateFormat:"dd.mm.yy",
		changeMonth: true,
		numberOfMonths: 1,
		onSelect: function( selectedDate ) {
			var option = this.id == "from" ? "minDate" : "maxDate",
				instance = $( this ).data( "datepicker" ),
				date = $.datepicker.parseDate(
					instance.settings.dateFormat ||
					$.datepicker._defaults.dateFormat,
					selectedDate, instance.settings );
			dates.not( this ).datepicker( "option", option, date );
		}
	});

	$("#tabit").tabs({
                    cookie:{}
                    });
	$("#salit").buttonset();
	//$("#ohjelmanumerot").selectable();
	$("#kategoriat_acc").accordion({collapsible:true,active:false,autoHeight:false});
	$("#slotit_acc").accordion({collapsible:true,active:false,autoHeight:false});
	$("#salit_acc").accordion({collapsible:true,active:false,autoHeight:false});



    $(".drag").draggable({
                         snap: ".target",
                         snapMode: "inner",
                         revert: "invalid",
                         zIndex: 4,
                         cursorAt:{top: 0, left: -20}
                         });
});

function e_dro(){
    $(".target").droppable("destroy");
    $(".target").droppable({
                tolerance: 'pointer',
                accept: '.drag',
                hoverClass: 'new',
                drop: function(event,ui){
                    console.log("Drop detected!");
                    var hour = $(this).parent().attr('hour');
                    var added = $(this).attr('added');
                    var pos = $(this).position();
                    fetch = baseurl+'ajax/ohjelma_save/';
                    $.post(fetch, { "id": ui.draggable.attr('oid'), "hour": hour, "sali": added }, function(data){
                        if(data.ret == true){
                            if(ui.draggable.parent().is("li")){
                                ui.draggable.parent().css({'height':'0'});
                            }
                            ui.draggable.prependTo("#cal-cont");
                    		ui.draggable.css({'top': pos.top, 'left': pos.left,'position': 'absolute'});
                    		ui.draggable.attr('added',added);
                    		ui.draggable.attr('hour',hour);
                        }else{
                            console.log("Ny mentiin falsee.");
                        }
                    },"json");
                }
    });
}

function save(){
	var container = $("#asetus_feedback");
	var start = $("#from").val();
	var stop = $("#to").val();
	var starth = $("#alku-klo-h").val();
	var startm = $("#alku-klo-m").val();
	var stoph = $("#loppu-klo-h").val();
	var stopm = $("#loppu-klo-m").val();
	fetch = baseurl+'ajax/tapahtuma_save/';
    $.post(fetch, { "start": start, "starth": starth, "startm": startm, "stop": stop,  "stoph": stoph, "stopm": stopm }, function(data){
        if(data.ret == true){
            inform(container,"Asetukset tallennettu!");
        }else{
            inform(container,"Tallennus epäonnistui!\n\n"+data.ret);
        }
    },"json");
}

$("#salit label").live("click",function(){
	var id = $(this).attr("id");
	var pressed = $(this).attr("aria-pressed");
    if(pressed == "false"){
    	var sali = $(this).text();
    	fetch = baseurl+'ajax/ohjelma_load/';
    	$.post(fetch, {"sali":id}, function(data){
        	$(".drag").draggable("destroy");
        	$(".target").droppable("destroy");
            $(".timetable tbody tr").append('<td class="target" added="'+id+'">&nbsp;</td>');//tässä vaiheessa vasta piirretään ruudukko
            $(".timetable thead tr").append('<th added="'+id+'">'+sali+'</th>');
            if(data.ret == true){
                $.each(data.ohjelmat,function(index,ohjelma){
                    var pos = $(".timetable tbody").find('tr[hour|="'+ohjelma.hour+'"] td[added|="'+id+'"]').position();
                    var element = "<div added=\""+id+"\" hour=\""+ohjelma.hour+"\" class=\"ui-widget-content drag ui-corner-all "+ohjelma.kategoria+"\" oid=\""+ohjelma.oid+"\" style=\"width:180px;height:"+ohjelma.height+"px;z-index:3;list-style-type: none;padding:5px;position:absolute;\" title=\""+ohjelma.title+"\">"+ohjelma.nimi+"</div>";
                    $("#cal-cont").prepend(element);
                    $("#cal-cont").find('div[oid|="'+ohjelma.oid+'"]').css({'top':pos.top,'left':pos.left});
                });
            }else{
            }
            e_dro();
            $(".drag").draggable({
                         snap: ".target",
                         refreshPositions: true,
                         snapMode: "inner",
                         revert: "invalid",
                         zIndex: 4,
                         cursorAt:{top:0,left: -20}
                         });
        },"json");


    }else{
        $('.timetable td[added|="'+id+'"]').remove();
        $('.timetable th[added|="'+id+'"]').remove();
        $('#cal-cont div[added|="'+id+'"]').remove();
        $('#cal-cont div').each(function(index,element){
            var pos = $(".timetable tbody").find('tr[hour|="'+$(element).attr('hour')+'"] td[added|="'+$(element).attr('added')+'"]').position();
            $(element).css({'top':pos.top,'left':pos.left});
        });
    }
});

$("#pituusselect").live("change", function(e){
	if($(this).val() == "muu"){
    	$("#muupituus").show('slide','','medium');
	}else{
    	$("#muupituus").hide('slide','','medium');
    }
});