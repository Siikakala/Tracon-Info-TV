jQuery.fx.interval = 5;
window.onfocus = function () {
$.fx.off = false;
};

window.onblur = function () {
$.fx.off = true;
};
$().framerate({framerate: 200, logframes: false});
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
    window.setTimeout(function(){
        //scrolleri();
    },500);

});

window.onload = function() {
        var scroller = new Kinetic.Stage({
          container: "marquee",
          width: 690,
          height: 100
        });
        var main = new Kinetic.Stage({
          container: "text",
          width: 900,
          height: 450
        });

        var layer1 = new Kinetic.Layer();
        var layer2 = new Kinetic.Layer();

        marqText = new Kinetic.Text({
          x: 700,
          y: 2,
          text: "Info-TV",
          fontSize: 80,
          fontStyle: "bold",
          fontFamily: "Helvetica",
          textFill: "black"
        });

        layer1.add(marqText);
        scroller.add(layer1);

        layer1.setThrottle(2000);

        var initial = 700;
        var moved = 0;

        scroller.onFrame(function(frame) {
            marqText.setX(initial-moved);
            layer1.draw();
            moved = moved + 3;
            if (moved > (700+marqText.getTextWidth())) {
                moved = 0;
            }
        });

        scroller.start();
}

function scrolleri(){
    var container = $(".scrollingtext");
    var paikka = parseInt(container.css("left"));
    var leveys = container.width();
    var ulkona = paikka + leveys;
    container.animate({"left": "-=10px"},60,"linear");
    if(ulkona < 0){
        container.stop(true, true);
        window.setTimeout(function(){
            container.css("left","700px");
        },50);
    }
    window.setTimeout(function(){
        if(container.queue("fx").lenght > 1){
            container.clearQueue("fx");
        }
        scrolleri();
    },59);
}

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
    var twitter = $("#twitter");
    var scroller = $("#rullaaja");
    var n = container.queue("fx");
    var y = twitter.queue("fx");
    var x = $("#text_cont").queue("fx");
    if(n.length > 10){
        container.clearQueue();
    }
    if(y.length > 10){
        twitter.clearQueue();
    }
    if(x.length > 6){
        $("#text_cont").clearQueue();
    }
    fetch = baseurl+'ajax/check/';
    $.getJSON(fetch, function(data) {
        if(data.ret == true){
            $.each(data,function(index,value){
                switch(index){
                    case "ret":
                        break;
                    case "dia":
                        if(value.changed == true){
                            container.hide("puff",700);

                            window.setTimeout(function(){
                                switch(value.part){
                                    case "text":
                                        twitter.hide(290);
                                        $("#text_cont").show('blind',300);
                                        window.setTimeout(function(){
                                            container.html(value.palautus);
                                            $.each(value.pie,function(index2,value2){
                                                if(value2){
                                                    drawTimer(value2,index2);
                                                }
                                            });
                                            window.setTimeout(function(){
                                                container.show('clip',300);
                                            },100);
                                        },300);
                                        break;
                                    case "twitter":
                                        twiit = page;
                                        container.hide("puff",300);
                                        window.setTimeout(function(){
                                            $("#text_cont").hide('blind',300);
                                        },200);
                                        window.setTimeout(function(){
                                            twitter.show(300);
                                        },810);
                                        container.html("");
                                        break;
                                    case "video":
                                        twitter.hide(300);
                                        window.setTimeout(function(){
                                            container.show(0);
                                            $("#text_cont").show(0);
                                            window.setTimeout(function(){
                                                container.html(value.palautus);
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
                                                            netConnectionUrl:value.video
                                                        },
                                                        controls: null
                                                    }
                                                });
                                            },100);
                                        },310);
                                        break;
                                }
                            },702);
                        }
                        break;
                    case "fcn":
                        $("#client").html(value.name);
                        break;
                    case "scroller":
                        if(value.changed == true){
                            //scroller.html(value.palautus);
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
        },1500);
    }
}