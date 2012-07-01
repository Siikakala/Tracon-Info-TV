$(function() {
    $( 'button, input:submit' ).button();
    $("#links .btn").button({
        icons:{
            primary: "ui-icon-triangle-1-e"
        }
    });
    $("#links a.head-links").click(function(event){
        event.preventDefault();
    });
    $("#links .btn").click(function(){
        normalize($(this).attr("value"));
    });
    update_load();
    update_clock();
    if(usrlvl > 0){
        connect();
        $("#chatbox").bind("keydown",function(e){
            switch(e.which){
                case 13://enter
                    say($("#chatbox").val());
                    $("#chatbox").val('')
                    break;
            }
        });
    }else{
        $("#chat").hide(0);
    }
});

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
            container.html("Serverin loadit: "+data.ret);
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

function inform(container,message){
    container.hide(0);
    container.html(message);
    container.show('drop',{ direction: "right", distance: "-50px" },500);
    window.setTimeout(function(){
        container.hide('drop',{ direction: "right", distance: "100px" },1000);
    },4000);
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

function connect(){
	log('Yhdistetään...');
	Server = new chatSocket('ws://10.10.10.2:9300');

	//Let the user know we're connected
	Server.bind('open', function() {
		log( "Yhdistetty.<br/>" );
	});

	//OH NOES! Disconnection occurred.
	Server.bind('close', function( data ) {
		log( "Yhteys katkaistu.<br/>" );
	});

	//Log any messages sent from server
	Server.bind('message', function( payload ) {
		log( payload );
		console.log('\u0007');
	});

	Server.connect();
};