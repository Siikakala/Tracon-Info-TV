<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
* Admin-controlleri hallintapuolelle..
*
* @author Miika Ojamo <miika@vkoski.net>
*/
class Controller_Admin extends Controller{

    public function before(){
        $db = Database::instance();
    	$this->session = Session::instance();
    	if($this->request->action() != "ajax"){//ei turhaan alusteta viewiä ajax-responselle
           	$this->view = new View('admin');
        	$this->view->header = new view('admin_header');
        	$this->view->content = new view ('admin_content');
        	$this->view->footer = new view('admin_footer');
        	$this->view->header->title = "Hallintapaneeli";
        	$this->view->header->css = html::style('css/admin_small.css');
        	$this->view->header->css .= html::style('css/ui-darkness/jquery-ui-1.8.12.custom.css');
        	$this->view->header->js = '<script type="text/javascript" src="'.URL::base($this->request).'jquery/jquery-1.5.1.min.js"></script>';
        	$this->view->header->js .= "\n".'<script type="text/javascript" src="'.URL::base($this->request).'jquery/jquery-ui-1.8.12.custom.min.js"></script>';
            $this->view->header->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."jquery/jquery.metadata.js\"></script>";
            if($this->session->get("show_tv",false) === false){
                $this->get_set();
            }
            if($this->session->get("show_tv") == 1) $nayta = "true";
            else $nayta = "false";
            $this->view->header->js .= "\n<script type=\"text/javascript\">
                                        $(function() {
                                            $( 'button, input:submit' ).button();
                                            if(".$nayta."){
                                                $(\"#show_stream\").show(\"medium\");
                                            }
                                        });

                                        function check_show(show){
                                            if(show == 1){
                                                $(\"#show_stream\").show(\"medium\");
                                            }else{
                                                $(\"#show_stream\").hide(\"medium\");
                                            }
                                        }

                                        function show_save(){
                                            var container = $(\"#show_feed\");
                                            container.hide(0);
                                            var nayta = $(\"#show_tv\").val();
                                            var stream = $(\"#show_stream\").val();
                                            fetch = '".URL::base($this->request)."admin/ajax/tv/'
                                            $.post(fetch, { \"nayta\": nayta, \"stream\": stream }, function(data){
                                                if(data.ret == true){
                                                    container.html(\"Muutettu.\");
                                                    $(\"#show_stream\").removeClass(\"new\");
                                                    $(\"#show_tv\").removeClass(\"new\");
                                                }else{
                                                    container.html(data.ret);
                                                }
                                            },\"json\");
                                            window.setTimeout(function(){
                                                container.show('drop', { direction:\"right\", distance: \"-50px\" },600);
                                                window.setTimeout(function(){
                                                    container.hide('drop', { direction:\"right\", distance: \"80px\" },1400);
                                                },598);
                                            },200);
                                        }
                                          </script>
                                        ";
        	$this->view->header->login = "";
        	$this->view->header->show = "";
            if($this->session->get('logged_in') && $this->request->action() != 'logout'){
                if($this->session->get("show_tv"))
                    $show = $this->session->get("show_tv");
                else
                    $show = false;
                if($this->session->get("show_stream"))
                    $striim = $this->session->get("show_stream");
                else
                    $striim = false;
                $this->view->header->login = "Kirjautunut käyttäjänä: ".$this->session->get('user')."<br />".html::file_anchor('admin/logout','Kirjaudu ulos');
                $this->view->header->show = "Näytä:".form::select("show",array("Diashow","Streami"),$show,array("id"=>"show_tv","onchange"=>"check_show(this.value);$(this).addClass(\"new\");$(\"#show_stream\").addClass(\"new\");")).form::select("streams",$this->get_streams(),$striim,array("id"=>"show_stream","onchange"=>"$(this).addClass(\"new\");")).form::button("apply","Vaihda",array("onclick"=>"show_save();"))."<br/><span id=\"show_feed\"></span>";
            }
        }
    }


    /**
    * Default-metodi, tarkistaa että onko kirjauduttu sisään, jollei, näyttää kirjautumisformin, muussa tapauksessa forwardaa eteenpäin
    */
    public function action_index(){
    	if(!$this->session->get('logged_in')){
			$this->view->content->text = "<h2>Kirjaudu sisään</h2>";
    	    $this->view->content->text .= form::open('admin/login');
			$this->view->content->text .= "<table><tr><td>";
			$this->view->content->text .= form::label('user','Käyttäjätunnus:')."</td><td>";
    	    $this->view->content->text .= form::input('user',null,array('id'=>'user'))."</td></tr><tr><td>";
			$this->view->content->text .= form::label('pass','Salasana:')."</td><td>";
			$this->view->content->text .= form::password('pass',null,array('id'=>'pass'))."</td></tr><tr><td></td><td>";
			$this->view->content->text .= form::submit('submit','Kirjaudu');
    	    if(isset($_GET['return'])) $this->view->content->text .= form::hidden('return',$_GET['return']);
			$this->view->content->text .= "</td></tr></table>";
    	    $this->view->content->text .= form::close();
    	    $this->view->content->links = "";
    	    $this->response->body($this->view->render());
    	}else
            if(isset($_GET['return'])) $this->request->redirect($_GET['return']);
        	else $this->request->redirect('admin/face');

    }

    public function action_login(){
        $auth = new Model_Authi();
        $login = $auth->auth($_POST['user'],$_POST['pass']);
        if($login !== false){
            $this->session->set('logged_in',true);
            $this->session->set('level',$login);
            $this->session->set('user',$_POST['user']);
            if(isset($_POST['return'])) $this->request->redirect($_POST['return']);
            else $this->request->redirect('admin/face');
        }else{
            $this->view->content->text = "<p class=\"error\">Väärä käyttäjätunnus tai salasana!</p>";
            $this->view->content->links = "";
        }
        $this->response->body($this->view->render());
    }

    public function action_face($page = null,$param1 = null, $param2 = null){
    	if(!$this->session->get('logged_in')){
			$this->request->redirect('admin/?return='.$this->request->uri()); // Ohjataan suoraan kirjautumiseen, mikäli yritetään avata tämä sivu ei-kirjautuneena
    	}elseif($this->session->get('level',0) < 1){//2. parametri = defaulttaa nollaks
        	$this->view->content->text = '<p>Valitettavasti sinulla ei ole ylläpito-oikeuksia.</p>';
        	$this->view->content->links = "";
    	}else{

    	    //<linkkipalkki>
			$this->view->content->links = "\n<ul>";
    	    $this->view->content->links .= "\n<li>".html::file_anchor('admin/face/scroller','Scroller')."</li>";
    	    $this->view->content->links .= "\n<li>".html::file_anchor('admin/face/rulla','Rulla-hallinta')."</li>";
    	    $this->view->content->links .= "\n<li>".html::file_anchor('admin/face/dia','Diojen hallinta')."</li>";
    	    $this->view->content->links .= "\n<li>".html::file_anchor('admin/face/streams','Streamit')."</li>";
            $this->view->content->links .= "\n</ul><br /><ul>";
    	    $this->view->content->links .= "\n<li>".html::file_anchor('admin/logout','Kirjaudu ulos')."</li>";
    	    $this->view->content->links .= "\n<li>".html::file_anchor('','Info-TV')."</li>";
			$this->view->content->links .= "\n</ul>";
    	    //</linkkipalkki>


    	    switch($page){
    	        case "scroller":
        	        $this->scroller($param1);
        	        break;
    	        case "rulla":
            		$this->rulla($param1);
            		break;
            	case "dia":
            		$this->dia($param1);
            		break;
            	case "streams":
                	$this->streams($param1);
                	break;
            	default:
               		$this->view->content->text = "<p>Olet nyt Info-TV:n hallintapaneelissa. Ole hyvä ja valitse toiminto valikosta.</p>";
               		break;

    	    }
    	}
    	$this->response->body($this->view->render());
	}

	private function scroller($param1){
        $this->view->header->js .= '
            <script type="text/javascript">
                var id = 500;

                function addrow(){
                    $(\'#scroller\').append(\'<tr class="new"><td><input type="text" name="pos-\' + id + \'" value="" size="1" /></td><td><input type="text" name="text-\' + id + \'" value="" size="45" /></td><td><input name="hidden-\' + id + \'" value="1" type="checkbox"></td><td style="border:0px; border-bottom-style: none; padding: 0px; width:2px;"><a href="javascript:;" class="del" >X</a></td></tr>\');
                    id = id + 1;
                }

                $("#form").submit(function(e) {
                    e.preventDefault();
                });

                function save(){
                    var container = $("#feedback");
                    container.hide(0);
                    fetch = \''.URL::base($this->request).'admin/ajax/scroller_save/\'
                    $.post(fetch,$("#form").serialize(),function(data) {
                        $.each(data,function(key,val){
                            container.html(val);
                            container.show(\'drop\',{ direction: "right", distance: "-50px" },500);
                        });
                    },"json");
                    window.setTimeout(function(){
                        container.hide(\'drop\',{ direction: "right", distance: "100px" },1000);
                    },4000);
                    window.setTimeout(function(){
                        $("#formidata").hide("explode",{pieces:8},1000);
                    },2500);
                    window.setTimeout(function(){
                        refresh_data();
                    },3500);
                    return false;
                }

                function refresh_data(){
                    var container = $("#formidata");
                    fetch = \''.URL::base($this->request).'admin/ajax/scroller_load/\'
                    $.getJSON(fetch, function(data) {
                        container.html(data.data);
                    });
                    container.show("clip",1000);
                }

                function dele(row){
                    var container = $("#feedback");
                    container.hide(0);
                    var sure = confirm("Oletko varma, että haluat poistaa tämän scrollerinpalan?")
                    if(sure){
                        fetch = \''.URL::base($this->request).'admin/ajax/scroller_delete/\' + row;
                        $.getJSON(fetch, function(data) {
                            if(data.ret == true){
                                $("."+row).remove();
                                container.html("Palanen poistettu. Muista tallentaa muutoksesi!");
                                container.show(\'drop\',{ direction: "right", distance: "-50px" },500);
                            }
                        });
                        window.setTimeout(function(){
                            container.hide(\'drop\',{ direction: "right", distance: "100px" },1000);
                        },4000);
                    }
                }

                $(".del").live({
                        mouseenter: function() {
                            $(this).addClass(\'hover\');
                        },
                        mouseleave: function(){
                            $(this).removeClass(\'hover\');
                        },
                        click: function(){
                            if(!$(this).hasClass("ignore")){
                                $(this).parent().parent().remove();
                                var container = $("#feedback");
                                container.hide(0);
                                container.html("Rivi poistettu. Muista tallentaa muutoksesi!");
                                container.show(\'drop\',{ direction: "right", distance: "-50px" },500);
                                window.setTimeout(function(){
                                    container.hide(\'drop\',{ direction: "right", distance: "100px" },1000);
                                },4000);
                            }
                        }
                    });
            </script>';

    	$query = DB::query(Database::SELECT,
                            "SELECT    scroll_id as \"id\"".
                            "         ,pos ".
                            "         ,text ".
                            "         ,hidden ".
                            "FROM      scroller ".
                            "ORDER BY  pos"
                            )->execute();
        if($query->count() > 0)
            $result = $query->as_array();
        else
            $result = false;

        $this->view->content->text  = "<h2>Scroller-hallinta</h2><div id=\"formidata\">";
        $this->view->content->text .= form::open(null, array("onsubmit" => "return false;", "id" => "form"));
        $this->view->content->text .= "<table id=\"scroller\" class=\"stats\" style=\"border-right:0px; border-top:0px; border-bottom:0px;\"><tr><th>Kohta</th><th>Teksti</th><th>Piilotettu?</th></tr>";

        if($result) foreach($result as $row=>$data){
            $this->view->content->text .= "<tr class=\"".$data["id"]."\"><td>".form::input('pos-'.$data["id"],$data["pos"],array("size"=>"1","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::input('text-'.$data["id"],$data["text"],array("size"=>"45","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::checkbox('hidden-'.$data["id"],1,(boolean)$data["hidden"],array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del ignore\" onclick=\"dele(".$data["id"].")\" >X</a></td></tr>";
        }
        $this->view->content->text .= "</table>".form::close()."</div>".form::button("moar","Lisää rivi",array("id"=>"lisarivi","onclick"=>"addrow();"))."<br/><br/><p><strong>MUISTA TALLENTAA MUUTOKSESI!</strong></p><p><strong>HUOM!</strong><ul><li>Numerointi voi hyppiä numeroiden yli, ne näytetään pienimmästä suurimpaan.<li>Tyhjiä rivejä ei huomioida tallennuksessa.<li><strong>Älä käytä kohtaa 0!</strong> Rivi ei tallennu tällöin.</ul></p>".
                                    form::button('submit','Tallenna',array("onclick" => "return save();"))."<div id=\"feed_cont\" style=\"min-height:20px\";><div id=\"feedback\" style=\"display:none;\"></div></div>";

    }

    private function rulla($param1){
        $this->view->header->js .= '
            <script type="text/javascript">
                var id = 500;

                function addrow(){
                    fetch = \''.URL::base($this->request).'admin/ajax/rulla_row/\' + id
                    $.getJSON(fetch, function(data) {
                        $(\'#rulla\').append(data.ret);
                    });
                    id = id + 1;

                }

                $("#form").submit(function(e) {
                    e.preventDefault();
                });

                function save(){
                    var container = $("#feedback");
                    container.hide(0);
                    fetch = \''.URL::base($this->request).'admin/ajax/rulla_save/\'
                    $.post(fetch,$("#form").serialize(),function(data) {
                        $.each(data,function(key,val){
                            container.html(val);
                            container.show(\'drop\',{ direction: "right", distance: "-50px" },500);
                        });
                    },"json");
                    window.setTimeout(function(){
                        container.hide(\'drop\',{ direction: "right", distance: "100px" },1000);
                    },4000);
                    window.setTimeout(function(){
                        $("#formidata").hide("explode",{pieces:8},1000);
                    },2500);
                    window.setTimeout(function(){
                        refresh_data();
                    },3470);
                    return false;
                }

                function refresh_data(post){
                    var container = $("#formidata");
                    fetch = \''.URL::base($this->request).'admin/ajax/rulla_load/\'
                    $.getJSON(fetch,function(data) {
                        container.html(data.data);
                    });
                    window.setTimeout(function(){
                        container.show("clip",1000);
                    },100);
                }

                function dele(row){
                    var container = $("#feedback");
                    container.hide(0);
                    var sure = confirm("Oletko varma, että haluat poistaa tämän dian diashowsta?")
                    if(sure){
                        fetch = \''.URL::base($this->request).'admin/ajax/rulla_delete/\' + row;
                        $.getJSON(fetch, function(data) {
                            if(data.ret == true){
                                $("."+row).remove();
                                container.hide(0);
                                container.html("Rivi poistettu. Muista tallentaa muutoksesi!");
                                container.show(\'drop\',{ direction: "right", distance: "-50px" },500);
                                window.setTimeout(function(){
                                    container.hide(\'drop\',{ direction: "right", distance: "100px" },1000);
                                },4000);
                            }else{
                                container.html("Rivin poisto ei tapahtunut ongelmitta. Lataa sivu uudelleen ja yritä uudelleen.");
                            }
                        });

                    }
                }

                $(".del").live({
                        mouseenter: function() {
                            $(this).addClass(\'hover\');
                        },
                        mouseleave: function(){
                            $(this).removeClass(\'hover\');
                        },
                        click: function(){
                            if(!$(this).hasClass("ignore")){
                                $(this).parent().parent().remove();
                                var container = $("#feedback");
                                container.hide(0);
                                container.html("Rivi poistettu. Muista tallentaa muutoksesi!");
                                container.show(\'drop\',{ direction: "right", distance: "-50px" },500);
                                window.setTimeout(function(){
                                    container.hide(\'drop\',{ direction: "right", distance: "100px" },1000);
                                },4000);
                            }
                        }
                    });
            </script>';
    	$query = DB::query(Database::SELECT,
                            "SELECT    rul_id as \"id\"".
                            "         ,pos ".
                            "         ,type ".
                            "         ,time ".
                            "         ,selector ".
                            "         ,hidden ".
                            "FROM      rulla ".
                            "ORDER BY  pos"
                            )->execute();
        if($query->count() > 0)
            $result = $query->as_array();
        else
            $result = false;

    	$query2 = DB::query(Database::SELECT,
                            "SELECT    dia_id as \"id\"".
                            "         ,tunniste ".
                            "FROM      diat ".
                            "ORDER BY  tunniste"
                            )->execute();
        if($query2->count() > 0)
            $result2 = $query2->as_array();
        else
            $result2 = false;

        $vaihtehdot = array();
        $vaihtoehdot[0] = "twitter";
        if($result2)foreach($result2 as $row => $data){
            $vaihtoehdot[$data['id']] = $this->utf8($data['tunniste']);
        }else
            $vaihtoehdot = false;

        $this->view->content->text  = "<h2>Rulla-hallinta</h2><p>aka. Diashow-hallinta</p><div id=\"formidata\">";
        $this->view->content->text .= form::open(null, array("onsubmit" => "return false;", "id" => "form"));
        $this->view->content->text .= "<table id=\"rulla\" class=\"stats\" style=\"border-right:0px; border-top:0px; border-bottom:0px;\"><tr><th>Kohta</th><th>Dia</th><th>Aika (~s)</th><th>Piilotettu?</th></tr>";



        if($result) foreach($result as $row=>$data){
            if($data['type'] == 2)
                $selector = 0;
            else
                $selector = $data['selector'];
            $this->view->content->text .= "<tr class=\"".$data["id"]."\"><td>".form::input('pos-'.$data["id"],$data["pos"],array("size"=>"1","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::select('text-'.$data["id"],$vaihtoehdot,$selector,array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::select('time-'.$data["id"],Date::seconds(1,1,121),$data["time"],array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::checkbox('hidden-'.$data["id"],1,(boolean)$data["hidden"],array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del ignore\" onclick=\"dele(".$data["id"].")\" >X</a></td></tr>";
        }
        $this->view->content->text .= "</table>".form::close()."</div>".form::button("moar","Lisää rivi",array("id"=>"lisarivi","onclick"=>"addrow();"))."<br/><br/><p><strong>MUISTA TALLENTAA MUUTOKSESI!</strong></p><p><strong>HUOM!</strong><ul><li>Numerointi voi hyppiä numeroiden yli, ne näytetään pienimmästä suurimpaan. <li><strong>Älä käytä kohtaa 0!</strong> Rivi ei tallennu tällöin.</ul></p>".
                                    form::button('submit','Tallenna',array("onclick" => "return save();"))."<div id=\"feed_cont\" style=\"min-height:20px;\"><div id=\"feedback\" style=\"display:none;\"></div></div>";
    }

    private function dia($param1){
        $this->view->header->js .= $this->tinymce("admin-tinymce.css");
        $this->view->header->js .= '
            <script type="text/javascript">
                var gid = 0;

                function load(id){

                    var container = $("#edit");
                    var identti = $("#ident_");
                    var ident = $("#ident");
                    container.hide(0);
                    identti.hide(0);
                    if(id == 0){
                        container.html("Valitse jokin dia. Mikäli haluamasi dia ei ole listauksessa, lataa sivu uudelleen.");
                        container.show("medium");
                    }else{
                        container.html("Ladataan dia id "+id+", odota hetki.");
                        container.show("medium");
                        fetch = \''.URL::base($this->request).'admin/ajax/dia_load/\' + id;
                        $.getJSON(fetch, function(data) {
                            if(data.ret){
                                gid = id;
                                container.hide(500);
                                window.setTimeout(function(){
                                    container.html(data.ret);
                                    ident.val(data.tunniste);
                                    tinymce_setup();
                                    container.show("medium");
                                    identti.show("medium");
                                },700);
                            }
                        });
                    }
                }

                function uusi(){
                    var container = $("#edit");
                    var identti = $("#ident_");
                    var ident = $("#ident");
                    container.hide(0);
                    identti.hide(0);
                    container.html(\'<br/>'.form::textarea("loota-new","",array("id"=>"loota","class"=>"tinymce")).'\');
                    ident.val("");
                    ident.removeClass("new");
                    tinymce_setup();
                    $("#loota_cancel_voice").html("Poista dia");
                    container.show("medium");
                    identti.show("medium");
                    gid = 0;
                    return false;
                }

                function tinymce_tallenna(){
                    var m = $("#loota");
                    var cont = m.tinymce().getContent();
                    var ident = $("#ident").val();
                    if(ident == ""){
                        alert("Et määritellyt dialle tunnistetta!");
                        $("#ident").addClass("new");
                    }else{
                        m.tinymce().setProgressState(1); // Show progress
                        fetch = \''.URL::base($this->request).'admin/ajax/dia_save/\'
                        $.post(fetch, { "cont": cont, "ident": ident, "id": gid }, function(data){
                            if(data.ret == true){
                                //kaikki ok.
                            }else{
                                alert(data.ret);
                            }
                            window.setTimeout(function(){
                                m.tinymce().setProgressState(0); // Hide progress
                                $("#ident").removeClass("new");
                            },500);
                        },"json");
                    }
                }

                function tinymce_poista(){
                    if(gid == 0){
                        $("#loota").html("");
                        $("#ident").val("");
                    }else{
                        var sure = confirm("Oletko varma että haluat poistaa dian " + $("#ident").val() + "?");
                        if(sure){
                            fetch = \''.URL::base($this->request).'admin/ajax/dia_delete/\' + gid;
                            $.getJSON(fetch, function(data) {
                                if(data.ret == true){
                                    $("#edit").hide(500);
                                    $("#ident_").hide(500);
                                    window.setTimeout(function(){
                                        $("#edit").html("Dia poistettu.");
                                        $("#edit").show("medium");
                                        $("#ident").val("");
                                    },500);
                                }else{
                                    alert(data.ret);
                                }
                            });
                        }
                    }
                }

            </script>
        ';
        $this->view->content->text = "<h2>Dia-hallinta</h2>";

        $query = DB::query(Database::SELECT,
                            "SELECT    dia_id ".
                            "         ,tunniste ".
                            "         ,data ".
                            "FROM      diat ".
                            "ORDER BY  dia_id"
                            )->execute();
        $result[0] = "";
        if($query->count() > 0)
            foreach($query as $row => $data){
                $result[$data['dia_id']] = $this->utf8($data['tunniste']);
            }
        else
            $result = false;

        $this->view->content->text .= "<div id=\"select\"><p>Valitse muokattava dia: ".form::select("dia",$result,0,array("onchange"=>"load(this.value)","id"=>"dia_sel"))." tai ".form::button("uusi","Luo uusi",array("onclick"=>"return uusi();"))."&nbsp;&nbsp;&nbsp;&nbsp;<span id=\"ident_\" style=\"display:none;\">Tunniste:".form::input("ident","",array("id"=>"ident"))."</span></p></div><div id=\"edit\" style=\"display:none;\"></div>";
        $this->view->content->text .= "<p>Muista käyttää esikatselutoimintoa ennen tallennusta. Tallennus tapahtuu levykkeestä, esikatselu sen oikealla puolella olevasta napista!<br/><strong>MUISTA TALLENTAA MUUTOKSESI!</strong></p>";
    }

    private function streams($param1){
        $this->view->header->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."flowplayer/flowplayer-3.2.6.min.js\"></script>";
        $this->view->header->js .= '
            <script type="text/javascript">
                var id = 500;

                function load(id){
                    var container = $("#stream_content");
                    container.hide(700);

                    window.setTimeout(function(){
                        container.html(\'<a href="\' + $("#"+id).val() + \'" style="display:block;width:700px;height:390px;" id="player"></a><br/><a href="javascript:;" onclick="$(\"#stream_content\"").hide(700)">[Sulje]</a>\');

                        window.setTimeout(function(){
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
                                        netConnectionUrl:$("#"+id).val()
                                    },
                                    controls: null
                                }
                            });
                        },700);
                        container.show(700);
                    },1600);
                }

                function addrow(){
                    $(\'#streamit\').append(\'<tr class="new"><td><input type="text" name="ident-\' + id + \'" value="" size="15" /></td><td><input type="text" name="url-\' + id + \'" value="" size="35" id="\' + id + \'" /></td><td><input name="jarkka-\' + id + \'" size="1" type="text"></td><td style="border:0px; border-bottom-style: none; padding: 0px; width:2px;"><a href="javascript:;" class="del" >X</a></td><td style="border:0px; border-bottom-style: none; padding: 0px;"><a href="javascript:;" onclick="load(\'+id+\');">&nbsp;Esikatsele</a></td></tr>\');
                    id = id + 1;
                }

                $(".del").live({
                    mouseenter: function() {
                        $(this).addClass(\'hover\');
                    },
                    mouseleave: function(){
                        $(this).removeClass(\'hover\');
                    },
                    click: function(){
                        if(!$(this).hasClass("ignore")){
                            $(this).parent().parent().remove();
                            var container = $("#feedback");
                            container.hide(0);
                            container.html("Rivi poistettu. Muista tallentaa muutoksesi!");
                            container.show(\'drop\',{ direction: "right", distance: "-50px" },500);
                            window.setTimeout(function(){
                                container.hide(\'drop\',{ direction: "right", distance: "100px" },1000);
                            },4000);
                        }
                    }
                });
            </script>
            ';

        $this->view->content->text = "<h2>Stream-hallinta</h2>";
        $query = DB::query(Database::SELECT,
                            "SELECT    stream_id ".
                            "         ,tunniste ".
                            "         ,url ".
                            "         ,jarjestys ".
                            "FROM      streamit ".
                            "ORDER BY  jarjestys"
                            )->execute();
        if($query->count() > 0){
            $result = $query->as_array();
            $count = $query->count() + 1;
        }else{
            $result = false;
            $count = 0;
        }

        $this->view->content->text .= "<div id=\"formidata\">";
        $this->view->content->text .= form::open(null, array("onsubmit" => "return false;", "id" => "form"));
        $this->view->content->text .= "<table id=\"streamit\" class=\"stats\" style=\"border-right:0px; border-top:0px; border-bottom:0px;\"><tr><th>Streamin tunniste</th><th>URL</th><th>Järjestysnro</th></tr>";

        if($result) foreach($result as $row => $data){
            $this->view->content->text .= "<tr><td>".form::input("ident-".$data['stream_id'],$data['tunniste'],array("class" => "tunniste","size" => "15","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::input("url-".$data['stream_id'],$data['url'],array("id" => $data['stream_id'],"class" => "url","size" => "35","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::input("jarkka-".$data['stream_id'],$data['jarjestys'],array("size" => "1", "onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del ignore\" >X</a></td><td style=\"border:0px; border-bottom-style: none; padding: 0px;\"><a href=\"javascript:;\" onclick=\"load(".$data['stream_id'].");\">&nbsp;Esikatsele</a></td></tr>";
        }

        $this->view->content->text .= "</table>".form::close()."</div><p>".form::button("moar","Lisää rivi",array("id"=>"lisarivi","onclick"=>"addrow();")).form::button("saev","Tallenna",array("id"=>"saev","onclick" => "save();"))."<br/><br/>Esikatselu:</p><div id=\"stream_content\" style=\"display:none;\"></div>";
    }

    private function get_streams(){
        $query = DB::query(Database::SELECT,
                            "SELECT    stream_id ".
                            "         ,tunniste ".
                            "FROM      streamit ".
                            "ORDER BY  jarjestys "
                            )->execute();
        $ret = array();
        foreach($query as $row){
            $ret[$row['stream_id']] = $row['tunniste'];
        }
        return $ret;
    }

    public function action_ajax($param1 = null,$param2 = null){
    	$return = "";
    	//if($this->request->param($param1)) $param1 = $this->request->param($param1);
    	if($this->session->get('logged_in') and $this->session->get('level') > 0){
        	switch($param1){
                case "scroller_save":
                    $post = $_POST;
                    $data = array();
                    $err = " ";
                    foreach($post as $key => $value){
                        $parts = explode("-",$key);
                        $rivi = $parts[1];
                        $data[$rivi][$parts[0]] = $value;
                    }
                    foreach($data as $row => $datat){
                        if(empty($datat["text"]) && empty($datat["pos"])){
                        }elseif(empty($datat["text"]) || empty($datat["pos"])){
                            $err .= "Jokin kenttä jäi täyttämättä. Kyseisen rivin tietoja <strong>EI</strong> ole tallennettu.";
                        }else{
                            if(!isset($datat["hidden"]))
                                $datat["hidden"] = false;
                            if($row >= 0 && $row < 500){
                                $query = DB::query(Database::UPDATE,
                                                    "UPDATE  scroller ".
                                                    "SET     pos = :pos ".
                                                    "       ,text = :text ".
                                                    "       ,hidden = :hidden ".
                                                    "WHERE   scroll_id = :row"
                                                    );
                                $query->parameters(array(":pos"    => $datat["pos"],
                                                         ":text"   => $datat["text"],
                                                         ":hidden" => $datat["hidden"],
                                                         ":row"    => $row
                                                         ));
                                $result = $query->execute();
                            }elseif($row >= 500){
                                $query = DB::query(Database::INSERT,
                                                    "INSERT INTO scroller ".
                                                    "           (pos ".
                                                    "           ,text ".
                                                    "           ,hidden ".
                                                    "           ) ".
                                                    "VALUES     (:pos ".
                                                    "           ,:text ".
                                                    "           ,:hidden ".
                                                    "           )"
                                                    );
                                $query->parameters(array(":pos"    => $datat["pos"],
                                                         ":text"   => $datat["text"],
                                                         ":hidden" => $datat["hidden"]
                                                         ));
                                $result = $query->execute();
                            }
                        }
                    }
                    $return = array("ret"=>"Scroller päivitetty.$err Odota hetki, päivitetään listaus...");
                    break;
                case "scroller_load":
                	$query = DB::query(Database::SELECT,
                                        "SELECT    scroll_id as \"id\"".
                                        "         ,pos ".
                                        "         ,text ".
                                        "         ,hidden ".
                                        "FROM      scroller ".
                                        "ORDER BY  pos"
                                        )->execute();
                    if($query->count() > 0)
                        $result = $query->as_array();
                    else
                        $result = false;
                    $text = form::open(null, array("onsubmit" => "return false;", "id" => "form"));
                    $text .= "<table id=\"scroller\" class=\"stats\" style=\"border-right:0px; border-top:0px; border-bottom:0px;\"><tr><th>Kohta</th><th>Teksti</th><th>Piilotettu?</th></tr>";

                    if($result) foreach($result as $row=>$data){
                        $text .= "<tr class=\"".$data["id"]."\"><td>".form::input('pos-'.$data["id"],$data["pos"],array("size"=>"1","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::input('text-'.$data["id"],$data["text"],array("size"=>"45","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::checkbox('hidden-'.$data["id"],1,(boolean)$data["hidden"],array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del ignore\" onclick=\"dele(".$data["id"].")\" >X</a></td></tr>";
                    }
                    $text .= "</table>".form::close();
                    $return = array("data" => $text);
                    break;
                case "scroller_delete":
                    if(!$param2){
                        $ret = false;
                    }else{
                        $query = DB::query(Database::DELETE,
                                            "DELETE FROM scroller ".
                                            "WHERE       scroll_id = :id"
                                            );
                        $query->param(":id",$param2)->execute();
                        $ret = true;
                    }
                    $return = array("ret" => $ret);
                    break;
                case "rulla_row":
                	$id = $param2;
                	$text = "";
                    $query = DB::query(Database::SELECT,
                                        "SELECT    pos ".
                                        "FROM      rulla ".
                                        "WHERE     pos = (SELECT MAX(pos) FROM rulla)"
                                        )->execute();
                    if($query->count() > 0)
                        $result = $query->as_array();
                    else
                        $result = false;

                	$query2 = DB::query(Database::SELECT,
                                        "SELECT    dia_id as \"id\"".
                                        "         ,tunniste ".
                                        "FROM      diat ".
                                        "ORDER BY  tunniste"
                                        )->execute();
                    if($query2->count() > 0)
                        $result2 = $query2->as_array();
                    else
                        $result2 = false;

                    $vaihtehdot = array();
                    $vaihtoehdot[0] = "twitter";
                    if($result2) foreach($result2 as $row => $data){
                        $vaihtoehdot[$data['id']] = $this->utf8($data['tunniste']);
                    }else
                        $vaihtoehdot = false;

                    if(!$result){
                        $pos = $id - 499;
                    }else{
                        $pos = $id - 499 + $result[0]["pos"];
                    }
                    $text = "<tr class=\"new\" new=\"$id\"><td>".form::input('pos-'.$id,$pos,array("size"=>"1"))."</td><td>".form::select('text-'.$id,$vaihtoehdot,1)."</td><td>".form::select('time-'.$id,Date::seconds(1,1,121),5)."</td><td>".form::checkbox('hidden-'.$id,1,false)."</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del\" >X</a></td></tr>";
                    $return = array("ret" => $text);
                    break;
                case "rulla_save":
                    $post = $_POST;
                    $data = array();
                    $err = " ";
                    foreach($post as $key => $value){
                        $parts = explode("-",$key);
                        $rivi = $parts[1];
                        $data[$rivi][$parts[0]] = $value;
                    }
                    foreach($data as $row => $datat){
                        if(empty($datat["pos"])){
                            $err .= "Jokin dian positio jäi täyttämättä. Kyseisen dian tietoja <strong>EI</strong> ole tallennettu.";
                        }else{
                            if(!isset($datat["hidden"]))
                                $datat["hidden"] = false;
                            if($row >= 0 && $row < 500){
                                $query = DB::query(Database::UPDATE,
                                                    "UPDATE  rulla ".
                                                    "SET     pos = :pos ".
                                                    "       ,selector = :text ".
                                                    "       ,time = :time".
                                                    "       ,hidden = :hidden ".
                                                    "WHERE   rul_id = :row"
                                                    );
                                $query->parameters(array(":pos"    => $datat["pos"],
                                                         ":text"   => $datat["text"],
                                                         ":time"   => $datat["time"],
                                                         ":hidden" => $datat["hidden"],
                                                         ":row"    => $row
                                                         ));
                                $result = $query->execute();
                            }elseif($row >= 500){
                                if($datat["text"] == 0)
                                    $type = 2;
                                else
                                    $type = 1;
                                $query = DB::query(Database::INSERT,
                                                    "INSERT INTO rulla ".
                                                    "           (pos ".
                                                    "           ,selector ".
                                                    "           ,time".
                                                    "           ,type".
                                                    "           ,hidden".
                                                    "           ) ".
                                                    "VALUES     (:pos ".
                                                    "           ,:text ".
                                                    "           ,:time".
                                                    "           ,$type".
                                                    "           ,:hidden".
                                                    "           )"
                                                    );
                                $query->parameters(array(":pos"    => $datat["pos"],
                                                         ":text"   => $datat["text"],
                                                         ":time"   => $datat["time"],
                                                         ":hidden" => $datat["hidden"]
                                                         ));
                                $result = $query->execute();
                            }
                        }
                    }
                    $return = array("ret"=>"Diashow päivitetty.$err Odota hetki, päivitetään listaus...");
                    break;
                case "rulla_load":
                	$query = DB::query(Database::SELECT,
                                        "SELECT    rul_id as \"id\"".
                                        "         ,pos ".
                                        "         ,type ".
                                        "         ,time ".
                                        "         ,selector ".
                                        "         ,hidden ".
                                        "FROM      rulla ".
                                        "ORDER BY  pos"
                                        )->execute();
                    if($query->count() > 0)
                        $result = $query->as_array();
                    else
                        $result = false;

                	$query2 = DB::query(Database::SELECT,
                                        "SELECT    dia_id as \"id\"".
                                        "         ,tunniste ".
                                        "FROM      diat ".
                                        "ORDER BY  tunniste"
                                        )->execute();
                    if($query2->count() > 0)
                        $result2 = $query2->as_array();
                    else
                        $result2 = false;

                    $vaihtehdot = array();
                    $vaihtoehdot[0] = "twitter";
                    if($result2) foreach($result2 as $row => $data){
                        $vaihtoehdot[$data['id']] = $this->utf8($data['tunniste']);
                    }else
                        $vaihtoehdot = false;
                    $text = form::open(null, array("onsubmit" => "return false;", "id" => "form"))."<table id=\"rulla\" class=\"stats\" style=\"border-right:0px; border-top:0px; border-bottom:0px;\"><tr><th>Kohta</th><th>Dia</th><th>Aika (~s)</th><th>Piilotettu?</th></tr>";

                    if($result) foreach($result as $row=>$data){
                        if($data['type'] == 2)
                            $selector = 0;
                        else
                            $selector = $data['selector'];
                        $text .= "<tr class=\"".$data["id"]."\"><td>".form::input('pos-'.$data["id"],$data["pos"],array("size"=>"1","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::select('text-'.$data["id"],$vaihtoehdot,$selector,array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::select('time-'.$data["id"],Date::seconds(1,1,121),$data["time"],array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::checkbox('hidden-'.$data["id"],1,(boolean)$data["hidden"],array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del ignore\" onclick=\"dele(".$data["id"].")\" >X</a></td></tr>";
                    }
                    $text .= "</table>".form::close();
                    $return = array("data"=>$text);
                    break;
                case "rulla_delete":
                    if(!$param2){
                        $ret = false;
                    }else{
                        $query = DB::query(Database::DELETE,
                                            "DELETE FROM rulla ".
                                            "WHERE       rul_id = :id"
                                            );
                        $query->param(":id",$param2)->execute();
                        $ret = true;
                    }
                    $return = array("ret" => $ret);
                    break;
                case "tv":
                    if($_POST['stream'] == "null"){
                        $return = array("ret" => "Streamia ei määritelty. Valitaan diashow.");
                        $query = DB::query(Database::UPDATE,
                                            "UPDATE   config ".
                                            "SET      value = 0 ".
                                            "WHERE    opt = 'show_tv'"
                                            )->execute();
                    }else{
                        $this->session->set("show_tv",$_POST['nayta']);
                        $this->session->set("show_stream",$_POST['stream']);
                        $query = DB::query(Database::UPDATE,
                                            "UPDATE   config ".
                                            "SET      value = :value ".
                                            "WHERE    opt = 'show_tv'"
                                            );
                        $query->param(":value",$_POST['nayta'])->execute();
                        $query = DB::query(Database::UPDATE,
                                            "UPDATE   config ".
                                            "SET      value = :value ".
                                            "WHERE    opt = 'show_stream'"
                                            );
                        $query->param(":value",$_POST['stream'])->execute();
                        $return = array("ret" => true);
                    }
                    break;
                case "dia_load":
                    if(!$param2){
                        $ret = false;
                    }else{
                        $query = DB::query(Database::SELECT,
                                            "SELECT   data ".
                                            "        ,tunniste ".
                                            "FROM     diat ".
                                            "WHERE    dia_id = :id"
                                            );
                        $query->param(":id",$param2);
                        $result = $query->execute();
                        if($result->count() > 0){
                            $data = $result->as_array();
                            $ret = "<br/>".form::textarea("loota-".$param2,$this->utf8($data[0]['data']),array("id"=>"loota","class"=>"tinymce"));
                            $tunniste = $this->utf8($data[0]['tunniste']);
                        }else{
                            $ret = false;
                            $tunniste = false;
                        }
                    }
                    $return = array("ret" => $ret,"tunniste" => $tunniste);
                    break;
                case "dia_save":
                    $post = $_POST;
                    if($post['id'] == 0){//uus
                        $tunniste = $post['ident'];
                        $data = $post['cont'];
                        $query = DB::query(Database::INSERT,
                                            "INSERT INTO diat ".
                                            "                 (tunniste ".
                                            "                 ,data ".
                                            "                 ) ".
                                            "VALUES           (:tunniste ".
                                            "                 ,:data ".
                                            "                 )"
                                            );
                        $query->parameters(array(":tunniste" => $tunniste,
                                                 ":data"     => $data
                                                 ));
                        $result = $query->execute();
                        $ret = true;
                    }elseif(!empty($post['id'])){//vanha
                        $tunniste = $post['ident'];
                        $data = $post['cont'];
                        $query = DB::query(Database::UPDATE,
                                            "UPDATE diat ".
                                            "SET    tunniste = :tunniste ".
                                            "      ,data = :data ".
                                            "WHERE  dia_id = :id"
                                            );
                        $query->parameters(array(":tunniste" => $tunniste,
                                                 ":data"     => $data,
                                                 ":id"       => $post['id']
                                                 ));
                        $result = $query->execute();
                        $ret = true;
                    }else{
                        //data ei tullu perille :|
                        $ret = "Tallennus epäonnistui.";
                    }
                    $return = array("ret" => $ret);
                    break;
                case "dia_delete":
                    if(!$param2){
                        $ret = false;
                    }else{
                        $q = DB::query(Database::SELECT,
                                        "SELECT rul_id ".
                                        "FROM   rulla ".
                                        "WHERE  selector = :id"
                                        );
                        $r = $q->param(":id",$param2)->execute();
                        if($r->count() > 0){
                            $ret = "Diaa käytetään vielä diashowssa. Poista dia sieltä ensin.";
                        }else{
                            $query = DB::query(Database::DELETE,
                                                "DELETE FROM diat ".
                                                "WHERE       dia_id = :id"
                                                );
                            $query->param(":id",$param2)->execute();
                            $ret = true;
                        }
                    }
                    $return = array("ret" => $ret);
                    break;
            }
    	}else{
        	if(empty($_SERVER['HTTP_REFERER'])) $referer = "";
        	else $referer = $_SERVER['HTTP_REFERER'];
            $ref = substr_replace(URL::base($this->request), "", $referer);
            $data = "<p>Sessio on vanhentunut. ".html::file_anchor('admin/?return='.$ref,'Kirjaudu uudelleen').", palaat takaisin tälle sivulle.</p>";
            $return = array("ret" => $data);
        }
        print json_encode($return);
    }

    public function action_logout(){
    	$this->session->destroy();
    	$this->view->content->text = "<p>Olet kirjautunut ulos.</p><p>".html::file_anchor('admin','Kirjaudu takaisin sisään').".</p>";
    	$this->view->content->links = "";
    	$this->response->body($this->view->render());
    }

    private function get_set(){
        //kaiva kannasta tiedot tämänhetkisestä tv:ssä pyörivästä setistä, ja tunge sessiomuuttujiin.
        $query = DB::query(Database::SELECT,
                            "SELECT  opt ".
                            "       ,value ".
                            "FROM    config"
                            )->execute();
        foreach($query as $row){
            $this->session->set($row['opt'],$row['value']);
        }
    }

    /**
    * UTF-8-muuntaja on-demand
    *
    * @param string $str Muunnettava teksti
    * @return string Teksti varmasti UTF-8:na
    */
	public function utf8($str){
		if($this->utf8_compliant($str) == 1){
			$return = $str;
		}else{
			$return = utf8_encode($str);
		}
		return $return;
	}

	/**
	* utf8:n kaveri. Tunnistaa, onko teksti utf-8:ia vai jotain muuta
	*
	* @param string $str Tunnistettava teksti
	* @return True/null, true, jos utf-8, kuolee hiljaa jollei.
	*/
    public function utf8_compliant($str) {
       	if ( strlen($str) == 0 ) {
           	return TRUE;
       	}
       	return (preg_match('/^.{1}/us',$str,$ar) == 1);
	}

    public function tinymce($css){
        $data = "
                <!-- TinyMCE -->
                <script type=\"text/javascript\" src=\"".URL::base($this->request)."tiny_mce/jquery.tinymce.js\"></script>
                <script type=\"text/javascript\"><!--
                function tinymce_setup(){
                    $(function(){
                    	$('textarea.tinymce').tinymce({
                        	script_url : \"".URL::base($this->request)."tiny_mce/tiny_mce.js\",

                    		// General options
                    		theme : \"advanced\",
                    		plugins : \"style,layer,advhr,advlink,iespell,inlinepopups,searchreplace,print,contextmenu,paste,directionality,fullscreen,visualchars,nonbreaking,xhtmlxtras,wordcount,advlist,save,preview\",

                    		// Buttons and toolbar
                    		theme_advanced_buttons1 : \"save,preview,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontsizeselect,|,cut,copy,paste,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,forecolor,backcolor,|,cancel\",
                        	theme_advanced_buttons2 : \"\",
                        	theme_advanced_buttons3 : \"\",
                    		theme_advanced_toolbar_location : \"external\",
                    		theme_advanced_toolbar_align : \"left\",
                    		theme_advanced_font_sizes: \"40px,45px,50px,60px,70px,80px,90px,100px\",
                    		theme_advanced_statusbar_location : \"bottom\",

                    		// Little tweaking.
                    		theme_advanced_resizing : false,
                    		force_br_newlines : true,
                            force_p_newlines : false,
                            forced_root_block : '',
                            save_onsavecallback : \"tinymce_tallenna\",
                            save_oncancelcallback : \"tinymce_poista\",
                            imagemanager_contextmenu: false,
                    		theme_advanced_resizing_use_cookie : false,

                    		// Editor size
                    		height: \"470\",
                    		width: \"940\",

                    		// Preview
                    		plugin_preview_width : \"1020\",
                            plugin_preview_height : \"760\",
                            plugin_preview_pageurl : \"".URL::base($this->request)."tiny_mce/preview.html\",

                    		// Content CSS
                    		content_css : \"".URL::base($this->request)."css/$css\",

                    		// Drop lists for link/image/media/template dialogs
                    		template_external_list_url : \"lists/template_list.js\",
                    		external_link_list_url : \"lists/link_list.js\",
                    		external_image_list_url : \"lists/image_list.js\",
                    		media_external_list_url : \"lists/media_list.js\",
                    		body_id : \"text\",
                    		body_class : \"main\",

                            setup : function(ed) {
                                ed.onPostRender.add(function(ed, cm) {
                                    document.getElementById(\"loota_cancel\").title = \"Poista dia\";
                                });
                            }
                    	});
                	});
                }
                --></script>

                <!-- /TinyMCE -->
            ";
        return $data;
    }
}
?>