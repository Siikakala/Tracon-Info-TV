<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Admin-controlleri hallintapuolelle..
 *
 * @author Miika Ojamo <miika@vkoski.net>
 */
class Controller_Admin extends Controller {
	public function before()
	{
		$db = Database::instance();
		$this->session = Session::instance();
		if (!defined("__tableprefix")) {
			$tb = DB::query(Database::SELECT, "SELECT value FROM config WHERE opt = 'tableprefix'")->execute(__db)->get('value', date('Y'));
			if ($tb == 0) {
				$tb = "dev";
			}
			define("__tableprefix", $tb);
		}
		$this->view = new View('admin');
		$this->view->header = new view('admin_header');
		$this->view->content = new view ('admin_content');
		$this->view->footer = new view('admin_footer');
		$this->view->header->title = "";
		$this->view->footer->dialogs = "";
		$this->view->header->css  = HTML::style('css/eggplant/jquery-ui.css');
		$this->view->header->css .= HTML::style('css/admin_small.css');
		$this->view->header->js  = "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "jquery/jquery-2.0.3.min.js\"></script>";
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "jquery/jquery-ui-1.10.3.custom.min.js\"></script>";
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "jquery/jquery.metadata.js\"></script>";
        $this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "jquery/jquery.themeswitcher.js\"></script>";
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "js/MD5.js\"></script>";
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "js/chat.js\"></script>";
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "js/pages/common.js\"></script>";
		// $this->view->header->js .= "\n<script src=\"http://yui.yahooapis.com/3.4.0/build/yui/yui-min.js\"></script>";
		$this->view->header->js .= "\n<script type=\"text/javascript\">
		var baseurl = '" . URL::site('/') . "'
		var usrlvl = '" . $this->session->get('level', 0) . "'
		var usr = '" . $this->session->get('user', 0) . "'
		var nick = '" . $this->session->get('nick', null) . "'
		var page = '" . $this->request->action() . "'
		var subpage = '".$this->request->param('param1', null)."'
		var begindate = '" . date("d.m.Y", strtotime(Jelly::query('tapahtuma')->limit(1)->select()->get('alkuaika'))) . "'
		if(begindate == \"01.01.1970\"){
		begindate = \"today\";
		}
        $(function(){
            $('#switcher').themeswitcher({
                themePath: '".URL::site('/')."css/',
                imgPath: '".URL::site('/')."images/themes/',
                loadTheme: 'eggplant',
                height: 400
            });
        });
		</script>
		";
		$this->view->header->login = ""; //oletuksena nää on tyhjiä
		$this->view->header->show = "";
		$this->view->header->helppi = "Kirjaudu sisään nähdäksesi ohjeet.";
		if ($this->session->get('logged_in') && $this->request->action() != 'logout') { // mutta jos ollaan kirjauduttu sisään, eikä kirjautumassa ulos
			$this->view->header->login = "Kirjautunut käyttäjänä: " . $this->session->get('user') . "<br />" . HTML::file_anchor('admin/logout', 'Kirjaudu ulos'); //ja näytetään kirjautunut käyttäjä, uloskirjautumislinkki, ja globaali hallinta.
			$this->view->header->helppi = new view("global_help");
			$this->view->header->helppi->page = $this->request->param('param1', null);
			if ($this->session->get('level', 0) >= 3) {
				$this->view->header->show = "Loadit: " . `cat /proc/loadavg|awk '{print $1,$2,$3}'`;
			}
		}
	}

	/**
	 * Default-metodi, tarkistaa että onko kirjauduttu sisään, jollei, näyttää kirjautumisformin, muussa tapauksessa forwardaa eteenpäin
	 */
	public function action_index()
	{
		if (!$this->session->get('logged_in')) {
			$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "js/pages/login.js\"></script>";
			$this->view->content->text = "<h2>Kirjaudu sisään</h2><div style=\"margin-left:0px;\">";
			$this->view->content->text .= Form::open("admin", array("id" => "login", "onsubmit" => "return false;", "style" => "float:left;"));
			$this->view->content->text .= "<table><tr><td>";
			$this->view->content->text .= Form::label('user', 'Käyttäjätunnus:') . "</td><td>";
			$this->view->content->text .= Form::input('user', null, array('id' => 'user')) . "</td></tr><tr><td>";
			$this->view->content->text .= Form::label('pass', 'Salasana:') . "</td><td>";
			$this->view->content->text .= Form::password('pass', null, array('id' => 'pass')) . "</td></tr><tr><td></td><td>";
			$this->view->content->text .= Form::submit('submit', 'Kirjaudu', array('onclick' => 'login(); return false;'));
			$this->view->content->text .= "</td></tr></table>";
			$this->view->content->text .= Form::close();
			$this->view->content->text .= "</div><div style=\"min-height:15px;margin-top:120px;\"><div id=\"feedback\"><span style=\"color:red\">Selaimesi Javascript ei ole käytössä. Ilman sitä et voi käyttää järjestelmää.</span></div></div>";
			$this->view->content->links = "";
			$this->response->body($this->view->render());
		} else
		if (isset($_GET['return'])) $this->redirect($_GET['return'],302);
		else $this->redirect('admin/face',302);
	}

	/**
	 * Päämetodi. Kaikki private funktio-kutsut kulkee tämän kautta. Viewi myös rendataan tämän lopussa.
	 * Alustaa admin-näkymän, ja lisää sinne kiinteät elementit, sekä hakee privafunkkareista muuttuvat.
	 *
	 * Suurin osa magiasta tapahtuu kuitenkin ajax-metodissa.
	 */
	public function action_face()
	{
		$page = $this->request->param('param1', null);
		$param1 = $this->request->param('param2', null);
		if (!$this->session->get('logged_in')) {
			$this->redirect('admin/?return=' . $this->request->uri(),302); // Ohjataan suoraan kirjautumiseen, mikäli yritetään avata tämä sivu ei-kirjautuneena
		} elseif ($this->session->get('level', 0) < 1) { // 2. parametri = defaulttaa nollaks
			$this->view->content->text = '<p>Valitettavasti sinulla ei ole ylläpito-oikeuksia.</p>';
			$this->view->content->links = "";
		} else {
			// <linkkipalkki>
			$pages = array("tvadm" => 
                                array("scroller", "rulla", "dia", "streams", "frontends", "video"), 
                           "info" => 
                                array("logi", "lipunmyynti", "tiedotteet", "tuotanto", "ohjelma", "tekstarit"), 
                           "bofh" => 
                                array("clients", "users", "settings")
                            );
			$this->session->set('results', array());
			function search($array, $key, $search)
			{
				$data = array_search($search, $array);
				if ($data === false) {
				} else {
					array_push($_SESSION['results'], $key);
				}
			}
			array_walk($pages, "search", $page);
			$resultsi = $this->session->get('results', array());
			if (!isset($resultsi[0])) {
				$active = "false";
			} elseif ($resultsi[0] == "tvadm") {
				$active = 0;
			} elseif ($resultsi[0] == "info") {
				$active = 1;
			} elseif ($resultsi[0] == "bofh") {
				$active = 2;
			}
			$this->view->header->js .= "\n<script type=\"text/javascript\">
			$(function() {
			    $(\"#accord\").accordion({active:" . $active . ",heightStyle: 'content',icons:{ 'header': 'ui-icon-plus', 'activeHeader': 'ui-icon-minus' }});
            });
			</script>";

			$this->view->content->links = new view ('pages/links');
			$this->view->content->links->baseurl = URL::site('/');
			$this->view->content->links->level = $this->session->get('level', 0);
			// </linkkipalkki>
			switch ($page) { // linkkien käsittely
				case "scroller":
					$this->scroller($param1);
					$this->view->header->title .= " &raquo; Scroller";
					break;
				case "rulla":
					$this->rulla($param1);
					$this->view->header->title .= " &raquo; Rulla";
					break;
				case "dia":
					$this->dia($param1);
					$this->view->header->title .= " &raquo; Dia";
					break;
				case "streams":
					$this->streams($param1);
					$this->view->header->title .= " &raquo; Streamit";
					break;
				case "frontends":
					$this->frontends($param1);
					$this->view->header->title .= " &raquo; Frontendit";
					break;
				case "video":
					$this->video($param1);
					$this->view->header->title .= " &raquo; Videolähetys";
					break;
				case "logi":
					$this->logi($param1);
					$this->view->header->title .= " &raquo; Lokikirja";
					break;
				case "dashboard":
					$this->dashboard($param1);
					$this->view->header->title .= " &raquo; Dashboard";
					break;
				case "users":
					$this->users($param1);
					$this->view->header->title .= " &raquo; Käyttäjät";
					break;
				case "ohjelma":
					$this->ohjelma($param1);
					$this->view->header->title .= " &raquo; Ohjelma";
					break;
				case "tuotanto":
					$this->tuotanto($param1);
					$this->view->header->title .= " &raquo; Tuotantosuunnitelma";
					break;
				case "settings":
					$this->asetukset($param1);
					$this->view->header->title .= " &raquo; Asetukset";
					break;
				case "tekstarit":
					$this->tekstarit($param1);
					$this->view->header->title .= " &raquo; Tekstiviestit";
					$this->view->footer->dialogs = new view('dialogs/tekstari-help');
					break;
				default:
					$this->view->content->text = "<p>Olet nyt Info-TV:n hallintapaneelissa. Ole hyvä ja valitse toiminto valikosta.</p><p>Mikäli jokin data ei ole jollakin sivulla päivittynyt, lataa sivu uudelleen.</p>";
					if (__db == "dev") $this->view->content->text .= "<p>Debug-dataa:<br /><pre>" . print_r($_SESSION, true) . "</pre></p>";
					break;
			}
		}
		$this->response->body($this->view->render());
	}

	private function scroller($param1)
	{
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "js/pages/scroller.js\"></script>";

		$model = new Model_Pages_Scroller();

		$this->view->content->text = $model->page();
	}

	private function rulla($param1)
	{
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "js/pages/rulla.js\"></script>";

		$query = Jelly::query('rulla')->where('instance', '=', $this->session->get('instance', 1))->order_by('pos')->select();
		$instances = $this->get_instances();
		if ($query->count() > 0)
			$result = $query;
		else
			$result = false;

		$query2 = Jelly::query('diat')->order_by('dia_id')->select();
		if ($query2->count() > 0)
			$result2 = true;
		else
			$result2 = false;

		$vaihtehdot = array();
		$vaihtoehdot[0] = "twitter";
		if ($result2)foreach($query2 as $data) {
			$vaihtoehdot[$data->dia_id] = $this->utf8($data->tunniste);
		} else
			$vaihtoehdot = false;

		$this->view->content->text = new view('pages/rulla');
		$this->view->content->text->tablebody = "";
		$this->view->content->text->instances = Form::select('instance', $instances, $this->session->get('instance', 1), array("onChange" => "set_instance(this.value);window.setTimeout(function(){refresh_data();},200);"));

		if ($result) foreach($result as $data) {
			if ($data->type == 2)
				$selector = 0;
			else
				$selector = $data->selector;
			$this->view->content->text->tablebody .= "<tr class=\"" . $data->rul_id . "\"><td>" . Form::input('pos-' . $data->rul_id, $data->pos, array("size" => "1", "onkeypress" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td>" . Form::select('text-' . $data->rul_id, $vaihtoehdot, $selector, array("onchange" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td>" . Form::select('time-' . $data->rul_id, Date::seconds(1, 1, 121), $data->time, array("onchange" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td>" . Form::checkbox('hidden-' . $data->rul_id, 1, (boolean)$data->hidden, array("onchange" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del ignore\" onclick=\"dele(" . $data->rul_id . ")\" >X</a></td></tr>";
		}
	}

	private function dia($param1)
	{
		$this->view->header->js .= $this->tinymce(__tableprefix . "-tv.css");
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "js/pages/dia.js\"></script>";
		$this->view->content->text = new view('pages/dia');

		$query = Jelly::query('diat')->order_by('dia_id')->select();
		$result[0] = "";
		if ($query->count() > 0)
			foreach($query as $data) {
			$result[$data->dia_id] = $this->utf8($data->tunniste);
		} else
			$result[0] = false;
		$salit = Jelly::query('salit')->select_column('tunniste')->select();
		$this->view->content->text->salit = implode(", ", array_keys($salit->as_array("tunniste")));

		$this->view->content->text->select = Form::select("dia", $result, 0, array("onchange" => "load(this.value)", "id" => "dia_sel"));
	}

	private function streams($param1)
	{
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "flowplayer/flowplayer-3.2.6.min.js\"></script>";
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "js/pages/streams.js\"></script>";

		$this->view->content->text = new view('pages/stream');

		$query = Jelly::query('streamit')->order_by('jarjestys')->select();
		if ($query->count() > 0) {
			$result = true;
		} else {
			$result = false;
		}
		$disabled = "";

		$this->view->content->text->tablebody = "";

		if ($result) foreach($query as $data) {
			$this->view->content->text->tablebody .= "<tr class=\"" . $data->stream_id . "\"><td>" . Form::input("ident-" . $data->stream_id, $data->tunniste, array($disabled, "class" => "tunniste", "size" => "15", "onkeypress" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td>" . Form::input("url-" . $data->stream_id, $data->url, array($disabled, "id" => $data->stream_id, "class" => "url", "size" => "35", "onkeypress" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td>" . Form::input("jarkka-" . $data->stream_id, $data->jarjestys, array($disabled, "size" => "1", "onkeypress" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px; background-color: transparent;\"><a href=\"javascript:;\" class=\"del ignore\" onclick=\"dele(" . $disabled . $data->stream_id . ")\">X</a></td><td style=\"border:0px; border-bottom-style: none; padding: 0px; background-color: transparent;\"><a href=\"javascript:;\" onclick=\"load(" . $data->stream_id . ");\">&nbsp;Esikatsele</a></td></tr>";
		}
	}

	private function get_streams()
	{
		$query = Jelly::query('streamit')->order_by('jarjestys')->select();
		$ret = array();
		foreach($query as $row) {
			$ret[$row->stream_id] = $row->tunniste;
		}
		return $ret;
	}

	private function frontends()
	{
		$this->view->content->text = new view('pages/frontend');
		// <globaali hallinta>
		if ($this->session->get("g-show_tv", false) === false) { // 2. parametri = mihin defaultataan.
			$this->get_set(); //määritelty alempana, asettaa g_show_tv ja g_show_stream sessiomuuttujat.
		}
		if ($this->session->get("g-show_tv") == 1) $nayta = "true";
		else $nayta = "false";

		$this->view->header->js .= "\n<script type=\"text/javascript\">var show = $nayta</script>";
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "js/pages/frontends.js\"></script>";

		if ($this->session->get("g-show_tv"))
			$show = $this->session->get("g-show_tv"); //pistetään valikoihin oikeet arvot
		else
			$show = false;
		if ($this->session->get("g-show_stream"))
			$striim = $this->session->get("g-show_stream");
		else
			$striim = false;

		$instances = $this->get_instances();

		$this->view->content->text->select = Form::select("show", array("Diashow", "Streami"), $show, array("id" => "show_tv", "onchange" => "check_show(this.value);$(this).addClass(\"new\");$(\"#show_stream\").addClass(\"new\");"))
		 . Form::select("streams", $this->get_streams(), $striim, array("id" => "show_stream", "onchange" => "$(this).addClass(\"new\");"))
		 . Form::select("instances", $instances, $this->session->get('show_inst', 1), array("id" => "show_inst", "onchange" => "set_instance(this.value);"));
		// </globaali hallinta>
		$query = Jelly::query('frontends')->where('last_active', '>', DB::expr('DATE_SUB(NOW(),INTERVAL 5 MINUTE)'))->select();
		if ($query->count() > 0) {
			$result = $query;
		} else {
			$result = false;
		}

		$query4 = Jelly::query('diat')->order_by('dia_id')->select();
		$diat[0] = "twitter";
		if ($query4->count() > 0)
			foreach($query4 as $data) {
			$diat[$data->dia_id] = $this->utf8($data->tunniste);
		} else
			$diat = false;
		// ----------- ÄLÄ KOSKE! Tehottomampi ja huonommin toimiva Jellyllä. ----------------------------------------------------------------------
		// Frontendit, jotka eivät ole ilmoittaneet itsestään yli viiteen minuuttiin, asetetaan käyttämään globaalia asetusta ja public-instanssia.
		$query2 = DB::query(Database::UPDATE,
			"UPDATE frontends " . 
            "SET    use_global = 1 " . 
            "      ,show_inst = 1 " . 
            "WHERE  last_active < DATE_SUB(NOW(),INTERVAL 5 MINUTE)"
			)->execute(__db);
		// Frontendit, jotka eivät ole ilmoittaneet itsestään yli viikkoon, poistetaan automaattisesti
		$query3 = DB::query(Database::DELETE,
			"DELETE FROM frontends " . 
            "WHERE  last_active < DATE_SUB(NOW(),INTERVAL 1 WEEK)"
			)->execute(__db);
		// ------------------------------------------------------------------------------------------------------------------------------------------
		$this->view->content->text->tablebody = "";
		if ($result) {
			$streams = $this->get_streams();
			foreach($result as $data) {
				if ($data->show_tv == 1) {
					$nayta_stream = "inline";
					$nayta_dia = "none";
					$nayta_inst = "none";
				} elseif ($data->show_tv == 2) {
					$nayta_stream = "none";
					$nayta_dia = "inline";
					$nayta_inst = "none";
				} else {
					$nayta_stream = "none";
					$nayta_dia = "none";
					$nayta_inst = "inline";
				}
				$this->view->content->text->tablebody .= "<tr class=\"" . $data->f_id . "\"><td>" . Form::input("ident-" . $data->f_id, $data->tunniste, array("size" => "20", "onkeypress" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td>" . Form::select("show_tv-" . $data->f_id, array("Diashow", "Streami", "Yksittäinen dia"), $data->show_tv, array("id" => $data->f_id . "-tv", "onchange" => "check(this.value,\"" . $data->f_id . "\");$(this).addClass(\"new\");$(\"#" . $data->f_id . "-stream\").addClass(\"new\");$(\"#" . $data->f_id . "-dia\").addClass(\"new\");$(\"#" . $data->f_id . "-inst\").addClass(\"new\");")) . Form::select("show_stream-" . $data->f_id, $streams, $data->show_stream, array("id" => $data->f_id . "-stream", "onchange" => "$(this).addClass(\"new\");", "style" => "display:$nayta_stream;")) . Form::select("dia-" . $data->f_id, $diat, $data->dia, array("id" => $data->f_id . "-dia", "onchange" => "$(this).addClass(\"new\");", "style" => "display:$nayta_dia;")) . Form::select("show_inst-" . $data->f_id, $instances, $data->show_inst, array("id" => $data->f_id . "-inst", "onchange" => "$(this).addClass(\"new\");", "style" => "display:$nayta_inst;")) . "</td><td>" . Form::checkbox("use_global-" . $data->f_id, 1, (boolean)$data->use_global, array("onchange" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; background-color: transparent;\">&nbsp;</td></tr>";
			}
		} else {
			$this->view->content->text->tablebody .= "<p>Yhtään aktiivista frontendiä ei löytynyt.</p>";
		}
	}

	private function video()
	{
		$this->view->header->css .= HTML::style('css/jquery.fileupload-ui.css');
		$this->view->header->js .= '<script src="http://blueimp.github.com/JavaScript-Templates/tmpl.min.js"></script>';
		$this->view->header->js .= '<script src="http://blueimp.github.com/JavaScript-Load-Image/load-image.min.js"></script>';
		$this->view->header->js .= '<script src="http://blueimp.github.com/JavaScript-Canvas-to-Blob/canvas-to-blob.min.js"></script>';
		$this->view->header->js .= '<script src="http://blueimp.github.com/jQuery-Image-Gallery/js/jquery.image-gallery.min.js"></script>';
		$this->view->header->js .= '<script type="text/javascript" src="' . URL::site('/') . 'jquery/jquery.iframe-transport.js"></script>';
		$this->view->header->js .= '<script type="text/javascript" src="' . URL::site('/') . 'jquery/jquery.fileupload.js"></script>';
		$this->view->header->js .= '<script type="text/javascript" src="' . URL::site('/') . 'jquery/jquery.fileupload-ip.js"></script>';
		$this->view->header->js .= '<script type="text/javascript" src="' . URL::site('/') . 'jquery/jquery.fileupload-ui.js"></script>';
		$this->view->header->js .= '<script type="text/javascript" src="' . URL::site('/') . 'jquery/jquery.fileupload-jui.js"></script>';
		$this->view->header->js .= '<script type="text/javascript" src="' . URL::site('/') . 'jquery/locale.js"></script>';
		$this->view->header->js .= '<script type="text/javascript" src="' . URL::site('/') . 'jquery/main.js"></script>';

		$this->view->content->text = new view('pages/video');
	}

	private function logi()
	{
		$this->view->content->text = new view('pages/loki');
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "js/pages/logi.js\"></script>";

		$rows = DB::query(Database::SELECT,
			'SELECT   id ' . 
            '        ,tag ' . 
            '        ,comment ' . 
            '        ,adder ' . 
            '        ,stamp ' . 
            '        ,ack ' . 
            '        ,ack_stamp ' . 
            'FROM    ' . __tableprefix . 'logi ' . 
            'WHERE    hidden = 0 ' . 
            'ORDER BY stamp DESC '
			)->as_object()->execute(__db);

		$types = array("tiedote" => "Tiedote", "ongelma" => "Ongelma", "kysely" => "Kysely", "löytötavara" => "Löytötavara", "muu" => "Muu");
		$this->view->content->text->select = Form::select('tag', $types, 2, array("id" => "tag"));

		if($this->session->get('nick',false) != false){
			$this->view->content->text->user = $this->session->get('nick');	
		}else{
			$this->view->content->text->user = $this->session->get('user');
		}
		
		$this->view->content->text->tablebody = "";

		if ($rows->count() > 0) {
			$this->view->footer->dialogs = new view('dialogs/loki');
			$this->view->footer->dialogs->types = Form::select('edittypes', $types, 2, array("id" => "edittypes"));
			$tablebody = "";
			foreach($rows as $row) {
				if (!empty($row->ack)) {
					$tablebody .= "<tr id=\"" . $row->id . "\" tag=\"" . $row->tag . "\" class=\"type-" . $row->tag . " type-" . $row->tag . "-kuitattu\" title=\"Kuittaaja: " . $row->ack . " (" . date("d.m. H:i", strtotime($row->ack_stamp)) . ")\"><td row=\"" . $row->id . "\">" . date("d.m. H:i", strtotime($row->stamp)) . "</td><td row=\"" . $row->id . "\">" . $types[$row->tag] . "</td><td row=\"" . $row->id . "\">" . $row->comment . "</td><td row=\"" . $row->id . "\">" . $row->adder . "</td></tr>";
				} else {
					$tablebody .= "<tr id=\"" . $row->id . "\" tag=\"" . $row->tag . "\" class=\"type-" . $row->tag . "\"><td row=\"" . $row->id . "\">" . date("d.m. H:i", strtotime($row->stamp)) . "</td><td row=\"" . $row->id . "\">" . $types[$row->tag] . "</td><td row=\"" . $row->id . "\">" . $row->comment . "</td><td row=\"" . $row->id . "\">" . $row->adder . "</td></tr>";
				}
			}
			$this->view->content->text->tablebody = $tablebody;
		}
	}

	private function dashboard()
	{
		$this->view->header->css .= HTML::style('css/dashboardui.css');
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "jquery/jquery.dashboard.js\"></script>";
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "js/pages/dashboard.js\"></script>";
		// $this->view->header->js .= "\n<script type=\"text/javascript\" src=\"".URL::site('/')."js/widget.js\"></script>";
		$this->view->content->text = new view('pages/dashboard');
	}

	private function users()
	{
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "js/pages/users.js\"></script>";

		$levels = array(1 => "Peruskäyttö", 2 => "Laaja käyttö", 3 => "BOFH", 4 => "ÜberBOFH");
		$this->view->footer->dialogs = new view('dialogs/user');
		$this->view->footer->dialogs->newuserselect = Form::select("leveli", $levels, 1, array("id" => "u_level"));
		$this->view->footer->dialogs->levelselect = Form::select("level", $levels, 1, array("id" => "level"));

		$users = Jelly::query('user')->select();

		$this->view->content->text = new view('pages/user');
		$this->view->content->text->tablebody = "";

		function gethost ($ip)
		{
			$host = `host $ip`;
			$host = explode(' ', $host);
			$host = end($host);
			$host = substr($host, 0, -2);
			$chk = explode("(", $host);
			if (isset($chk[1])) return $ip;
			else return $host;
		}

		foreach($users as $user) {
			if (strcmp("0000-00-00 00:00:00", $user->last_login) === 0)
				$last = "Ei ole vielä kirjautunut.";
			else
				$last = date("d.m.Y H:i", strtotime($user->last_login));
			$host = gethost($user->ip);
			if (strcmp($host, $user->ip) != 0 and $host !== false)
				$show_host = "(" . $host . ")";
			else
				$show_host = "";
			$this->view->content->text->tablebody .= "<tr id=\"" . $user->u_id . "\" usr=\"" . $user->kayttis . "\"><td row=\"" . $user->u_id . "\">" . $user->u_id . "</td><td row=\"" . $user->u_id . "\">" . $user->kayttis . "</td><td row=\"" . $user->u_id . "\">" . $levels[$user->level] . "</td><td row=\"" . $user->u_id . "\">" . $last . "</td><td row=\"" . $user->u_id . "\">" . $user->ip . "<br/>" . $show_host . "</td></tr>";
		}
	}

	private function ohjelma()
	{
		$this->view->header->css .= HTML::style('css/jPicker-1.1.6.min.css');
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "jquery/jpicker-1.1.6.min.js\"></script>";
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "jquery/jquery.event.drag-2.2.js\"></script>";
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "jquery/jquery.event.drag.live-2.2.js\"></script>";
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "jquery/jquery.event.drop-2.2.js\"></script>";
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "jquery/jquery.event.drop.live-2.2.js\"></script>";
		$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "js/pages/ohjelma.js\"></script>";
		$this->view->content->text = new view('pages/ohjelma');
		$this->view->content->text->level = $this->session->get('level', 0);

		$katequery = Jelly::query('kategoriat')->select();
		$kategoriat = array();
		foreach($katequery as $row) {
			if ($row->loaded()) {
				$kategoriat[$row->tunniste]["nimi"] = $row->nimi;
				$kategoriat[$row->tunniste]["vari"] = $row->vari;
				$kategoriat[$row->tunniste]["fontti"] = $row->fontti;
			}
		}

		$kats = array();
		foreach($katequery as $row){
            $kats[$row->tunniste] = $row->nimi;
        }

		$slotquery = Jelly::query('slotit')->select();
		$slotit = array();
		foreach($slotquery as $row) {
			if ($row->loaded())
				$slotit[$row->pituus] = $row->selite;
		}
		$slotit["muu"] = "Muu:";
		$this->view->footer->dialogs = new view('dialogs/ohjelma');
		$this->view->footer->dialogs->kategoria = Form::select('kategoria', $kats);
		$this->view->footer->dialogs->pituus = Form::select('pituus', $slotit, "45", array("id" => "pituusselect"));
		$this->view->footer->dialogs->ekategoria = Form::select('kategoria', $kats,0,array("id"=>"e-kategoria"));
		$this->view->footer->dialogs->epituus = Form::select('pituus', $slotit, "45", array("id" => "e-pituusselect"));
		// <ohjelmakartan timetable>
		$tc = Jelly::query('tapahtuma')->limit(1)->select(); //tapahtumaconfig
		if ($tc->loaded())
			if (strtotime($tc->loppuaika) > strtotime($tc->alkuaika))
				$span = Date::span(strtotime($tc->alkuaika), strtotime($tc->loppuaika), "hours");
			else
				$span = 0;
			else
				$span = 0;
			$timetable = "";
			$slots = 4;
			if ($tc->loaded())
				$start = strtotime($tc->alkuaika);
			else
				$start = 0;
			for($i = 0;$i < $span;$i++) {
				$hour = $start + ($i * 3600);
				$timetable .= "<tr class=\"hourstart-$i\" id=\"" . $i . "0\" hour=\"$hour\" slot=\"0\"><td style=\"text-align:right;\">" . date("d.m. H", $hour) . ":00</td></tr>";
				for($y = 1;$y < $slots;$y++) {
					$s = $hour + ($y * 15 * 60);
					$timetable .= "<tr id=\"$i$y\" hour=\"$s\" slot=\"$y\"><td style=\"text-align:right;\">" . date("H:", $hour) . $y * 15 . "</td></tr>";
				}
			}
			// </ohjelmakartan timetable>
			// <slottia vailla olevat ohjelmanumerot>
			$ohjelmat = "";
			$data = Jelly::query('ohjelma')->where('sali', 'like', '0')->or_where('sali', 'like', '')->select();
			foreach($data as $row) {
				if ($row->loaded()) { // 15min==15px;
					$le = $row->kesto - 12 + ($row->kesto / 45 * 3);
					$li = $le + 10;
					$lyhyt_otsikko = substr(htmlspecialchars($row->otsikko), 0, 25);
					trim($lyhyt_otsikko);
					if (strlen(htmlspecialchars($row->otsikko)) > 25)
						$lyhyt_otsikko .= "...";
					$checked = "";
					if ($row->hidden == 1)
						$checked = "checked=\"checked\"";
					$ohjelmat .= "<li style=\"height:" . $li . "px;\"><div class=\"ui-widget-content drag ui-corner-all " . $row->kategoria . "\" oid=\"" . $row->id . "\" style=\"width:180px;height:" . $le . "px;z-index:3;list-style-type: none;padding:5px;position:absolute;\" title=\"" . htmlspecialchars($row->otsikko) . "\nPitäjä: " . $row->pitaja . "\nKategoria: " . $row->kategoria . "\nKesto: " . $row->kesto . "min\nKuvaus: " . $row->kuvaus . " \">" . $lyhyt_otsikko . "<input name=\"piilotettu\" class=\"hid toggle\" type=\"checkbox\" id=\"hidden-" . $row->id . "\" value=\"1\" " . $checked . " /><label class=\"hid\" for=\"hidden-" . $row->id . "\">Piilotettu?</label></div></li>";
				}
			}
			// </slottia vailla olevat ohjelmanumerot>

			$ohjelmaedit = "";
			$odata = Jelly::query('ohjelma')->select();
			foreach($odata as $row) {
				if ($row->loaded()) {
					$checked = "";
					if ($row->hidden == 1)
						$checked = "checked=\"checked\"";
					$ohjelmaedit .= "<div style=\"height: 80px; width:190px; padding:5px;float:left;\"><div class=\"ui-widget-content ui-corner-all " . $row->kategoria . "\" oid=\"" . $row->id . "\" style=\"width:180px;height: 70px; padding:5px;position:absolute;\" title=\"" . htmlspecialchars($row->otsikko) . "\nPitäjä: " . $row->pitaja . "\nKategoria: " . $row->kategoria . "\nKesto: " . $row->kesto . "min\nKuvaus: " . $row->kuvaus . " \">" . $row->otsikko . "<input name=\"piilotettu\" class=\"hid toggle\" type=\"checkbox\" id=\"hidden-" . $row->id . "\" value=\"1\" " . $checked . " /><label class=\"hid\" for=\"hidden-" . $row->id . "\">Piilotettu?</label><span class=\"ui-icon ui-icon-circle-close\" style=\"position:absolute; top:0px; right:0px;\" onclick=\"del(".$row->id.")\"></span></div></div>";
				}
			}
			$ohjelmaedit .= "<div style:\"clear:both;\"></div>";

			$saliquery = Jelly::query('salit')->select();
			$salit = "";
			foreach($saliquery as $row) {
				if ($row->loaded())
					$salit .= Form::checkbox($row->tunniste, $row->tunniste, false, array("id" => $row->tunniste . "-c")) . Form::label($row->tunniste . "-c", $row->nimi, array("id" => $row->tunniste));
			}

			$this->view->content->text->salit = $salit;
			$this->view->content->text->ohjelmat = $ohjelmat;
			$this->view->content->text->timetable = $timetable;

			$this->view->content->text->ohjelmamuoks = $ohjelmaedit;

			$this->view->content->text->alkupaiva = date('d.m.Y', strtotime($tc->alkuaika));
			$this->view->content->text->alkuselect = Form::select('alku-klo-h', Date::hours(1, true), date('H', strtotime($tc->alkuaika)), array("id" => "alku-klo-h")) . " " . Form::select('alku-klo-m', Date::minutes(1), date('i', strtotime($tc->alkuaika)), array("id" => "alku-klo-m"));
			$this->view->content->text->loppupaiva = date('d.m.Y', strtotime($tc->loppuaika));
			$this->view->content->text->loppuselect = Form::select('loppu-klo-h', Date::hours(1, true), date('H', strtotime($tc->loppuaika)), array("id" => "loppu-klo-h")) . " " . Form::select('loppu-klo-m', Date::minutes(1), date('i', strtotime($tc->loppuaika)), array("id" => "loppu-klo-m"));
			$this->view->content->text->kategoriat_acc = "";
			foreach($kategoriat as $tunniste => $data) {
				$this->view->content->text->kategoriat_acc .= "<tr style=\"background-color:'" . $data["vari"] . "'; color:'" . $data["fontti"] . "';\"><td>" . $tunniste . "</td><td>" . $data["nimi"] . "</td></tr>";
			}
			$this->view->content->text->timeslots_acc = "";
			foreach($slotit as $pituus => $selite) {
				$this->view->content->text->timeslots_acc .= "<tr><td>" . $pituus . "</td><td>" . $selite . "</td></tr>";
			}
			$this->view->content->text->salit_acc = "";
			foreach($saliquery as $row) {
				$this->view->content->text->salit_acc .= "<tr><td>" . $row->tunniste . "</td><td>" . $row->nimi . "</td></tr>";
			}
		}

		private function tuotanto()
		{
			$this->view->content->text = new view('pages/tuotanto');
			$this->view->footer->dialogs = new view('dialogs/tuotanto');
			$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "js/pages/tuotanto.js\"></script>";
			$priority = array('0 - Triviaali', '1 - Matala', '2 - Normaali', '3 - Korkea', '4 - Ehdoton');
			$category = array('ohjelma' => 'Ohjelma', 'viestinta' => 'Viestintä', 'tyovoima' => 'Työvoima', 'info' => 'Info', 'teema' => 'Teema', 'tilat' => 'Tilat', 'logistiikka' => 'Logistiikka', 'turva' => 'Turvallisuus', 'tekniikka' => 'Tekniikka', 'talous' => 'Talous', 'kunnia' => 'Kunniavieras', 'majoitus' => 'Majoitus', 'lipunmyynti' => 'Lipunmyynti', 'muu' => 'Muu');
			$type = array('public' => 'Julkinen', 'internal' => 'Sisäinen', 'ydin' => 'Ydinryhmä', 'note' => 'Huomio/kommentti');
			$this->view->content->text->priority = Form::select('priority', $priority, 2, array("style" => "width:100%;"));
			$this->view->footer->dialogs->priority = Form::select('priority', $priority, 2, array("style" => "width:100%;", "id" => "prioriteetti"));
			$this->view->content->text->category = Form::select('category[]', $category, 'logistiikka', array("style" => "width:100%;", "multiple"));
			$this->view->footer->dialogs->category = Form::select('category[]', $category, 'logistiikka', array("style" => "width:100%;", "multiple", "id" => "kategoria"));
			$this->view->content->text->type = Form::select('type', $type, 'internal', array("style" => "width:100%;"));
			$this->view->footer->dialogs->type = Form::select('type', $type, 'internal', array("style" => "width:100%;", "id" => "tyyppi"));
			$this->view->content->text->hours = Form::select('hours', Date::hours(1, true));
			$this->view->footer->dialogs->hours = Form::select('hours', Date::hours(1, true), 0, array("id" => "tunnit"));
			$this->view->content->text->mins = Form::select('mins', Date::minutes(1));
			$this->view->footer->dialogs->mins = Form::select('mins', Date::minutes(1), 0, array("id" => "minuutit"));

			$data = Jelly::query('tuotanto')->order_by('start', 'ASC')->select();
			$tablebody = "";

			foreach($data as $row) {
				if ($row->loaded()) {
					$categories = explode(',', $row->category);
					$cats = array();
					foreach($categories as $cate)
					$cats[] = $category[$cate];
					$show_cats = implode(", ", $cats);
					$tablebody .= "    <tr id=\"" . $row->id . "\"><td type=\"prioriteetti\" class=\"prio-".$row->priority."\">" . $priority[$row->priority] . "</td><td type=\"kategoria\">" . $show_cats . "</td><td type=\"tyyppi\">" . $type[$row->type] . "</td><td type=\"alkuaika\">" . date('d.m.Y H:i', strtotime($row->start)) . "</td><td type=\"pituus\">" . $row->length . " min</td><td type=\"eventti\">" . $row->event . "</td><td type=\"lisat\">" . nl2br($row->notes) . "</td><td type=\"vastuullinen\">" . $row->vastuu . "</td><td type=\"tekijat\">" . $row->duunarit . "</td></tr>\n";
				}
			}
			$this->view->content->text->tablebody = $tablebody;
		}

		private function asetukset()
		{
			$this->view->content->text = new view("pages/asetukset");
			$this->view->footer->dialogs = new view('dialogs/asetukset');
			$this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "js/pages/asetukset.js\"></script>";

			$current = DB::query(Database::SELECT, "select value from config where opt = 'tableprefix'")->execute(__db)->as_array();
			$current = $current[0]['value'];

			$data = Jelly::query('asetukset')->select();
			$dataset = array();
			foreach($data as $row) {
				if ($row->loaded()) {
					$dataset[$row->prefix] = $row->prefix . " - " . $row->tapahtuma;
				}
			}
			if (empty($dataset))
				$dataset[date("Y")] = date("Y") . " - default";
			$moardata = Jelly::query('instances')->select();
			$instanssit = "<tbody>";
			foreach($moardata as $row) {
				if ($row->loaded()) {
					$instanssit .= "<tr><td>" . $this->utf8($row->nimi) . "</td><td>" . $this->utf8($row->selite) . "</td></tr>";
				}
			}
			$instanssit .= "</tbody>";

			$this->view->content->text->dataset = Form::select('dataset', $dataset, $current, array("onchange" => "change_active();", "id" => "datasetti"));
			$this->view->content->text->instanssit = $instanssit;
		}

		private function tekstarit()
		{
            $this->view->content->text = new view("pages/tekstari");
            $this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "js/pages/tekstari.js\"></script>";
            $this->view->header->js .= "\n<script type=\"text/javascript\" src=\"" . URL::site('/') . "js/fileuploader.js\"></script>";
            $inbox = Jelly::query('smsinbox')->order_by('stamp','DESC')->limit(15)->select();
            $inbox_data = "<table class=\"stats\"><thead><tr><td>Lähettäjä</td><td>Viesti</td><td>Vastaanotettu</td></tr></thead><tbody>";
            foreach($inbox as $row){
            	if ($row->loaded()) {
            		$inbox_data .= "<tr><td>+" . $this->utf8($row->from) . "</td><td>" . $this->utf8($row->text) . "</td><td>" . date('d.m.Y H:i', strtotime($row->stamp." UTC")) . "</td></tr>";
            	}
            }
            $inbox_data .= "</table>";
            $outbox = Jelly::query('smsoutbox')->order_by('stamp','DESC')->limit(15)->select();
            $outbox_data = "<table class=\"stats\"><thead><tr><td>Vastaanottaja</td><td>Viesti</td><td>Lähetetty</td><td>Status</td></tr></thead><tbody>";
            foreach($outbox as $row){
            	if ($row->loaded()) {
            		$outbox_data .= "<tr title=\"Lähettäjä: ".$this->utf8($row->sender)."\"><td>+" . $this->utf8($row->to) . "</td><td>" . $this->utf8($row->text) . "</td><td>" . date('d.m.Y H:i', strtotime($row->stamp)) . "</td><td>" . $this->utf8($row->status) . "</td></tr>";
            	}
            }
            $outbox_data .= "</table>";
            $this->view->content->text->inbox = $inbox_data;
            $this->view->content->text->valitystiedot = $outbox_data;
        }


		public function action_logout()
		{
			$this->session->destroy();
			$this->view->content->text = "<p>Olet kirjautunut ulos.</p><p>" . HTML::file_anchor('admin', 'Kirjaudu takaisin sisään') . ".</p>";
			$this->view->content->links = "";
			$this->response->body($this->view->render());
		}

		private function get_set()
		{
			// kaiva kannasta tiedot tämänhetkisestä tv:ssä pyörivästä setistä, ja tunge sessiomuuttujiin.
			$query = DB::query(Database::SELECT,
				"SELECT  opt " . 
                "       ,value " . 
                "FROM    config"
				)->execute(__db);
			foreach($query as $row) {
				$this->session->set($row['opt'], $row['value']);
				$this->session->set("g-" . $row['opt'], $row['value']);
			}
		}

		public static function get_instances()
		{
			$instances = Jelly::query('instances')->select();
			if ($instances->count() === 0) {
				Jelly::factory('instances')->set(array('rul_id' => 1, 'nimi' => 'Public', 'selite' => 'Julkinen'))->save();
				$instances = Jelly::query('instances')->select();
			}
			$instance_list = array();
			foreach($instances as $row) {
				if ($row->loaded()) {
					$instance_list[$row->inst_id] = Controller_Admin::utf8($row->nimi);
				}
			}
			return $instance_list;
		}

		/**
		 * UTF-8-muuntaja on-demand
		 *
		 * @param string $str Muunnettava teksti
		 * @return string Teksti varmasti UTF-8:na
		 */
		public static function utf8($str)
		{
			if (Controller_Admin::utf8_compliant($str) == 1) {
				$return = $str;
			} else {
				$return = utf8_encode($str);
			}
			return $return;
		}

		/**
		 * utf8:n kaveri. Tunnistaa, onko teksti utf-8:ia vai jotain muuta
		 *
		 * @param string $str Tunnistettava teksti
		 * @return True /null, true, jos utf-8, kuolee hiljaa jollei.
		 */
		public static function utf8_compliant($str)
		{
			if (strlen($str) == 0) {
				return true;
			}
			return (preg_match('/^.{1}/us', $str, $ar) == 1);
		}
		// Helppoa tinymce-implementointia varten.
		public function tinymce($css)
		{
			$data = "
                <!-- TinyMCE -->
                <script type=\"text/javascript\" src=\"" . URL::site('/') . "tiny_mce/4.0.2/jquery.tinymce.js\"></script>
                <script type=\"text/javascript\">var stylesheetti = '$css'; var setted_up = false;</script>
                <script type=\"text/javascript\" src=\"" . URL::site('/') . "js/tinymce_setup.js\"></script>
                <!-- /TinyMCE -->
                ";
			return $data;
		}
	}

	?>