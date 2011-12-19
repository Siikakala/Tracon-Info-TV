<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Frontend extends Controller {


    //vastaa constructoria, mutta sitä ei tarvitse overloadata.
    public function before(){
    	$db = Database::instance();
    	$this->session = Session::instance();
    	//$halp = new Halp();
    	$this->view = new View('start');
        $this->view->js  = "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."jquery/jquery-1.6.2.min.js\"></script>";
    	$this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."jquery/jquery-ui-1.8.16.custom.min.js\"></script>";
        $this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."jquery/jquery.validate.js\"></script>";
        $this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."jquery/jquery.metadata.js\"></script>";
        $this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."jquery/jquery.Scroller-1.0.src.js\"></script>";
        $this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."flowplayer/flowplayer-3.2.6.min.js\"></script>";
        $this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."js/widget.js\"></script>";
        //$this->view->js .= "\n<script src=\"http://yui.yahooapis.com/3.4.0/build/yui/yui-min.js\"></script>"; //tätä ei toistaiseksi käytetä.
    	$this->view->css = html::style("css/admin.css");

    }

    public function action_to_tv(){
        $this->request->redirect("tv");
    }

	public function action_index()
	{
    	$page = $this->request->param("id");
    	if($page){//jos requestetaan tietty sivu, näytetään sit pelkkää sitä! :D
            $check = "0";
            $tm = 1000;
        }else{
            $page = "0001";
            $check = "1";
            $tm = 5000;
        }
    	$this->view->js .= '
    	<script type="text/javascript">
        	var page = "'.$page.'";
        	var page_was = "";
        	var twiit = "";
            $(function() {

                    update_clock();
                    var container = $("#text");
                    var fetch = "";
                    window.setTimeout(function(){
                        check('.$check.');
                    },'.$tm.');
                    window.setTimeout(function(){
                        scrolleri();
                    },500);

                });

                function scrolleri(){
                    var container = $(".scrollingtext");
                    var paikka = parseInt(container.css("left"));
                    var leveys = container.width();
                    var ulkona = paikka + leveys;
                    container.animate({"left": "-=100px"},600,"linear");
                    if(ulkona < 0){
                        container.stop(true);
                        window.setTimeout(function(){
                            container.css("left","700px");
                        },50);
                    }
                    window.setTimeout(function(){
                        if(container.queue("fx").lenght > 30){
                            container.clearQueue();
                        }
                        scrolleri();
                    },598);
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

                function override(){
                    check_scroller("true",0);
                    window.setTimeout(function(){
                        override();
                    },20000);
                }

                function sleep(delay){
                    var start = new Date().getTime();
                    while (new Date().getTime() < start + delay);
                }

                function drawTimer(percent,index){
       				$(\'#\'+index+\'.timer\').html(\'<div id="\'+index+\'" class="percent"></div><div id="slice-\'+index+\'"\'+(percent > 50?\' class="gt50"\':\'\')+\'><div id="pie-\'+index+\'" class="pie"></div>\'+(percent > 50?\'<div id="fillpie-\'+index+\'" class="pie fill"></div>\':\'\')+\'</div>\');
    				var deg = 360/100*percent;
    				$(\'#slice-\'+index+\' .pie\').css({
    					\'-moz-transform\':\'rotate(\'+deg+\'deg)\',
    					\'-webkit-transform\':\'rotate(\'+deg+\'deg)\',
    					\'-o-transform\':\'rotate(\'+deg+\'deg)\',
    					\'transform\':\'rotate(\'+deg+\'deg)\'
    				});
    				$(\'#\'+index+\'.percent\').html(Math.round(percent)+\'%\');
    			}

                function check_scroller(override,spawn){
                    if(!override)
                        var override = false;
                    var container = $("#rullaaja");
                    fetch = \''.URL::base($this->request).'backend/\' + page+ \'/check_scroller/\' + override + \'/\';
                    $.getJSON(fetch, function(data) {
                        if(data.changed == true){
                            container.html(data.palautus);
                        }
                    });
                    if(spawn == 1){
                        window.setTimeout(function(){
                            check_scroller(0,1);
                        },5000);
                    }
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
                    fetch = \''.URL::base($this->request).'ajax/check/\';
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
                                                        $("#text_cont").show(\'blind\',300);
                                                        window.setTimeout(function(){
                                                            container.html(value.palautus);
                                                            $.each(value.pie,function(index2,value2){
                                                                if(value2){
                                                                    drawTimer(value2,index2);
                                                                }
                                                            });
                                                            window.setTimeout(function(){
                                                                container.show(\'clip\',300);
                                                            },100);
                                                        },300);
                                                        break;
                                                    case "twitter":
                                                        twiit = page;
                                                        container.hide("puff",300);
                                                        window.setTimeout(function(){
                                                            $("#text_cont").hide(\'blind\',300);
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
                                                                flowplayer("player", "'.URL::base($this->request).'flowplayer/flowplayer.swf", {
                                                                    clip : {
                                                                        autoPlay: true,
                                                                        autoBuffering: true,
                                                                        live:true,
                                                                        provider:\'influxis\'
                                                                    },
                                                                    plugins:{
                                                                        influxis:{
                                                                            url:\''.URL::base($this->request).'flowplayer/flowplayer.rtmp-3.2.3.swf\',
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
                                            scroller.html(value.palautus);
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
        </script>
    	';
        $this->view->login = "";
    	$this->view->text = "Tervetuloa seuraamaan Tracon VI:n inforuutua.<br><br>Odota hetki, synkronoidutaan inforuutujärjestelmään.";
		$this->response->body($this->view->render());
	}

} // End Frontend

?>