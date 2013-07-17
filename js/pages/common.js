var wide = 0;
var reconnects = 0;
var linkshidden = 0;
var timeoutti;
$(function () {
    $('button, input:submit').button();
    $("[title]").each(function(){
        $(this).tooltip({ content: $(this).attr("title")});//Käytännössä mahdollistaa titleissä HTML:n käytön.
    });
    $("#links .btn").button({
        icons: {
            primary: "ui-icon-triangle-1-e"
        }
    });
    $("#links a.head-links").click(function (event) {
        event.preventDefault();
    });
    $("#links .btn").click(function () {
        normalize($(this).attr("value"));
    });
    update_load();
    update_clock();
    if (usrlvl > 0 && page != "logout") {
        connect();
        $("#chatbox").bind("keydown", function (e) {
            switch (e.which) {
                case 13: //enter
                    say($("#chatbox").val());
                    $("#chatbox").val('')
                    break;
            }
        });
        $(window).bind("keydown", function (e) {
            switch (e.which) {
                case 115: //F4
                    $("#chatbox").focus().select();
                    break;
            }
        });
    } else {
        $("#chat").hide(0);
    }

    var help_timeoutti;
    $("#helpimg").hover(
                    function () { $("#helptext").show(100) },
                    function () {
                        window.clearTimeout(help_timeoutti);
                        help_timeoutti = window.setTimeout(function () {
                            $("#helptext").hide(100);
                        },800);
                    });
    $("#helptext").hover(
                    function () { $("#helptext").show(100) },
                    function () {
                        window.clearTimeout(help_timeoutti);
                        help_timeoutti = window.setTimeout(function () {
                            $("#helptext").hide(100);
                        },800);
                    });
});

/**
 *
 * @access public
 * @return void
 **/
function toggle_links(){
    if(linkshidden == 0){
        $("#links").hide('blind',{direction:"horizontal"},200);$("#text").animate({"margin-left":"25px"},200);
        linkshidden = 1;
        $("#toggler").html(">>");
    }else{
        $("#links").show('blind',{direction:"horizontal"},200);$("#text").animate({"margin-left":"175px"},200);
        linkshidden = 0;
        $("#toggler").html("<<");
    }
}

/**
 *
 * @access public
 * @return void
 **/
function set_instance(instance){
    fetch = baseurl+'ajax/instance/'
    $.post(fetch,{"instance":instance},function(data) {
        if(data.ret == true){}
        else{
            alert("Instanssin vaihto epäonnistui!");
        }
    },"json");
}

/**
 *
 * @access public
 * @return void
 **/
function update_load(){
    if(usrlvl >= 3){
        var container = $("#show");
        fetch = baseurl+'ajax/loads/'
        $.getJSON(fetch,function(data) {
            container.html("Loadit: "+data.ret);
        });
        window.setTimeout(function(){
            update_load();
        },2500);
    }
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

function inform(container,message,timeout){
    if(timeout == undefined){
        timeout = 4000;
    }
    container.hide(0);
    container.html(message);
    container.show('drop',{ direction: "right", distance: "-50px" },500);
    window.setTimeout(function(){
        container.hide('drop',{ direction: "right", distance: "100px" },1000);
    },timeout);
}

function normalize(href){
    if($("#main").width() != 960){
        $('#main').animate({width:'960px'},300,'easeInOutCubic');
        setTimeout(function () {
            window.location = href;
        }, 310);
    }else{
        window.location = href;
    }
    console.log(href);
}

function widen(){
    if($(window).width() > 1060){
        var resize = $(window).width() - 100;
        window.setTimeout(function(){
            if($(window).width() > 1060){
                $('#main').animate({width:resize+'px'},2000,'easeInOutCubic');
            }
        },300);
    }
    wide_check();
}

/**
 *
 * @access public
 * @return void
 **/
function wide_check(){
    console.log("wide_check called");
    if(wide == 0){
        wide = 1;
        $(window).resize(function(){
            window.clearTimeout(timeoutti);
            timeoutti = window.setTimeout(function(){
                widen();
            },100);
        });
    }
}

var Server;

function log( text ) {
	$log = $('#chatlog');
	//Add text to log
	$log.append(text);
	//Autoscroll
	$log[0].scrollTop = $log[0].scrollHeight - $log[0].clientHeight;
}

function send( text ) {
	Server.send( 'message', text );
}

/**
 *
 * @access public
 * @return void
 **/
function say(message){
    if(message == ''){
    }else{
        var Digital = new Date();
        var hours = Digital.getHours();
        var minutes = Digital.getMinutes();
        var seconds = Digital.getSeconds()
        if(minutes < 10){
            minutes = "0" + minutes;
        }
        if(hours < 10){
            hours = "0" + hours;
        }
        if(seconds < 10){
            seconds = "0" + seconds;
        }
        var nick = '';
        if($("#nickbox").val() == ''){
            nick = usr;
        }else{
            nick = $("#nickbox").val();
        }
        send("&lt;"+nick+"&gt; "+message);
        log(hours + ":" + minutes + ":" + seconds + " <i>&lt;Sinä&gt;</i> " + message + "<br/>");
    }
}

function connect(){
	if(reconnects == 0){
        log('Yhdistetään...');
    }else{
        log('Yhdistetään uudelleen ('+reconnects+')...')
    }
	Server = new chatSocket('ws://hakku.tracon.fi:47774');

	//Let the user know we're connected
	Server.bind('open', function() {
		log( "Yhdistetty.<br/>" );
	});

	//OH NOES! Disconnection occurred.
	Server.bind('close', function( data ) {
		log( "Yhteys katkaistu.<br/>" );
		reconnects++;
		window.setTimeout(function(){
    		connect();
    	},10000);
	});

	//Log any messages sent from server
	Server.bind('message', function( payload ) {
		log( payload );
	});

	Server.connect();
};