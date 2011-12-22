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
            $this->view->header->js .= "\n<script type=\"text/javascript\">
                                    $(function() {
                                        $( 'button, input:submit' ).button();
                                    });
                                    </script>
                                        ";
        	$this->view->header->login = "";//oletuksena nää on tyhjiä
        	$this->view->header->show = "";
            if($this->session->get('logged_in') && $this->request->action() != 'logout'){//mutta jos ollaan kirjauduttu sisään, eikä kirjautumassa ulos

                $this->view->header->login = "Kirjautunut käyttäjänä: ".$this->session->get('user')."<br />".html::file_anchor('admin/logout','Kirjaudu ulos');//ja näytetään kirjautunut käyttäjä, uloskirjautumislinkki, ja globaali hallinta.

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
    public function action_face(){
        $page = $this->request->param('param1',null);
        $param1 = $this->request->param('param2',null);
    	if(!$this->session->get('logged_in')){
			$this->request->redirect('admin/?return='.$this->request->uri()); // Ohjataan suoraan kirjautumiseen, mikäli yritetään avata tämä sivu ei-kirjautuneena
    	}elseif($this->session->get('level',0) < 1){//2. parametri = defaulttaa nollaks
        	$this->view->content->text = '<p>Valitettavasti sinulla ei ole ylläpito-oikeuksia.</p>';
        	$this->view->content->links = "";
    	}else{
        	//<linkkipalkki>
            $pages = array("tvadm" => array("scroller","rulla","dia","streams","frontends","ohjelmakartta"),"info" => array("logi","lipunmyynti","tiedotteet","tuotanto","ohjelma"),"bofh" => array("clients","users"));
            $this->session->set('results',array());
            function search($array,$key,$search){
                $data = array_search($search,$array);
                if($data === false){
                }else{
                    array_push($_SESSION['results'],$key);
                }
            }
            array_walk($pages,"search",$page);
            $resultsi = $this->session->get('results',array());
            if(!isset($resultsi[0])){
                $active = "false";
            }elseif($resultsi[0] == "tvadm"){
                $active = 0;
            }elseif($resultsi[0] == "info"){
                $active = 1;
            }elseif($resultsi[0] == "bofh"){
                $active = 2;
            }
        	$this->view->header->js .= "
            \n<script type=\"text/javascript\">
                $(function(){
                    $(\"#links .btn\").button({
                        icons:{
                            primary: \"ui-icon-triangle-1-e\"
                        }
                    });
                    $(\"#links a.head-links\").click(function(event){
                        event.preventDefault();
                    });
                    $(\"#accord\").accordion({active:".$active.",autoHeight: false,icons:{ 'header': 'ui-icon-plus', 'headerSelected': 'ui-icon-minus' }});

                });

            </script>
            ";


			$this->view->content->links = "\n<div id=\"accord\">\n";
    			$this->view->content->links .= "\n<h3><a href=\"#\" class=\"head-links\">TV-ylläpito:</a></h3>";
        	    $this->view->content->links .= "\n<div><ul>";
            	    $this->view->content->links .= "\n".form::button("scroller","Scroller",array("onclick"=>"parent.location='".url::base($this->request)."admin/face/scroller'", "class" => "btn"))."<br/>";
            	    $this->view->content->links .= "\n".form::button("rulla","Rulla",array("onclick"=>"parent.location='".url::base($this->request)."admin/face/rulla'", "class" => "btn"))."<br/>";
            	    $this->view->content->links .= "\n".form::button("dia","Diat",array("onclick"=>"parent.location='".url::base($this->request)."admin/face/dia'", "class" => "btn"))."<br/>";
            	    $this->view->content->links .= "\n".form::button("streams","Streamit",array("onclick"=>"parent.location='".url::base($this->request)."admin/face/streams'", "class" => "btn"))."<br/>";
            	    $this->view->content->links .= "\n".form::button("frontends","Frontendit",array("onclick"=>"parent.location='".url::base($this->request)."admin/face/frontends'", "class" => "btn"))."<br/>";
            	    $this->view->content->links .= "\n".form::button("ohjelmakartta","Ohjelmakartta",array("onclick"=>"parent.location='".url::base($this->request)."admin/face/ohjelmakartta'", "class" => "btn"))."<br/>";
        	    $this->view->content->links .= "\n</ul></div>";
        	    $this->view->content->links .= "\n<h3><a href=\"#\" class=\"head-links\">Info:</a></h3>";
        	    $this->view->content->links .= "\n<div><ul>";
            	    $this->view->content->links .= "\n".form::button("logi","Lokikirja",array("onclick"=>"parent.location='".url::base($this->request)."admin/face/logi'", "class" => "btn"))."<br/>";
            	    $this->view->content->links .= "\n".form::button("lipunmyynti","Lipunmyynti",array("onclick"=>"parent.location='".url::base($this->request)."admin/face/lipunmyynti'", "class" => "btn"))."<br/>";
            	    $this->view->content->links .= "\n".form::button("tiedotteet","Tiedotteet",array("onclick"=>"parent.location='".url::base($this->request)."admin/face/tiedotteet'", "class" => "btn"))."<br/>";
            	    $this->view->content->links .= "\n".form::button("tuotanto","Tuotantosuunnit.",array("onclick"=>"parent.location='".url::base($this->request)."admin/face/tuotanto'", "class" => "btn"))."<br/>";
            	    $this->view->content->links .= "\n".form::button("ohjelma","Ohjelma",array("onclick"=>"parent.location='".url::base($this->request)."admin/face/ohjelma'", "class" => "btn"))."<br/>";
                $this->view->content->links .= "\n</ul></div>";
                if($this->session->get("level",0) >= 3){
                    $this->view->content->links .= "\n<h3><a href=\"#\" class=\"head-links\">BOFH:</a></h3>";
            	    $this->view->content->links .= "\n<div><ul>";
                	    $this->view->content->links .= "\n".form::button("clients","Clientit",array("onclick"=>"parent.location='".url::base($this->request)."admin/face/clients'", "class" => "btn"))."<br/>";
                	    $this->view->content->links .= "\n".form::button("users","Käyttäjät",array("onclick"=>"parent.location='".url::base($this->request)."admin/face/users'", "class" => "btn"))."<br/>";
                    $this->view->content->links .= "\n</ul></div>";
                }
            $this->view->content->links .= "\n</div><br/><ul>";
            $this->view->content->links .= "\n".form::button("dashboard","Dashboard",array("onclick"=>"parent.location='".url::base($this->request)."admin/face/dashboard'", "class" => "btn"))."<br/><br/>";
            $this->view->content->links .= "\n".form::button("logout","Kirjaudu ulos",array("onclick"=>"parent.location='".url::base($this->request)."admin/logout'", "class" => "btn"))."<br/>";
            $this->view->content->links .= "\n".form::button("infotv","Info-TV",array("onclick"=>"parent.location='".url::base($this->request)."'", "class" => "btn"))."<br/>";
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
               		$this->view->content->text = "<p>Olet nyt Info-TV:n hallintapaneelissa. Ole hyvä ja valitse toiminto valikosta.</p><p>Mikäli jokin data ei ole jollakin sivulla päivittynyt, lataa sivu uudelleen.</p>
                                                   <p>Debug-dataa:<br /><pre>".print_r($_SESSION,true)."</pre></p>";
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
                    fetch = \''.URL::base($this->request).'ajax/scroller_save/\'
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
                    fetch = \''.URL::base($this->request).'ajax/scroller_load/\'
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
                        fetch = \''.URL::base($this->request).'ajax/scroller_delete/\' + row;
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
                    fetch = \''.URL::base($this->request).'ajax/rulla_row/\' + id
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
                    fetch = \''.URL::base($this->request).'ajax/rulla_save/\'
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
                    fetch = \''.URL::base($this->request).'ajax/rulla_load/\'
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
                        fetch = \''.URL::base($this->request).'ajax/rulla_delete/\' + row;
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
        $this->view->content->text .= "</tbody></table>".form::close()."</div>".form::button("moar","Lisää rivi",array("id"=>"lisarivi","onclick"=>"addrow();"))."<br/><br/><p><strong>MUISTA TALLENTAA MUUTOKSESI!</strong></p><p><strong>HUOM!</strong><ul><li>Numerointi voi hyppiä numeroiden yli, ne näytetään pienimmästä suurimpaan. <li><strong>Älä käytä kohtaa 0!</strong> Rivi ei tallennu tällöin.<li>Twitter-feediä ei voi olla kuin yksi. Ensimmäisen jälkeiset ovat vain tyhjiä dioja.</li><li>Diat näkyvät noin sekunnin pidempään kuin määrität tässä.</li></ul></p>".
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
                        fetch = \''.URL::base($this->request).'ajax/dia_load/\' + id;
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
                        fetch = \''.URL::base($this->request).'ajax/dia_save/\'
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
                            fetch = \''.URL::base($this->request).'ajax/dia_delete/\' + gid;
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
                        fetch = \''.URL::base($this->request).'ajax/stream_delete/\' + id;
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
                    fetch = \''.URL::base($this->request).'ajax/stream_save/\'
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
                    fetch = \''.URL::base($this->request).'ajax/stream_load/\'
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
        $disabled = "";

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
                        fetch = \''.URL::base($this->request).'ajax/fronted_delete/\' + id;
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
                    fetch = \''.URL::base($this->request).'ajax/frontend_save/\'
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
                    fetch = \''.URL::base($this->request).'ajax/frontend_load/\'
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
        $this->view->content->text = "<h2>Frontend-hallinta</h2><p>Tällä sivulla voit tarvittaessa pakottaa jonkin frontendin näyttämään vaikkapa pelkkää diashowta, esim. infossa.</p><h4>Globaali hallinta:</h4><br/>";
        //<globaali hallinta>

        if($this->session->get("g-show_tv",false) === false){//2. parametri = mihin defaultataan.
            $this->get_set();//määritelty alempana, asettaa g_show_tv ja g_show_stream sessiomuuttujat.
        }
        if($this->session->get("g-show_tv") == 1) $nayta = "true";
        else $nayta = "false";
        $this->view->header->js .= "\n<script type=\"text/javascript\">
                                    $(function() {
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
                                        fetch = '".URL::base($this->request)."ajax/tv/'
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
                                            },1000);
                                        },200);
                                    }
                                      </script>
                                    ";
        if($this->session->get("g-show_tv"))
            $show = $this->session->get("g-show_tv");//pistetään valikoihin oikeet arvot
        else
            $show = false;
        if($this->session->get("g-show_stream"))
            $striim = $this->session->get("g-show_stream");
        else
            $striim = false;

        $this->view->content->text .= "Näytä:".form::select("show",array("Diashow","Streami"),$show,array("id"=>"show_tv","onchange"=>"check_show(this.value);$(this).addClass(\"new\");$(\"#show_stream\").addClass(\"new\");"))
                                      .form::select("streams",$this->get_streams(),$striim,array("id"=>"show_stream","onchange"=>"$(this).addClass(\"new\");"))
                                      .form::button("apply","Vaihda",array("onclick"=>"show_save();"))."<br/><div id=\"span_cont\" style=\"min-height:25px;\"><div id=\"show_feed\"></div></div><hr><br/>";

        //</globaali hallinta>
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
                            fetch = \''.URL::base($this->request).'ajax/ohjelma/\'
                            $.post(fetch, { "file": filename }, function(data){
                                $("#baari").hide(\'blind\',\'300\');
                                window.setTimeout(function(){
                                    $("#baari").html(data.ret);
                                    $("#baari").show(\'300\');
                                    window.setTimeout(function(){
                                        bar.hide(\'blind\',\'slow\');
                                        fetch = \''.URL::base($this->request).'ajax/upload/?file=\' + filename + \'&del=1\';
                                        $.getJSON(fetch,function(data){
                                            $("tr.template-download").hide(\'medium\');
                                        });
                                        fetch = \''.URL::base($this->request).'ajax/lastupdate/\';
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
                                            form::open(URL::base($this->request).'ajax/upload', array('enctype' => 'multipart/form-data','method' => 'post')).
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
                    ref();
                });

                $(function() {
                    $("#filter_cont").delegate("input","keyup",function(event) {
                        search();
                    })
                });

                function refresh(){
                    var container = $("#table");
                    fetch = \''.URL::base($this->request).'ajax/todo_refresh/\'
                    $.getJSON(fetch,function(data) {
                        container.html(data.ret);
                    });
                    window.setTimeout(function(){
                        var theTable = $(\'table.stats\');
                        var arvo = $("#filter").val();
                        $.uiTableFilter( theTable, arvo );
                    },80);
                    return false;
                };

                function search(){
                    var container = $("#table");
                    var search = $("#filter").val();
                    fetch = \''.URL::base($this->request).'ajax/todo_search/\'
                    $.post(fetch,{ "search": search},function(data) {
                        container.html(data.ret);
                    },"json");
                    return false;
                };

                function save(){
                    var container = $("#feedback");
                    container.hide(\'fast\');
                    fetch = \''.URL::base($this->request).'ajax/todo_save/\'
                    $.post(fetch,$("#form").serialize(),function(data) {
                        container.html(data.ret);
                        if(data.ok)
                            $( "form" )[ 0 ].reset();
                    },"json");
                    container.show(\'drop\',{ direction: "right", distance: "-50px" },500);
                    refresh();

                    window.setTimeout(function(){
                        container.hide(\'drop\',{ direction: "right", distance: "100px" },1000);
                    },4000);
                    return false;
                };

                function ref(){
                    search();
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
        $types = array("tiedote"=>"Tiedote","ongelma"=>"Ongelma","kysely"=>"Kysely","löytötavara"=>"Löytötavara","muu"=>"Muu");
        $add = form::open(null, array("onsubmit" => "save(); return false;", "id" => "form"))."Lisää rivi:<br />".form::label('tag',' Tyyppi:').form::select('tag',$types,2,array("id"=>"tag")).form::label('comment',' Viesti:').form::input('comment',null,array("id"=>"com","size"=>"56")).form::label('adder',' Lisääjä:').form::input('adder',null,array("id"=>"adder","size"=>"5")).form::submit(null,'Lisää').form::close()."\n";
        $this->view->content->text .= "<div id=\"filter_cont\" style=\"float:right;margin-top:-30px;\">Suodatus/haku: ".form::input('filter',null,array("id"=>"filter"))."</div><div id=\"add\">$add</div><div id=\"feed_cont\" style=\"min-height:20px;\"><div id=\"feedback\"></div></div>
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
                <script type=\"text/javascript\" src=\"".URL::base($this->request)."tiny_mce/3.4.7/jquery.tinymce.js\"></script>
                <script type=\"text/javascript\"><!--
                function tinymce_setup(){
                    $(function(){
                    	$('textarea.tinymce').tinymce({
                        	script_url : \"".URL::base($this->request)."tiny_mce/3.4.7/tiny_mce.js\",

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