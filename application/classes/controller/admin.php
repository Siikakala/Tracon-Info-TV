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
        	$this->view->header->css .= html::style('css/ui-tracon/jquery-ui-1.8.16.custom.css');
        	$this->view->header->js = '<script type="text/javascript" src="'.URL::base($this->request).'jquery/jquery-1.7.min.js"></script>';
        	$this->view->header->js .= "\n".'<script type="text/javascript" src="'.URL::base($this->request).'jquery/jquery-ui-1.8.16.custom.min.js"></script>';
            $this->view->header->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."jquery/jquery.metadata.js\"></script>";
            $this->view->header->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."jquery/jquery.uitablefilter.js\"></script>";
            $this->view->header->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."jquery/jquery.tablesorter.min.js\"></script>";
            $this->view->header->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."jquery/jquery.tablesorter.pager.js\"></script>";
            $this->view->header->js .= "\n<script src=\"http://yui.yahooapis.com/3.4.0/build/yui/yui-min.js\"></script>";
            if($this->session->get("g-show_tv",false) === false){//2. parametri = mihin defaultataan.
                $this->get_set();//määritelty alempana, asettaa g_show_tv ja g_show_stream sessiomuuttujat.
            }
            if($this->session->get("g-show_tv") == 1) $nayta = "true";
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
        	$this->view->header->login = "";//oletuksena nää on tyhjiä
        	$this->view->header->show = "";
            if($this->session->get('logged_in') && $this->request->action() != 'logout'){//mutta jos ollaan kirjauduttu sisään, eikä kirjautumassa ulos
                if($this->session->get("g-show_tv"))
                    $show = $this->session->get("g-show_tv");//pistetään valikoihin oikeet arvot
                else
                    $show = false;
                if($this->session->get("g-show_stream"))
                    $striim = $this->session->get("g-show_stream");
                else
                    $striim = false;
                $this->view->header->login = "Kirjautunut käyttäjänä: ".$this->session->get('user')."<br />".html::file_anchor('admin/logout','Kirjaudu ulos');//ja näytetään kirjautunut käyttäjä, uloskirjautumislinkki, ja globaali hallinta.
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
            $this->session->set('logged_in',true);//true/false
            $this->session->set('level',$login);//>= 1
            $this->session->set('user',$_POST['user']); //käyttäjätunnus.
            if(isset($_POST['return'])) $this->request->redirect($_POST['return']);//jos return-urli on määritelty, palataan sinne.
            else $this->request->redirect('admin/face');//muuten mennään oletussivulle.
        }else{
            $this->view->content->text = "<p class=\"error\">Väärä käyttäjätunnus tai salasana!</p>";
            $this->view->content->links = "";
        }
        $this->response->body($this->view->render());
    }

    /**
    * Päämetodi. Kaikki private funktio-kutsut kulkee tämän kautta. Viewi myös rendataan tämän lopussa.
    * Alustaa admin-näkymän, ja lisää sinne kiinteät elementit, sekä hakee privafunkkareista muuttuvat.
    *
    * Suurin osa magiasta tapahtuu kuitenkin ajax-metodissa.
    */
    public function action_face($page = null,$param1 = null, $param2 = null){
    	if(!$this->session->get('logged_in')){
			$this->request->redirect('admin/?return='.$this->request->uri()); // Ohjataan suoraan kirjautumiseen, mikäli yritetään avata tämä sivu ei-kirjautuneena
    	}elseif($this->session->get('level',0) < 1){//2. parametri = defaulttaa nollaks
        	$this->view->content->text = '<p>Valitettavasti sinulla ei ole ylläpito-oikeuksia.</p>';
        	$this->view->content->links = "";
    	}else{

        	$this->view->header->js .= "
            \n<script type=\"text/javascript\">
                $(function(){
                    $(\"#links li\").button({
                        icons:{
                            primary: \"ui-icon-triangle-1-e\"
                        }
                    });
                });
            </script>
            ";

    	    //<linkkipalkki>
			$this->view->content->links = "\n<ul>";
    	    $this->view->content->links .= "\n<li>".html::file_anchor('admin/face/scroller','Scroller')."</li><br/>";
    	    $this->view->content->links .= "\n<li>".html::file_anchor('admin/face/rulla','Rulla')."</li><br/>";
    	    $this->view->content->links .= "\n<li>".html::file_anchor('admin/face/dia','Diat')."</li><br/>";
    	    $this->view->content->links .= "\n<li>".html::file_anchor('admin/face/streams','Streamit')."</li><br/>";
    	    $this->view->content->links .= "\n<li>".html::file_anchor('admin/face/frontends','Frontendit')."</li><br/>";
    	    $this->view->content->links .= "\n<li>".html::file_anchor('admin/face/ohjelmakartta','Ohjelmakartta')."</li><br/>";
    	    $this->view->content->links .= "\n<li>".html::file_anchor('admin/face/logi','Lokikirja')."</li><br/>";
            $this->view->content->links .= "\n</ul><br /><ul>";
    	    $this->view->content->links .= "\n<li>".html::file_anchor('admin/logout','Kirjaudu ulos')."</li><br/>";
    	    $this->view->content->links .= "\n<li>".html::file_anchor('','Info-TV')."</li>";
			$this->view->content->links .= "\n</ul>";
    	    //</linkkipalkki>


    	    switch($page){//linkkien käsittely
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
                case "frontends":
                	$this->frontends($param1);
                	break;
                case "ohjelmakartta":
                	$this->ohjelmakartta($param1);
                	break;
                case "logi":
                    $this->logi($param1);
                    break;
                default:
               		$this->view->content->text = "<p>Olet nyt Info-TV:n hallintapaneelissa. Ole hyvä ja valitse toiminto valikosta.</p><p>Mikäli jokin data ei ole jollakin sivulla päivittynyt, lataa sivu uudelleen.</p>";
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

                $(".del").on({
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
                            )->execute(__db);
        if($query->count() > 0)
            $result = $query->as_array();
        else
            $result = false;

        $this->view->content->text  = "<h2>Scroller-hallinta</h2><div id=\"formidata\">";
        $this->view->content->text .= form::open(null, array("onsubmit" => "return false;", "id" => "form"));
        $this->view->content->text .= "<table id=\"scroller\" class=\"stats\" style=\"border-right:0px; border-top:0px; border-bottom:0px;\"><thead><tr><th class=\"ui-state-default\">Kohta</th><th class=\"ui-state-default\">Teksti</th><th class=\"ui-state-default\">Piilotettu?</th></tr></thead><tbody>";

        if($result) foreach($result as $row=>$data){
            $this->view->content->text .= "<tr class=\"".$data["id"]."\"><td>".form::input('pos-'.$data["id"],$data["pos"],array("size"=>"1","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::input('text-'.$data["id"],$data["text"],array("size"=>"45","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::checkbox('hidden-'.$data["id"],1,(boolean)$data["hidden"],array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del ignore\" onclick=\"dele(".$data["id"].")\" >X</a></td></tr>";
        }
        $this->view->content->text .= "</tbody></table>".form::close()."</div>".form::button("moar","Lisää rivi",array("id"=>"lisarivi","onclick"=>"addrow();"))."<br/><br/><p><strong>MUISTA TALLENTAA MUUTOKSESI!</strong></p><p><strong>HUOM!</strong><ul><li>Numerointi voi hyppiä numeroiden yli, ne näytetään pienimmästä suurimpaan.<li>Tyhjiä rivejä ei huomioida tallennuksessa.<li><strong>Älä käytä kohtaa 0!</strong> Rivi ei tallennu tällöin.</ul></p>".
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

                $(".del").on({
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
                            )->execute(__db);
        if($query->count() > 0)
            $result = $query->as_array();
        else
            $result = false;

    	$query2 = DB::query(Database::SELECT,
                            "SELECT    dia_id as \"id\"".
                            "         ,tunniste ".
                            "FROM      diat ".
                            "ORDER BY  dia_id"
                            )->execute(__db);
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
        $this->view->content->text .= "<table id=\"rulla\" class=\"stats\" style=\"border-right:0px; border-top:0px; border-bottom:0px;\"><thead><tr><th class=\"ui-state-default\">Kohta</th><th class=\"ui-state-default\">Dia</th><th class=\"ui-state-default\">Aika (~s)</th><th class=\"ui-state-default\">Piilotettu?</th></tr></thead><tbody>";



        if($result) foreach($result as $row=>$data){
            if($data['type'] == 2)
                $selector = 0;
            else
                $selector = $data['selector'];
            $this->view->content->text .= "<tr class=\"".$data["id"]."\"><td>".form::input('pos-'.$data["id"],$data["pos"],array("size"=>"1","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::select('text-'.$data["id"],$vaihtoehdot,$selector,array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::select('time-'.$data["id"],Date::seconds(1,1,121),$data["time"],array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::checkbox('hidden-'.$data["id"],1,(boolean)$data["hidden"],array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del ignore\" onclick=\"dele(".$data["id"].")\" >X</a></td></tr>";
        }
        $this->view->content->text .= "</tbody></table>".form::close()."</div>".form::button("moar","Lisää rivi",array("id"=>"lisarivi","onclick"=>"addrow();"))."<br/><br/><p><strong>MUISTA TALLENTAA MUUTOKSESI!</strong></p><p><strong>HUOM!</strong><ul><li>Numerointi voi hyppiä numeroiden yli, ne näytetään pienimmästä suurimpaan. <li><strong>Älä käytä kohtaa 0!</strong> Rivi ei tallennu tällöin.<li>Twitter-feediä ei voi olla kuin yksi. Ensimmäisen jälkeiset ovat vain tyhjiä dioja.</li></ul></p>".
                                    form::button('submit','Tallenna',array("onclick" => "return save();"))."<div id=\"feed_cont\" style=\"min-height:20px;\"><div id=\"feedback\" style=\"display:none;\"></div></div>";
    }

    private function dia($param1){
        $this->view->header->js .= $this->tinymce("admin.css");
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
                            )->execute(__db);
        $result[0] = "";
        if($query->count() > 0)
            foreach($query as $row => $data){
                $result[$data['dia_id']] = $this->utf8($data['tunniste']);
            }
        else
            $result[0] = false;

        $this->view->content->text .= "<div id=\"select\"><p>Valitse muokattava dia: ".form::select("dia",$result,0,array("onchange"=>"load(this.value)","id"=>"dia_sel"))." tai ".form::button("uusi","Luo uusi",array("onclick"=>"return uusi();"))."&nbsp;&nbsp;&nbsp;&nbsp;<span id=\"ident_\" style=\"display:none;\">Tunniste:".form::input("ident","",array("id"=>"ident"))."</span></p></div><div id=\"edit\" style=\"display:none;\"></div>";
        $this->view->content->text .= "<p>Kopioi haluamasi nuoli tästä: → ➜ ➔ ➞ ➨ ➧ ➩ ➭ ➼<br/>Voit käyttää [salinnimi-nyt] , [salinnimi-next] ja [aika] -tageja tekstin seassa, nyt; mitä tällä hetkellä salissa tapahtuu (- jos ei mitään), next; mitä tapahtuu seuraavaksi, kellonaikoineen (esim. 15 - 18 Cosplay-kisat (WCS ja pukukisa)), aika; tuottaa tämänhetkisen tunnin (esim 10 - 11). Esim. [iso_sali-nyt] tuottaa lauantaina klo 11:30 tekstin \"Avajaiset\".</p><p><strong>Pistä kuva-urlit johonkin ylös mikäli lisäät kuvia, koska et voi muokata niitä! (tinymce:n bugi)</strong></p><p>Muista käyttää esikatselutoimintoa ennen tallennusta. Tallennus tapahtuu levykkeestä, esikatselu sen oikealla puolella olevasta napista! Dian voi poistaa oikeassa reunassa olevasta napista.<br/><strong>MUISTA TALLENTAA MUUTOKSESI!</strong></p>";
    }

    private function streams($param1){
        $this->view->header->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."flowplayer/flowplayer-3.2.6.min.js\"></script>";
        $this->view->header->js .= '
            <script type="text/javascript">
                var id = 500;

                function load(id){
                    var container = $("#stream_content");

                    container.html(\'<a href="\' + $("#"+id).val() + \'" style="display:block;width:700px;height:390px;" id="player"></a><br/><a href="javascript:;" onclick="$f().stop(); window.setTimeout($(this).parent().hide(\\\'blind\\\',3000),800);">[Sulje]</a>\');

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

                }

                function dele(id){
                    var sure = confirm("Oletko varma että haluat poistaa tämän streamin?");
                    var container = $("#feedback");
                    if(sure){
                        fetch = \''.URL::base($this->request).'admin/ajax/stream_delete/\' + id;
                        $.getJSON(fetch, function(data) {
                            if(data.ret == true){
                                $("."+id).remove();
                                container.hide(0);
                                container.html("Rivi poistettu. Muista tallentaa muutoksesi!");
                            }else{
                                container.html(data.ret);
                            }
                            container.show(\'drop\',{ direction: "right", distance: "-50px" },500);
                            window.setTimeout(function(){
                                container.hide(\'drop\',{ direction: "right", distance: "100px" },1000);
                            },4000);
                        });

                    }
                }

                function addrow(){
                    $(\'#streamit\').append(\'<tr class="new"><td><input type="text" name="ident-\' + id + \'" value="" size="15" /></td><td><input type="text" name="url-\' + id + \'" value="" size="35" id="\' + id + \'" /></td><td><input name="jarkka-\' + id + \'" size="1" type="text"></td><td style="border:0px; border-bottom-style: none; padding: 0px; width:2px;"><a href="javascript:;" class="del" >X</a></td><td style="border:0px; border-bottom-style: none; padding: 0px;"><a href="javascript:;" onclick="load(\'+id+\');">&nbsp;Esikatsele</a></td></tr>\');
                    id = id + 1;
                }

                function save(){
                    var container = $("#feedback");
                    container.hide(0);
                    fetch = \''.URL::base($this->request).'admin/ajax/stream_save/\'
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
                    fetch = \''.URL::base($this->request).'admin/ajax/stream_load/\'
                    $.getJSON(fetch,function(data) {
                        container.html(data.ret);
                    });
                    window.setTimeout(function(){
                        container.show("clip",1000);
                    },100);
                }

                $(".del").on({
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
                            )->execute(__db);
        if($query->count() > 0){
            $result = $query->as_array();
            $count = $query->count() + 1;
        }else{
            $result = false;
            $count = 0;
        }
        if($this->session->get("level",0) < 3){
            $disabled = "disabled";
        }else{
            $disabled = "";
        }

        $this->view->content->text .= "<div id=\"formidata\">";
        $this->view->content->text .= form::open(null, array("onsubmit" => "return false;", "id" => "form"));
        $this->view->content->text .= "<table id=\"streamit\" class=\"stats\" style=\"border-right:0px; border-top:0px; border-bottom:0px;\"><thead><tr><th class=\"ui-state-default\">Streamin tunniste</th><th class=\"ui-state-default\">URL</th><th class=\"ui-state-default\">Järjestysnro</th></tr></thead><tbody>";

        if($result) foreach($result as $row => $data){
            $this->view->content->text .= "<tr class=\"".$data['stream_id']."\"><td>".form::input("ident-".$data['stream_id'],$data['tunniste'],array($disabled,"class" => "tunniste","size" => "15","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::input("url-".$data['stream_id'],$data['url'],array($disabled,"id" => $data['stream_id'],"class" => "url","size" => "35","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::input("jarkka-".$data['stream_id'],$data['jarjestys'],array($disabled,"size" => "1", "onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px; background-color: transparent;\"><a href=\"javascript:;\" class=\"del ignore\" onclick=\"dele(".$disabled.$data["stream_id"].")\">X</a></td><td style=\"border:0px; border-bottom-style: none; padding: 0px; background-color: transparent;\"><a href=\"javascript:;\" onclick=\"load(".$data['stream_id'].");\">&nbsp;Esikatsele</a></td></tr>";
        }

        $this->view->content->text .= "</tbody></table>".form::close()."</div><p>".form::button("moar","Lisää rivi",array("id"=>"lisarivi","onclick"=>"addrow();")).form::button("saev","Tallenna",array($disabled,"id"=>"saev","onclick" => "save();"))."<div id=\"feedback_container\" style=\"min-height:20px;\"><div id=\"feedback\" style=\"display:none;\"></div></div>Esikatselu:</p><div id=\"stream_content\" style=\"display:none;\"></div>";
    }

    private function get_streams(){
        $query = DB::query(Database::SELECT,
                            "SELECT    stream_id ".
                            "         ,tunniste ".
                            "FROM      streamit ".
                            "ORDER BY  jarjestys "
                            )->execute(__db);
        $ret = array();
        foreach($query as $row){
            $ret[$row['stream_id']] = $row['tunniste'];
        }
        return $ret;
    }

    private function frontends(){
        $this->view->header->js .= '
            <script type="text/javascript">

                function check(show,id){
                    if(show == 1){
                        $(\'#\' + id + \'-stream\').show("medium");
                        $(\'#\' + id + \'-dia\').hide("medium");
                    }else if(show == 2){
                        $(\'#\' + id + \'-stream\').hide("medium");
                        $(\'#\' + id + \'-dia\').show("medium");
                    }else{
                        $(\'#\' + id + \'-stream\').hide("medium");
                        $(\'#\' + id + \'-dia\').hide("medium");
                    }
                }


                function dele(id){
                    var sure = confirm("Oletko varma että haluat poistaa tämän frontendin? Se nollaa frontendin asetukset ja nimen.");
                    var container = $("#feedback");
                    if(sure){
                        fetch = \''.URL::base($this->request).'admin/ajax/fronted_delete/\' + id;
                        $.getJSON(fetch, function(data) {
                            if(data.ret == true){
                                $("."+id).remove();
                                container.hide(0);
                                container.html("Frontend poistettu. Muista tallentaa muutoksesi!");
                            }else{
                                container.html(data.ret);
                            }
                            container.show(\'drop\',{ direction: "right", distance: "-50px" },500);
                            window.setTimeout(function(){
                                container.hide(\'drop\',{ direction: "right", distance: "100px" },1000);
                            },4000);
                        });

                    }
                }

                function save(){
                    var container = $("#feedback");
                    container.hide(0);
                    fetch = \''.URL::base($this->request).'admin/ajax/frontend_save/\'
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
                    fetch = \''.URL::base($this->request).'admin/ajax/frontend_load/\'
                    $.getJSON(fetch,function(data) {
                        container.html(data.ret);
                    });
                    window.setTimeout(function(){
                        container.show("clip",1000);
                    },100);
                }

                $(".del").on({
                    mouseenter: function() {
                        $(this).addClass(\'hover\');
                    },
                    mouseleave: function(){
                        $(this).removeClass(\'hover\');
                    }
                });
            </script>
            ';
        $this->view->content->text = "<h2>Frontend-hallinta</h2><h4>Globaali hallinta löytyy yläreunasta.</h4><p>Tällä sivulla voit tarvittaessa pakottaa jonkin frontendin näyttämään vaikkapa pelkkää diashowta, esim. infossa.</p>";
        $query = DB::query(Database::SELECT,
                            "SELECT   f_id ".
                            "        ,tunniste ".
                            "        ,show_tv ".
                            "        ,show_stream ".
                            "        ,dia ".
                            "        ,use_global ".
                            "FROM     frontends ".
                            "WHERE    last_active > DATE_SUB(NOW(),INTERVAL 15 MINUTE)"
                            )->execute(__db);
        if($query->count() > 0){
            $result = $query->as_array();
        }else{
            $result = false;
        }

        $query4 = DB::query(Database::SELECT,
                            "SELECT    dia_id ".
                            "         ,tunniste ".
                            "FROM      diat ".
                            "ORDER BY  dia_id"
                            )->execute(__db);
        $diat[0] = "twitter";
        if($query4->count() > 0)
            foreach($query4 as $row => $data){
                $diat[$data['dia_id']] = $this->utf8($data['tunniste']);
            }
        else
            $diat = false;

        //Frontendit, jotka eivät ole ilmoittaneet itsestään yli viiteen minuuttiin, asetetaan käyttämään globaalia asetusta.
        $query2 = DB::query(Database::UPDATE,
                            "UPDATE frontends ".
                            "SET    use_global = 1 ".
                            "WHERE  last_active < DATE_SUB(NOW(),INTERVAL 5 MINUTE)"
                            )->execute(__db);

        //Frontendit, jotka eivät ole ilmoittaneet itsestään yli viikkoon, poistetaan automaattisesti
        $query3 = DB::query(Database::DELETE,
                            "DELETE FROM frontends ".
                            "WHERE  last_active < DATE_SUB(NOW(),INTERVAL 1 WEEK)"
                            )->execute(__db);

        $this->view->content->text .= "<div id=\"formidata\">";
        if($result){
            $streams = $this->get_streams();
            $this->view->content->text .= form::open(null, array("onsubmit" => "return false;", "id" => "form"));
            $this->view->content->text .= "<table id=\"frontendit\" class=\"stats\" style=\"border-right:0px; border-top:0px; border-bottom:0px;\"><thead><tr><th class=\"ui-state-default\">Frontend</th><th class=\"ui-state-default\">Näytä</th><th class=\"ui-state-default\">Käytä globaalia?</th></tr></thead><tbody>";
            foreach($result as $row => $data){
                if($data['show_tv'] == 1){
                    $nayta_stream = "inline";
                    $nayta_dia = "none";
                }elseif($data['show_tv'] == 2){
                    $nayta_stream = "none";
                    $nayta_dia = "inline";
                }else{
                    $nayta_stream = "none";
                    $nayta_dia = "none";
                }
                $this->view->content->text .= "<tr class=\"".$data['f_id']."\"><td>".form::input("ident-".$data['f_id'],$data['tunniste'],array("size" => "20","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::select("show_tv-".$data['f_id'],array("Diashow","Streami","Yksittäinen dia"),$data['show_tv'],array("id"=>$data['f_id']."-tv","onchange"=>"check(this.value,\"".$data['f_id']."\");$(this).addClass(\"new\");$(\"#".$data['f_id']."-stream\").addClass(\"new\");$(\"#".$data['f_id']."-dia\").addClass(\"new\");")).form::select("show_stream-".$data['f_id'],$streams,$data['show_stream'],array("id"=>$data['f_id']."-stream","onchange"=>"$(this).addClass(\"new\");","style" => "display:$nayta_stream;")).form::select("dia-".$data['f_id'],$diat,$data['dia'],array("id"=>$data['f_id']."-dia","onchange"=>"$(this).addClass(\"new\");","style" => "display:$nayta_dia;"))."</td><td>".form::checkbox("use_global-".$data['f_id'],1,(boolean)$data['use_global'],array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; background-color: transparent;\">&nbsp;</td></tr>";
            }
            $this->view->content->text .= "</tbody></table>".form::close()."</div><p>".form::button("saev","Tallenna",array("id"=>"saev","onclick" => "save();"))."<div id=\"feedback_container\" style=\"min-height:20px;\"><div id=\"feedback\" style=\"display:none;\"></div></div>";
        }else{
            $this->view->content->text .= "<p>Yhtään aktiivista frontendiä ei löytynyt.</p>";
        }
        $this->view->content->text .= "<p><strong>HUOM!</strong><ul><li>Listauksessa on vain 15min sisään itsestään ilmoittaneet frontendit</li><li>Globaalia asetusta käyttävien näyttöasetukset eivät vaikuta mihinkään.</li><li>Frontendit, jotka eivät ole ilmoittaneet itsestään yli viiteen minuuttiin, asetetaan käyttämään globaalia asetusta.</li><li>Frontendit, jotka eivät ole ilmoittaneet itsestään yli viikkoon, poistetaan automaattisesti</li></ul></p>";
    }

    private function ohjelmakartta(){
    	$this->view->header->css .= html::style('css/jquery.fileupload-ui.css');
    	$this->view->header->js .= '<script type="text/javascript" src="'.URL::base($this->request).'jquery/jquery.iframe-transport.js"></script>';
    	$this->view->header->js .= '<script type="text/javascript" src="'.URL::base($this->request).'jquery/jquery.fileupload.js"></script>';
    	$this->view->header->js .= '<script type="text/javascript" src="'.URL::base($this->request).'jquery/jquery.fileupload-ui.js"></script>';
    	$this->view->header->js .= '<script type="text/javascript" src="'.URL::base($this->request).'jquery/jquery.tmpl.min.js"></script>';
    	$this->view->header->js .= '
        <script type="text/javascript">
            $(function(){
                $("#upload").fileupload();

                $("#upload").bind(\'fileuploaddone\', function (e, data) {
                    $.each(data.result, function(index,file){
                        process(file.name);
                    });
                });

                // Load existing files:
                $.getJSON($(\'#upload form\').prop(\'action\'), function (files) {
                    var fu = $(\'#upload\').data(\'fileupload\');
                    fu._adjustMaxNumberOfFiles(-files.length);
                    fu._renderDownload(files)
                        .appendTo($(\'#upload .files\'))
                        .fadeIn(function () {
                            // Fix for IE7 and lower:
                            $(this).show();
                        });
                });

                // Open download dialogs via iframes,
                // to prevent aborting current uploads:
                $(\'#upload .files a:not([target^=_blank])\').on(\'click\', function (e) {
                    e.preventDefault();
                    $(\'<iframe style="display:none;"></iframe>\')
                        .prop(\'src\', this.href)
                        .appendTo(\'body\');
                });
            });

            function process(filename){
                var container = $("#upload");
                var bar = $("#progress");
                window.setTimeout(function(){//<*hifist*>
                    bar.html(\'<span id="baari">Prosessoidaan ohjelmakarttaa, odota hetki..</span>\');
                    bar.show(\'medium\');
                    window.setTimeout(function(){
                        window.setTimeout(function(){
                            fetch = \''.URL::base($this->request).'admin/ajax/ohjelma/\'
                            $.post(fetch, { "file": filename }, function(data){
                                $("#baari").hide(\'blind\',\'300\');
                                window.setTimeout(function(){
                                    $("#baari").html(data.ret);
                                    $("#baari").show(\'300\');
                                    window.setTimeout(function(){
                                        bar.hide(\'blind\',\'slow\');
                                        fetch = \''.URL::base($this->request).'admin/ajax/upload/?file=\' + filename + \'&del=1\';
                                        $.getJSON(fetch,function(data){
                                            $("tr.template-download").hide(\'medium\');
                                        });
                                        fetch = \''.URL::base($this->request).'admin/ajax/lastupdate/\';
                                        $.getJSON(fetch,function(data){
                                            $("#lastupdate").html(data.ret);
                                        });
                                    },4000);
                                },310);
                            },"json");
                        },500);
                    },1300);
                },500);//</*hifist*>
            }
        </script>
        <script id="template-upload" type="text/x-jquery-tmpl">
            <tr class="template-upload{{if error}} ui-state-error{{/if}}">
                <td class="preview"></td>
                <td class="name">${name}</td>
                <td class="size">${sizef}</td>
                {{if error}}
                    <td class="error" colspan="2">Error:
                        {{if error === \'maxFileSize\'}}File is too big
                        {{else error === \'minFileSize\'}}File is too small
                        {{else error === \'acceptFileTypes\'}}Filetype not allowed
                        {{else error === \'maxNumberOfFiles\'}}Max number of files exceeded
                        {{else}}${error}
                        {{/if}}
                    </td>
                {{else}}
                    <td class="progress"><div></div></td>
                    <td class="start"><button>Start</button></td>
                {{/if}}
                <td class="cancel"><button>Cancel</button></td>
            </tr>
        </script>
        <script id="template-download" type="text/x-jquery-tmpl">
            <tr class="template-download{{if error}} ui-state-error{{/if}}">
                {{if error}}
                    <td></td>
                    <td class="name">${name}</td>
                    <td class="size">${sizef}</td>
                    <td class="error" colspan="2">Error:
                        {{if error === 1}}File exceeds upload_max_filesize (php.ini directive)
                        {{else error === 2}}File exceeds MAX_FILE_SIZE (HTML form directive)
                        {{else error === 3}}File was only partially uploaded
                        {{else error === 4}}No File was uploaded
                        {{else error === 5}}Missing a temporary folder
                        {{else error === 6}}Failed to write file to disk
                        {{else error === 7}}File upload stopped by extension
                        {{else error === \'maxFileSize\'}}File is too big
                        {{else error === \'minFileSize\'}}File is too small
                        {{else error === \'acceptFileTypes\'}}Filetype not allowed
                        {{else error === \'maxNumberOfFiles\'}}Max number of files exceeded
                        {{else error === \'uploadedBytes\'}}Uploaded bytes exceed file size
                        {{else error === \'emptyResult\'}}Empty file upload result
                        {{else}}${error}
                        {{/if}}
                    </td>
                {{else}}
                    <td class="preview">
                        {{if thumbnail_url}}
                            <a href="${url}" target="_blank"><img src="${thumbnail_url}"></a>
                        {{/if}}
                    </td>
                    <td class="name">
                        <a href="${url}"{{if thumbnail_url}} target="_blank"{{/if}}>${name}</a>
                    </td>
                    <td class="size">${sizef}</td>
                    <td colspan="2"></td>
                {{/if}}

            </tr>
        </script>
        ';
        /*

        <td class="delete">
                    <button data-type="${delete_type}" data-url="${delete_url}">Delete</button>
                </td>

        */
        $this->view->content->text = "<h2>Ohjelmakarttadatan päivitys</h2>";

        $q = DB::query(Database::SELECT,"SELECT `update` FROM `ohjelmadata` ORDER BY `update` DESC LIMIT 1")->execute(__db);
        $r = $q->as_array();
        if(isset($r[0])){
            $last_update = date("d.m.Y H:i",strtotime($r[0]['update']));
        }else{
            $last_update = "Ei koskaan.";
        }

        $this->view->content->text .= "<div id=\"upload\">".
                                            form::open(URL::base($this->request).'admin/ajax/upload', array('enctype' => 'multipart/form-data','method' => 'post')).
                                                "<div class=\"fileupload-buttonbar\">
                                                    <label class=\"fileinput-button\">
                                                        <span>Päivitä ohjelmakartta</span>".
                                                        form::file('files[]',array("accept"=>"text/plain"))."
                                                    </label>
                                                 </div>".
                                            form::close().
                                            "<div class=\"fileupload-content\">
                                                <table class=\"files\">
                                                    <tr><td class=\"preview\"></td><td class=\"name\" colspan=\"4\">Ohjelmakartta päivitetty viimeksi: <span id=\"lastupdate\">".$last_update."</span></td></tr>
                                                </table>
                                                <div id=\"progress\" class=\"fileupload-progressbar\"></div>
                                             </div>
                                        </div>
                                        <p><br/><br/><strong>HUOM!</strong><br/>On suositeltavaa, että piilotat ohjelmakarttadiat datan päivityksen ajaksi. Näin dioihin ei tule kummallisuuksia.</p>";

    }

    private function logi(){

        $this->view->content->text = "<h2>Lokikirja</h2>";
        $this->view->header->js .= '
            <script type="text/javascript">
                $(document).ready(function() {
                    //ref();
                });

                $(function() {
                    $("#filter").on("keyup",function(event) {
                        var theTable = $(\'table.stats\');
                        $.uiTableFilter( theTable, this.value );
                    })
                });

                function refresh(){
                    var container = $("#table");
                    fetch = \''.URL::base($this->request).'admin/ajax/todo_refresh/\'
                    $.getJSON(fetch+ \'?\' + Math.round(new Date().getTime()),function(data) {
                        $.each(data,function(key,val){
                            container.html(val);
                        });
                    });
                    window.setTimeout(function(){
                        var theTable = $(\'table.stats\');
                        var arvo = $("#filter").value;
                        $.uiTableFilter( theTable, arvo );
                    },40);
                    return false;
                };

                function save(){
                    var container = $("#feedback");
                    container.hide(\'fast\');
                    fetch = \''.URL::base($this->request).'admin/ajax/todo_save/\'
                    $.post(fetch,$("#form").serialize(),function(data) {
                        container.html(data.ret);
                    },"json");
                    container.show(\'drop\',{ direction: "right", distance: "-50px" },500);
                    refresh();
                    $( "form" )[ 0 ].reset();
                    window.setTimeout(function(){
                        container.hide(\'drop\',{ direction: "right", distance: "100px" },1000);
                    },4000);
                    return false;
                };

                function ref(){
                    refresh();
                    window.setTimeout(function(){
                        ref();
                    },5000);
                };

                $("form").submit(function(e) {
                    e.preventDefault();
                });
            </script>
            ';

        $query = DB::query(Database::SELECT,
                           'SELECT   tag '.
                           '        ,comment '.
                           '        ,adder '.
                           '        ,stamp '.
                           'FROM     logi '.
                           'ORDER BY stamp DESC'
                           )->execute(__db);
        $types = array(2=>"Tiedote",1=>"Ongelma",3=>"Kysely",0=>"Löytötavara",4=>"Muu");
        $add = form::open(null, array("onsubmit" => "save(); return false;", "id" => "form"))."Lisää rivi:<br />".form::label('tag',' Tyyppi:').form::select('tag',$types,2,array("id"=>"tag")).form::label('comment',' Viesti:').form::input('comment',null,array("id"=>"com","size"=>"56")).form::label('adder',' Lisääjä:').form::input('adder',null,array("id"=>"adder","size"=>"5")).form::submit(null,'Lisää').form::close()."\n";
        $this->view->content->text .= "<div id=\"filter\" style=\"float:right;margin-top:-30px;\">Suodatus/haku: ".form::input('filter',null,array("id"=>"filter"))."</div><div id=\"add\">$add</div><div id=\"feed_cont\" style=\"min-height:20px;\"><div id=\"feedback\"></div></div>
        <div id=\"table\">\n";

        if($query->count() > 0){
            $this->view->content->text .= "<table id=\"taulu\" class=\"stats tablesorter\"><thead><tr><th>Aika</th><th>Tyyppi</th><th>Viesti</th><th>Lisääjä</th></tr></thead><tbody>\n";
            foreach($query as $row){
                $this->view->content->text .= "<tr class=\"type-".$row['tag']."\"><td>".date("d.m. H:i",strtotime($row['stamp']))."</td><td>".$types[$row['tag']]."</td><td>".$row['comment']."</td><td>".$row['adder']."</td></tr>\n";
            }
            $this->view->content->text .= "</tbody></table>";
        }
        $this->view->content->text .= "</div>";
    }

    /**
    * Warning! Casting Magics ahead!
    *
    * Tässä metodissa tapahtuu siis 90% koko controllerin toiminnallisuudesta.
    */
    public function action_ajax($param1 = null,$param2 = null){
    	$return = "";
    	//if($this->request->param($param1)) $param1 = $this->request->param($param1);
    	if($this->session->get('logged_in') and $this->session->get('level') > 0){//varmistetaan että on kirjauduttu sisään ja oikeudet muokata asioita.
        	switch($param1){
                case "scroller_save":
                    $post = $_POST;
                    $data = array();
                    $err = " ";
                    foreach($post as $key => $value){
                        $parts = explode("-",$key);
                        $rivi = $parts[1];
                        $data[$rivi][$parts[0]] = $value;//automagiikka <input name="data-id" value="value"> -> $data['id']['data'] = value
                    }
                    foreach($data as $row => $datat){
                        if(empty($datat["text"]) && empty($datat["pos"])){
                        }elseif(empty($datat["text"]) || empty($datat["pos"])){
                            $err .= "Jokin kenttä jäi täyttämättä. Kyseisen rivin tietoja <strong>EI</strong> ole tallennettu.";
                        }else{
                            if(!isset($datat["hidden"]))//checkkaamattomat checkboxit ei tuu mukaan ollenkaan.
                                $datat["hidden"] = false;
                            if($row >= 0 && $row < 500){//vanha rivi
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
                                $result = $query->execute(__db);
                            }elseif($row >= 500){//uusi rivi
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
                                $result = $query->execute(__db);
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
                                        )->execute(__db);
                    if($query->count() > 0)
                        $result = $query->as_array();
                    else
                        $result = false;
                    $text = form::open(null, array("onsubmit" => "return false;", "id" => "form"));
                    $text .= "<table id=\"scroller\" class=\"stats\" style=\"border-right:0px; border-top:0px; border-bottom:0px;\"><thead><tr><th class=\"ui-state-default\">Kohta</th><th class=\"ui-state-default\">Teksti</th><th class=\"ui-state-default\">Piilotettu?</th></tr></thead><tbody>";

                    if($result) foreach($result as $row=>$data){
                        $text .= "<tr class=\"".$data["id"]."\"><td>".form::input('pos-'.$data["id"],$data["pos"],array("size"=>"1","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::input('text-'.$data["id"],$data["text"],array("size"=>"45","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::checkbox('hidden-'.$data["id"],1,(boolean)$data["hidden"],array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del ignore\" onclick=\"dele(".$data["id"].")\" >X</a></td></tr>";
                    }
                    $text .= "</tbody></table>".form::close();
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
                        $query->param(":id",$param2)->execute(__db);
                        $ret = true;
                    }
                    $return = array("ret" => $ret);
                    break;
                case "rulla_row"://rivin generointi vaatii sen verran että on helpompaa hakee data ajaxilla.
                	$id = $param2;
                	$text = "";
                    $query = DB::query(Database::SELECT,
                                        "SELECT    pos ".
                                        "FROM      rulla ".
                                        "WHERE     pos = (SELECT MAX(pos) FROM rulla)"
                                        )->execute(__db);
                    if($query->count() > 0)
                        $result = $query->as_array();
                    else
                        $result = false;

                	$query2 = DB::query(Database::SELECT,
                                        "SELECT    dia_id as \"id\"".
                                        "         ,tunniste ".
                                        "FROM      diat ".
                                        "ORDER BY  dia_id"
                                        )->execute(__db);
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
                    $text = "<tr class=\"new\" new=\"$id\"><td>".form::input('pos-'.$id,$pos,array("size"=>"1"))."</td><td>".form::select('text-'.$id,$vaihtoehdot,1)."</td><td>".form::select('time-'.$id,Date::seconds(1,1,121),10)."</td><td>".form::checkbox('hidden-'.$id,1,false)."</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del\" >X</a></td></tr>";
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
                                if($datat["text"] == 0)
                                    $type = 2;
                                else
                                    $type = 1;
                                $query = DB::query(Database::UPDATE,
                                                    "UPDATE  rulla ".
                                                    "SET     pos = :pos ".
                                                    "       ,selector = :text ".
                                                    "       ,time = :time".
                                                    "       ,hidden = :hidden ".
                                                    "       ,type = $type ".
                                                    "WHERE   rul_id = :row"
                                                    );
                                $query->parameters(array(":pos"    => $datat["pos"],
                                                         ":text"   => $datat["text"],
                                                         ":time"   => $datat["time"],
                                                         ":hidden" => $datat["hidden"],
                                                         ":row"    => $row
                                                         ));
                                $result = $query->execute(__db);
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
                                $result = $query->execute(__db);
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
                                        )->execute(__db);
                    if($query->count() > 0)
                        $result = $query->as_array();
                    else
                        $result = false;

                	$query2 = DB::query(Database::SELECT,
                                        "SELECT    dia_id as \"id\"".
                                        "         ,tunniste ".
                                        "FROM      diat ".
                                        "ORDER BY  dia_id"
                                        )->execute(__db);
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
                    $text = form::open(null, array("onsubmit" => "return false;", "id" => "form"))."<table id=\"rulla\" class=\"stats\" style=\"border-right:0px; border-top:0px; border-bottom:0px;\"><thead><tr><th class=\"ui-state-default\">Kohta</th><th class=\"ui-state-default\">Dia</th><th class=\"ui-state-default\">Aika (~s)</th><th class=\"ui-state-default\">Piilotettu?</th></tr></thead><tbody>";

                    if($result) foreach($result as $row=>$data){
                        if($data['type'] == 2)//jos twitter.
                            $selector = 0;//joka on aina ensimmäinen vaihtoehdoista.
                        else
                            $selector = $data['selector'];
                        $text .= "<tr class=\"".$data["id"]."\"><td>".form::input('pos-'.$data["id"],$data["pos"],array("size"=>"1","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::select('text-'.$data["id"],$vaihtoehdot,$selector,array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::select('time-'.$data["id"],Date::seconds(1,1,121),$data["time"],array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::checkbox('hidden-'.$data["id"],1,(boolean)$data["hidden"],array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del ignore\" onclick=\"dele(".$data["id"].")\" >X</a></td></tr>";
                    }
                    $text .= "</tbody></table>".form::close();
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
                        $query->param(":id",$param2)->execute(__db);
                        $ret = true;
                    }
                    $return = array("ret" => $ret);
                    break;
                case "tv"://globaali hallinta.
                    if($_POST['stream'] == "null"){
                        $return = array("ret" => "Streamia ei määritelty. Valitaan diashow.");
                        $query = DB::query(Database::UPDATE,
                                            "UPDATE   config ".
                                            "SET      value = 0 ".
                                            "WHERE    opt = 'show_tv'"
                                            )->execute(__db);
                    }else{
                        $this->session->set("g-show_tv",$_POST['nayta']);
                        $this->session->set("g-show_stream",$_POST['stream']);
                        $query = DB::query(Database::UPDATE,
                                            "UPDATE   config ".
                                            "SET      value = :value ".
                                            "WHERE    opt = 'show_tv'"
                                            );
                        $query->param(":value",$_POST['nayta'])->execute(__db);
                        $query = DB::query(Database::UPDATE,
                                            "UPDATE   config ".
                                            "SET      value = :value ".
                                            "WHERE    opt = 'show_stream'"
                                            );
                        $query->param(":value",$_POST['stream'])->execute(__db);
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
                        $result = $query->execute(__db);
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
                        $result = $query->execute(__db);
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
                        $result = $query->execute(__db);
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
                        $r = $q->param(":id",$param2)->execute(__db);
                        $q2 = DB::query(Database::SELECT,
                                        "SELECT tunniste ".
                                        "FROM   frontends ".
                                        "WHERE  dia = :id"
                                        );
                        $r2 = $q2->param(":id",$param2)->execute(__db);
                        if($r->count() > 0){
                            $ret = "Diaa käytetään vielä diashowssa. Poista dia sieltä ensin.";
                        }elseif($r2->count() > 0){
                            $d = $r2->as_array();
                            $ret = "Diaa näytetään tällä hetkellä frontendissä ".$d[0]['tunniste'].". Poista dia sieltä ensin.";
                        }else{
                            $query = DB::query(Database::DELETE,
                                                "DELETE FROM diat ".
                                                "WHERE       dia_id = :id"
                                                );
                            $query->param(":id",$param2)->execute(__db);
                            $ret = true;
                        }
                    }
                    $return = array("ret" => $ret);
                    break;
                case "stream_delete":
                    if(!$param2){
                        $ret = false;
                    }else{
                        //Globaali asetus
                        $q = DB::query(Database::SELECT,
                                        "SELECT opt ".
                                        "FROM   config ".
                                        "WHERE  value = :id ".
                                        "       AND ".
                                        "       opt = 'show_stream'".
                                        "       AND ".
                                        "       (select value from config where opt = 'show_tv') = 1"
                                        );
                        $r = $q->param(":id",$param2)->execute(__db);

                        //Yksittäinen frontend, joka ei käytä globaalia
                        $q2 = DB::query(Database::SELECT,
                                        "SELECT f_id ".
                                        "FROM   frontends ".
                                        "WHERE  show_stream = :id ".
                                        "       AND ".
                                        "       use_global = 0".
                                        "       AND ".
                                        "       show_tv = 1"
                                        );
                        $r2 = $q2->param(":id",$param2)->execute(__db);

                        if($r->count() > 0 || $r2->count() > 0){
                            $ret = "Streamia näytetään parhaillaan. Vaihda toiseen streamiin tai diashowhun ensin.";
                        }else{
                            $query = DB::query(Database::DELETE,
                                                "DELETE FROM streamit ".
                                                "WHERE       stream_id = :id"
                                                );
                            $query->param(":id",$param2)->execute(__db);
                            $ret = true;
                        }
                    }
                    $return = array("ret" => $ret);
                    break;
                case "stream_save":
                    $post = $_POST;
                    $data = array();
                    $err = " ";
                    foreach($post as $key => $value){
                        $parts = explode("-",$key);
                        $rivi = $parts[1];
                        $data[$rivi][$parts[0]] = $value;
                    }
                    foreach($data as $row => $datat){
                        if(empty($datat["ident"]) && empty($datat["url"])){
                        }elseif(empty($datat["ident"]) || empty($datat["url"])){
                            $err .= "Jokin kenttä jäi täyttämättä. Kyseisen rivin tietoja <strong>EI</strong> ole tallennettu.";
                        }else{
                            if(empty($datat['jarkka']))
                                $datat['jarkka'] = $row+200;
                            if($row >= 0 && $row < 500){
                                $query = DB::query(Database::UPDATE,
                                                    "UPDATE  streamit ".
                                                    "SET     tunniste = :ident ".
                                                    "       ,url = :url ".
                                                    "       ,jarjestys = :jarkka ".
                                                    "WHERE   stream_id = :row"
                                                    );
                                $query->parameters(array(":ident"  => $datat["ident"],
                                                         ":url"    => $datat["url"],
                                                         ":jarkka" => $datat["jarkka"],
                                                         ":row"    => $row
                                                         ));
                                $result = $query->execute(__db);
                            }elseif($row >= 500){
                                $query = DB::query(Database::INSERT,
                                                    "INSERT INTO streamit ".
                                                    "           (tunniste ".
                                                    "           ,url ".
                                                    "           ,jarjestys ".
                                                    "           ) ".
                                                    "VALUES     (:ident ".
                                                    "           ,:url ".
                                                    "           ,:jarkka ".
                                                    "           )"
                                                    );
                                $query->parameters(array(":ident"  => $datat["ident"],
                                                         ":url"    => $datat["url"],
                                                         ":jarkka" => $datat["jarkka"]
                                                         ));
                                $result = $query->execute(__db);
                            }
                        }
                    }
                    $return = array("ret"=>"Streamit päivitetty.$err Odota hetki, päivitetään listaus...");
                    break;
                case "stream_load":
                    $text = "";
                    $query = DB::query(Database::SELECT,
                                        "SELECT    stream_id ".
                                        "         ,tunniste ".
                                        "         ,url ".
                                        "         ,jarjestys ".
                                        "FROM      streamit ".
                                        "ORDER BY  jarjestys"
                                        )->execute(__db);
                    if($query->count() > 0){
                        $result = $query->as_array();
                        $count = $query->count() + 1;//...tätä ei edes käytetä enää -_-;
                    }else{
                        $result = false;
                        $count = 0;
                    }

                    $text .= form::open(null, array("onsubmit" => "return false;", "id" => "form"));
                    $text .= "<table id=\"streamit\" class=\"stats\" style=\"border-right:0px; border-top:0px; border-bottom:0px;\"><thead><tr><th class=\"ui-state-default\">Streamin tunniste</th><th class=\"ui-state-default\">URL</th><th class=\"ui-state-default\">Järjestysnro</th></tr></thead><tbody>";

                    if($result) foreach($result as $row => $data){
                        $text .= "<tr class=\"".$data['stream_id']."\"><td>".form::input("ident-".$data['stream_id'],$data['tunniste'],array("class" => "tunniste","size" => "15","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::input("url-".$data['stream_id'],$data['url'],array("id" => $data['stream_id'],"class" => "url","size" => "35","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::input("jarkka-".$data['stream_id'],$data['jarjestys'],array("size" => "1", "onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del ignore\" onclick=\"dele(".$data["stream_id"].")\">X</a></td><td style=\"border:0px; border-bottom-style: none; padding: 0px;\"><a href=\"javascript:;\" onclick=\"load(".$data['stream_id'].");\">&nbsp;Esikatsele</a></td></tr>";
                    }

                    $text .= "</tbody></table>".form::close();
                    $return = array("ret" => $text);
                    break;
                case "frontend_save":
                    $post = $_POST;
                    $data = array();
                    $err = " ";
                    foreach($post as $key => $value){
                        $parts = explode("-",$key);
                        $rivi = $parts[1];
                        $data[$rivi][$parts[0]] = $value;
                    }
                    foreach($data as $row => $datat){
                        if(empty($datat["use_global"]))
                            $datat["use_global"] = 0;
                        if(empty($datat["ident"])){
                            $err .= "Jokin frontendin tunniste jäi täyttämättä. Kyseisen frontendin tietoja <strong>EI</strong> ole tallennettu.";
                        }else{
                            $query = DB::query(Database::UPDATE,
                                                "UPDATE  frontends ".
                                                "SET     tunniste = :ident ".
                                                "       ,show_tv = :tv ".
                                                "       ,show_stream = :stream ".
                                                "       ,dia = :dia ".
                                                "       ,use_global = :global ".
                                                "WHERE   f_id = :row"
                                                );
                            $query->parameters(array(":ident"  => $datat["ident"],
                                                     ":tv"     => $datat["show_tv"],
                                                     ":stream" => $datat["show_stream"],
                                                     ":dia"    => $datat["dia"],
                                                     ":global" => $datat["use_global"],
                                                     ":row"    => $row
                                                     ));
                            $result = $query->execute(__db);
                        }
                    }
                    $return = array("ret"=>"Frontendit päivitetty.$err Odota hetki, päivitetään listaus...");
                    break;
                case "frontend_load":
                    $query = DB::query(Database::SELECT,
                                        "SELECT   f_id ".
                                        "        ,tunniste ".
                                        "        ,show_tv ".
                                        "        ,show_stream ".
                                        "        ,dia ".
                                        "        ,use_global ".
                                        "FROM     frontends ".
                                        "WHERE    last_active > DATE_SUB(NOW(),INTERVAL 5 MINUTE)"
                                        )->execute(__db);
                    if($query->count() > 0){
                        $result = $query->as_array();
                    }else{
                        $result = false;
                    }

                    $query4 = DB::query(Database::SELECT,
                                        "SELECT    dia_id ".
                                        "         ,tunniste ".
                                        "FROM      diat ".
                                        "ORDER BY  dia_id"
                                        )->execute(__db);
                    $diat[0] = "twitter";
                    if($query4->count() > 0)
                        foreach($query4 as $row => $data){
                            $diat[$data['dia_id']] = $this->utf8($data['tunniste']);
                        }
                    else
                        $diat = false;

                    if($result){
                        $streams = $this->get_streams();
                        $text = form::open(null, array("onsubmit" => "return false;", "id" => "form"));
                        $text .= "<table id=\"frontendit\" class=\"stats\" style=\"border-right:0px; border-top:0px; border-bottom:0px;\"><thead><tr><th class=\"ui-state-default\">Frontend</th><th class=\"ui-state-default\">Näytä</th><th class=\"ui-state-default\">Käytä globaalia?</th></tr></thead><tbody>";
                        foreach($result as $row => $data){
                            if($data['show_tv'] == 1){
                                $nayta_stream = "inline";
                                $nayta_dia = "none";
                            }elseif($data['show_tv'] == 2){
                                $nayta_stream = "none";
                                $nayta_dia = "inline";
                            }else{
                                $nayta_stream = "none";
                                $nayta_dia = "none";
                            }
                            $text .= "<tr class=\"".$data['f_id']."\"><td>".form::input("ident-".$data['f_id'],$data['tunniste'],array("size" => "20","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".form::select("show_tv-".$data['f_id'],array("Diashow","Streami","Yksittäinen dia"),$data['show_tv'],array("id"=>$data['f_id']."-tv","onchange"=>"check(this.value,\"".$data['f_id']."\");$(this).addClass(\"new\");$(\"#".$data['f_id']."-stream\").addClass(\"new\");$(\"#".$data['f_id']."-dia\").addClass(\"new\");")).form::select("show_stream-".$data['f_id'],$streams,$data['show_stream'],array("id"=>$data['f_id']."-stream","onchange"=>"$(this).addClass(\"new\");","style" => "display:$nayta_stream;")).form::select("dia-".$data['f_id'],$diat,$data['dia'],array("id"=>$data['f_id']."-dia","onchange"=>"$(this).addClass(\"new\");","style" => "display:$nayta_dia;"))."</td><td>".form::checkbox("use_global-".$data['f_id'],1,(boolean)$data['use_global'],array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; background-color: transparent;\">&nbsp;</td></tr>";
                        }
                        $text .= "</tbody></table>".form::close();
                    }else{
                        $text = "<p>Yhtään aktiivista frontendiä ei löytynyt.</p>";
                    }
                    $return = array("ret" => $text);
                    break;
                case "upload"://käsittele tiedoston uploadaukset.
                    $upload_handler = new Upload();

                    header('Pragma: no-cache');
                    header('Cache-Control: private, no-cache');
                    header('Content-Disposition: inline; filename="files.json"');
                    header('X-Content-Type-Options: nosniff');

                    if($this->request->query('del') == 1){
                        $upload_handler->delete();
                    }else{
                        switch ($_SERVER['REQUEST_METHOD']) {
                            case 'HEAD':
                            case 'GET':
                                $upload_handler->get();
                                break;
                            case 'POST':
                                $upload_handler->post();
                                break;
                            case 'DELETE':
                                $upload_handler->delete();
                                break;
                            default:
                                header('HTTP/1.0 405 Method Not Allowed');
                        }
                    }
                    break;
                case "ohjelma"://prosessoi ohjelmdakarttatiedosto.
                    $post = $_POST;

                    $ohjelma_data = array();
                    $salinimet = array();
                    //<siviskoodi>
                    $fp = @fopen(__documentroot."files/".$post['file'], "r");
                    if($fp === FALSE)
                        $ret = "Tiedostoa ei pysytty avaamaan!";
                    while(!feof($fp)){//[päivä] [sali] [alkuaika] [kesto|nimi|järjestäjä|tyyppi|kuvaus]
                        $buf = $this->utf8(fgets($fp));
                        if(!strncasecmp("O::", $buf, 3)){
                            $flag = 0;
                            $tmparr = explode("::", $buf);
                            $paiva = trim($tmparr[1]);

                            $alkuaika = constant("ALKUAIKA_".$paiva);

                            $salinimi = strtolower(str_replace(" ", "_", trim($tmparr[2])));
                            $aika = intval(trim($tmparr[3]))-$alkuaika+1;
                            if(!isset($salinimet[$salinimi]))
                                $salinimet[$salinimi] = trim($tmparr[2]);
                            $curohjelma =& $ohjelma_data[trim($tmparr[1])][$salinimi][$aika];
                            $curohjelma = array(
                                            "kesto" => trim($tmparr[4]),
                                            "nimi" => str_replace("&", "&amp;", trim($tmparr[5])),
                                            "jarjestaja" => str_replace("&", "&amp;", trim($tmparr[6])),
                                            "tyyppi" => strtolower(trim($tmparr[7])),
                                            "kuvaus" => "",
                                            );
                        }elseif(isset($curohjelma)){
                            if($flag){
                                $curohjelma["kuvaus"] .= '</p><p>';
                            }
                            $flag = 1;
                            $curohjelma["kuvaus"] .= str_replace("&", "&amp;", $buf);
                        }
                    }
                    fclose($fp);
                    //</siviskoodi>
                    if(!isset($ohjelma_data["Lauantai"])){//tää vaatinee tapahtumakohtasta puukkoa mut..
                        $ret = "Tiedoston syntaksi ei ole käyttökelpoinen.";
                    }else{//data = ok.
                        $q1 = DB::query(Database::DELETE,//eka vanhat pois
                                        "TRUNCATE ohjelmadata"
                                        )->execute(__db);
                        $error = 0;
                        foreach($ohjelma_data as $paiva => $d1){
                            foreach($d1 as $sali => $d2){
                                foreach($d2 as $alkuaika => $data){
                                    $query = DB::query(Database::INSERT,//ja uudet tilalle.
                                                        "INSERT INTO ohjelmadata ".
                                                        "           (paiva ".
                                                        "           ,sali ".
                                                        "           ,alku ".
                                                        "           ,kesto ".
                                                        "           ,nimi ".
                                                        "           ,jarjestaja ".
                                                        "           ,tyyppi ".
                                                        "           ,kuvaus ".
                                                        "           ,`update` ".
                                                        "           ) ".
                                                        "VALUES     (:paiva ".
                                                        "           ,:sali ".
                                                        "           ,:alku ".
                                                        "           ,:kesto ".
                                                        "           ,:nimi ".
                                                        "           ,:jarjestaja ".
                                                        "           ,:tyyppi ".
                                                        "           ,:kuvaus ".
                                                        "           ,NOW() ".
                                                        "           )"
                                                        );
                                    $query->parameters(array(
                                                            ":paiva"      => $paiva,
                                                            ":sali"       => $sali,
                                                            ":alku"       => $alkuaika,
                                                            ":kesto"      => $data['kesto'],
                                                            ":nimi"       => $data['nimi'],
                                                            ":jarjestaja" => $data['jarjestaja'],
                                                            ":tyyppi"     => $data['tyyppi'],
                                                            ":kuvaus"     => $data['kuvaus']
                                                            ));
                                    list($insert_id, $affected_rows) = $query->execute(__db);
                                    if($affected_rows == 0){
                                        $error = 1;
                                    }
                                }
                            }
                        }
                        if($error){
                            $ret = "Ohjelmakartan päivityksessä tapahtui virhe. Yritä uudelleen.";
                        }else{
                            $ret = "Ohjelmakartan päivitys onnistui!";
                        }
                    }
                    $return = array("ret" => $ret);
                    break;
                case "lastupdate"://x)
                    $return = array("ret" => date("d.m.Y H:i"));
                    break;
                case "todo_save":
                  $post = $_POST;
                  $query = DB::query(Database::INSERT,
                                      'INSERT INTO logi '.
                                      '           (tag '.
                                      '           ,comment '.
                                      '           ,adder '.
                                      '            ) '.
                                      'VALUES     ( '.
                                      '            :tag '.
                                      '           ,:comment '.
                                      '           ,:adder '.
                                      '            )'
                                      );
                  $query->parameters(array(":tag"     => $post['tag']
                                          ,":comment" => $post['comment']
                                          ,":adder"   => $post['adder']
                                          ));
                  $result = $query->execute(__db);
                  $return = array("ret"=>"Rivi lisätty onnistuneesti.");
                  break;
              case "todo_refresh":
                    $query = DB::query(Database::SELECT,
                                       'SELECT   tag '.
                                       '        ,comment '.
                                       '        ,adder '.
                                       '        ,stamp '.
                                       'FROM     logi '.
                                       'ORDER BY stamp DESC'
                                       )->execute(__db);
                    $text = "<table class=\"stats\"><tr><th>Aika</th><th>Tyyppi</th><th>Viesti</th><th>Lisääjä</th></tr>";
                    $types = array("Löytötavara","Ongelma","Tiedote","Kysely","Muu");
                    foreach($query as $row){
                        $text .= "<tr class=\"type-".$row['tag']."\"><td>".date("d.m. H:i",strtotime($row['stamp']))."</td><td>".$types[$row['tag']]."</td><td>".$row['comment']."</td><td>".$row['adder']."</td></tr>";
                    }
                    $text .= "</table>";
                    $return = array("ret"=>$text);
                    break;
              case "populate_logi":
                    $query = DB::query(Database::INSERT,
                                        'INSERT INTO logi (tag,comment,adder) '.
                                        'VALUES (:tag,:comment,:adder)'
                                        );
                    for($i=1;$i<600;$i++){
                        $query->parameters(array(":tag"=>($i%5),":comment"=>"Kommentti $i",":adder"=>"Automagia"))->execute(__db);
                    }
                    break;
            }
    	}else{//Jos käyttäjä ei ole kirjautunut sisään, tai ei ole admin. Estää abusoinnin siis.
        	if(empty($_SERVER['HTTP_REFERER'])) $referer = "";//pitää tehdä vaikeesti koska kohanassa ei oo suoraa tähän funkkaria.
        	else $referer = $_SERVER['HTTP_REFERER'];
            $ref = substr_replace(URL::base($this->request), "", $referer);
            $data = "<p>Sessio on vanhentunut. ".html::file_anchor('admin/?return='.$ref,'Kirjaudu uudelleen').", palaat takaisin tälle sivulle.</p>";
            $return = array("ret" => $data);
        }
        if($param1 != "upload")//upload ei tykänny ylimääräsestä "" json-palautuksen lopussa.
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
                            )->execute(__db);
        foreach($query as $row){
            $this->session->set($row['opt'],$row['value']);
            $this->session->set("g-".$row['opt'],$row['value']);
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

    //Helppoa tinymce-implementointia varten.
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
                    		theme_advanced_buttons1 : \"save,preview,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontsizeselect,removeformat,|,cut,copy,paste,|,search,replace,|,image,advhr,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,forecolor,backcolor,|,cancel\",
                        	theme_advanced_buttons2 : \"\",
                        	theme_advanced_buttons3 : \"\",
                    		theme_advanced_toolbar_location : \"external\",
                    		theme_advanced_toolbar_align : \"center\",
                    		theme_advanced_font_sizes: \"40px,45px,50px,60px,70px,80px,90px,100px\",
                    		theme_advanced_statusbar_location : \"bottom\",

                    		//Font tweaking
                            theme_advanced_fonts : 'Helvetica=helvetica,arial,sans-serif;',
                            style_formats : [
                                {title : 'Paragraph', inline : 'p'},
                                {title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
                                {title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
                                {title : 'Example 1', inline : 'span', classes : 'example1'},
                                {title : 'Example 2', inline : 'span', classes : 'example2'},
                                {title : 'Table styles'},
                                {title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
                            ],

                    		// Little tweaking.
                    		theme_advanced_resizing : false,
                    		force_br_newlines : true,
                            force_p_newlines : false,
                            forced_root_block : '',
                            save_onsavecallback : \"tinymce_tallenna\",
                            save_oncancelcallback : \"tinymce_poista\",
                            imagemanager_contextmenu: true,
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
                            body_id : \"text\",
                    		body_class : \"main\",

                            setup : function(ed) {//on-demand hack cancel-napin tooltipin vaihtoon.
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