
var page = "0001";
var page_was = "";
var twiit = "";
var marqText;
var maincont;
$(function() {
    var container = $("#text");
    update_clock();
    var fetch = "";
    window.setTimeout(function(){
        check(1);
    },50);

});

function update_clock(){
    var kello = $("#kello");
    var Digital = new Date();
    var hours = Digital.getHours();
    var minutes = Digital.getMinutes();
    if(minutes < 10){
        minutes = "0" + minutes;
    }
    if(hours < 10){
        hours = "0" + hours;
    }
    kello.html(hours + ":" + minutes);
    window.setTimeout(function(){
        update_clock();
    },1000);
}

function drawTimer(percent,index){
	$('#'+index+'.timer').html('<div id="'+index+'" class="percent"></div><div id="slice-'+index+'"'+(percent > 50?' class="gt50"':'')+'><div id="pie-'+index+'" class="pie"></div>'+(percent > 50?'<div id="fillpie-'+index+'" class="pie fill"></div>':'')+'</div>');
	var deg = 360/100*percent;
	$('#slice-'+index+' .pie').css({
		'-moz-transform':'rotate('+deg+'deg)',
		'-webkit-transform':'rotate('+deg+'deg)',
		'-o-transform':'rotate('+deg+'deg)',
		'transform':'rotate('+deg+'deg)'
	});
	$('#'+index+'.percent').html(Math.round(percent)+'%');
}

function check(cont){

    var container = $("#text");
    fetch = baseurl+'ajax/check/';
    $.getJSON(fetch, function(data) {
        if(data.ret == true){
            $.each(data,function(index,value){
                switch(index){
                  case "ret":
                      break;
                  case "dia":
                      if(value.changed == true){
                        switch(value.part){
                          case "text":
                            container.html(value.palautus);
                            $.each(value.pie,function(index2,value2){
                                if(value2){
                                    drawTimer(value2,index2);
                                }
                            });
                            break;
                        }
                      }
                      break;
                  case "fcn":
                      $("#client").html(value.name);
                      break;
                  case "scroller":
                      if(value.changed == true){
                          marqText.setText(value.palautus);
                      }
                      break;
                  case "page":
                      page_was = page;
                      page = value;
                      break;
                }
            });
        }
    });
    if(cont == 1){
        window.setTimeout(function(){
            check(1);
        },2000);
    }
}