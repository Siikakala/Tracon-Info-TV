<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Admin-controlleri hallintapuolelle..
 *
 * @author Miika Ojamo <miika@vkoski.net>
 */
class Controller_Ajax extends Controller {
	public function before()
	{
		$this->db = Database::instance(__db);
		$this->session = Session::instance();
		if (!defined("__tableprefix")) {
			$tb = DB::query(Database::SELECT, "SELECT value FROM config WHERE opt = 'tableprefix'")->execute(__db)->get('value', date('Y'));
			if ($tb == 0) {
				$tb = "dev";
			}
			define("__tableprefix", $tb);
		}
	}

	/**
	 * Warning! Casting Magics ahead!
	 *
	 * Tässä metodissa tapahtuu siis 90% koko järjestelmän toiminnallisuudesta.
	 */
	public function action_ajax()
	{
		$param1 = $this->request->param('param1', null);
		$param2 = $this->request->param('param2', null);
		if (Kohana::$profiling === true) {
			// Start a new benchmark
			$benchmark = Profiler::start('ajax', __FUNCTION__);
		}
		$return = "";
		$this->session->set('results', array());
		$kutsut = array("infotv-readonly" => array("kutsut" =>
				array("scroller_load", "rulla_row", "rulla_load", "dia_load", "stream_load", "frontend_load", "lastupdate", "tuotanto_dash"),
				"level" => 1
				),
			"infotv-common" => array("kutsut" =>
				array("scroller_save", "scroller_delete", "scroller_delete", "rulla_save", "rulla_delete", "dia_save", "dia_delete", "upload", "tv", "ohjelma", "instance"),
				"level" => 2
				),
			"infotv-adv" => array("kutsut" =>
				array("stream_delete", "stream_save", "frontend_save"),
				"level" => 3
				),
			"info-common" => array("kutsut" =>
				array(
                    "kategoria_add", "slot_add", "sali_add",
                    "ohjelma_add", "ohjelma_save", "ohjelma_load", "ohjelma_dash",
                    "tuotanto_save", "tuotanto_refresh", "tuotanto_populate", "tuotanto_edit",
                    "ohjelma_hide","ohjelma_loadedit","ohjelma_edit","ohjelma_del",
                    "tekstari_send", "tekstari_file"
                ),
				"level" => 2
				),
			"info-adv" => array("kutsut" =>
				array("tapahtuma_save", "tuotanto_del"),
				"level" => 3
				),
			"logi-readonly" => array("kutsut" =>
				array("todo_refresh", "todo_search"),
				"level" => 1
				),
			"logi-common" => array("kutsut" =>
				array("todo_save", "todo_ack", "todo_unack", "todo_del"),
				"level" => 2
				),
			"logi-adv" => array("kutsut" =>
				array("populate_logi"),
				"level" => 3
				),
			"bofh" => array("kutsut" =>
				array("user_del", "user_level", "user_pass", "user_new", "loads", "dataset_change", "dataset_add", "instance_add"),
				"level" => 3
				),
			"public" => array("kutsut" =>
				array("login", "check"),
				"level" => 0
				)
			);

		function search($array, $key, $search)
		{
			$data = array_search($search, $array["kutsut"]);
			if ($data === false) {
			} else {
				array_push($_SESSION['results'], $key);
			}
		}
		array_walk($kutsut, "search", $param1);
		$resultsi = $this->session->get('results', null);
		if (empty($resultsi)) {
			$kutsu_ok = false;
			throw new Kohana_Exception("Kutsua :param1 ei löydy", array(":param1" => $param1), E_WARNING);
		} else {
			$kutsu_ok = true;
		}
		if (count($resultsi) > 1)
			throw new Kohana_Exception("Kutsu määritelty useampaan kertaan. :param1 määritelty ryhmissä :kutsut.", array(":param1" => $param1, ":kutsut" => implode(", ", $resultsi)), E_NOTICE);

		if ($kutsu_ok !== true) {
			$return = array("ret" => false);
		} elseif ($this->session->get('level', 0) >= $kutsut[$resultsi[0]]['level']) { // varmistetaan että on kirjauduttu sisään ja oikeudet muokata asioita.
			switch ($param1) {
				case "login":
					$auth = new Model_Authi();
					$login = $auth->auth($_POST['user'], $_POST['pass'], $_SERVER['REMOTE_ADDR']);
					if ($login !== false) {
						$this->session->set('logged_in', true); //true/false
						$this->session->set('level', $login); //>= 1
						$this->session->set('user', $_POST['user']); //käyttäjätunnus.
						$this->session->set('instance', 1);
						$ret = true;
					} else {
						$ret = "Väärä käyttäjätunnus tai salasana!";
					}
					$return = array("ret" => $ret);
					break;
				case "scroller_save":
					$model = new Model_Pages_Scroller();
					$return = $model->ajax("save", $param2);
					break;
				case "scroller_load":
					$model = new Model_Pages_Scroller();
					$return = $model->ajax("load", $param2);
					break;
				case "scroller_delete":
					$model = new Model_Pages_Scroller();
					$return = $model->ajax("delete", $param2);
					break;
				case "rulla_row":// rivin generointi vaatii sen verran että on helpompaa hakee data ajaxilla.
					$id = $param2;
					$text = "";
					$query = Jelly::query('rulla')->order_by('pos', 'DESC')->limit(1)->select();
					if ($query->count() > 0)
						$result = $query->as_array(array(null, 'pos'));
					else
						$result = false;

					$query2 = Jelly::query('diat')->order_by('dia_id')->select();
					if ($query2->count() > 0)
						$result2 = $query2;
					else
						$result2 = false;

					$vaihtehdot = array();
					$vaihtoehdot[0] = "twitter";
					if ($result2) foreach($result2 as $row) {
						$vaihtoehdot[$row->dia_id] = $this->utf8($row->tunniste);
					} else
						$vaihtoehdot = false;

					if (!$result) {
						$pos = $id - 499;
					} else {
						$pos = $id - 499 + $result["pos"];
					}
					$text = "<tr class=\"new\" new=\"$id\"><td>" . Form::input('pos-' . $id, $pos, array("size" => "1")) . "</td><td>" . Form::select('text-' . $id, $vaihtoehdot, 1) . "</td><td>" . Form::select('time-' . $id, Date::seconds(1, 1, 121), 10) . "</td><td>" . Form::checkbox('hidden-' . $id, 1, false) . "</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del\" >X</a></td></tr>";
					$return = array("ret" => $text);
					break;
				case "rulla_save":
					$post = $_POST;
					$data = array();
					$err = " ";
					foreach($post as $key => $value) {
						$parts = explode("-", $key);
						$rivi = $parts[1];
						$data[$rivi][$parts[0]] = $value;
					}
					foreach($data as $row => $datat) {
						if (empty($datat["pos"])) {
							$err .= "Jokin dian positio jäi täyttämättä. Kyseisen dian tietoja <strong>EI</strong> ole tallennettu.";
						} else {
							if (!isset($datat["hidden"]))
								$datat["hidden"] = false;
							if ($row >= 0 && $row < 500) {
								if ($datat["text"] == 0)
									$type = 2;
								else
									$type = 1;
								$d = Jelly::query('rulla', $row)->select();
								$d->pos = $datat["pos"];
								$d->selector = $datat["text"];
								$d->time = $datat["time"];
								$d->hidden = $datat["hidden"];
								$d->instance = $this->session->get('instance', 1);
								$d->type = $type;
								$d->save();
							} elseif ($row >= 500) {
								if ($datat["text"] == 0)
									$datat["type"] = 2;
								else
									$datat["type"] = 1;
								$datat["selector"] = $datat["text"];
								$datat["instance"] = $this->session->get('instance', 1);
								Jelly::factory('rulla')->set($datat)->save();
							}
						}
					}
					$return = array("ret" => "Diashow päivitetty.$err Odota hetki, päivitetään listaus...");
					break;
				case "rulla_load":
					$query = Jelly::query('rulla')->where('instance', '=', $this->session->get('instance', 1))->order_by('pos')->select();
					if ($query->count() > 0)
						$result = $query;
					else
						$result = false;

					$query2 = Jelly::query('diat')->order_by('dia_id')->select();
					if ($query2->count() > 0)
						$result2 = $query2;
					else
						$result2 = false;

					$vaihtehdot = array();
					$vaihtoehdot[0] = "twitter";
					if ($result2) foreach($result2 as $row) {
						$vaihtoehdot[$row->dia_id] = $this->utf8($row->tunniste);
					} else
						$vaihtoehdot = false;
					$text = Form::open(null, array("onsubmit" => "return false;", "id" => "form")) . "<table id=\"rulla\" class=\"stats\" style=\"border-right:0px; border-top:0px; border-bottom:0px;\"><thead><tr><th class=\"ui-state-default\">Kohta</th><th class=\"ui-state-default\">Dia</th><th class=\"ui-state-default\">Aika (~s)</th><th class=\"ui-state-default\">Piilotettu?</th></tr></thead><tbody>";

					if ($result) foreach($result as $row) {
						if ($row->type == 2)// jos twitter.
							$selector = 0; //joka on aina ensimmäinen vaihtoehdoista.
						else
							$selector = $row->selector;
						$text .= "<tr class=\"" . $row->rul_id . "\"><td>" . Form::input('pos-' . $row->rul_id, $row->pos, array("size" => "1", "onkeypress" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td>" . Form::select('text-' . $row->rul_id, $vaihtoehdot, $selector, array("onchange" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td>" . Form::select('time-' . $row->rul_id, Date::seconds(1, 1, 121), $row->time, array("onchange" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td>" . Form::checkbox('hidden-' . $row->rul_id, 1, (boolean)$row->hidden, array("onchange" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del ignore\" onclick=\"dele(" . $row->rul_id . ")\" >X</a></td></tr>";
					}
					$text .= "</tbody></table>" . Form::close();
					$return = array("data" => $text);
					break;
				case "rulla_delete":
					if (!$param2) {
						$ret = false;
					} else {
						Jelly::query('rulla', $param2)->where('instance', '=', $this->session->get('instance', 1))->select()->delete();
						$ret = true;
					}
					$return = array("ret" => $ret);
					break;
				case "tv":// globaali hallinta.
					if ($_POST['stream'] == "null") {
						$return = array("ret" => "Streamia ei määritelty. Valitaan diashow.");
						$query = DB::query(Database::UPDATE,
							"UPDATE   config " . "SET      value = 0 " . "WHERE    opt = 'show_tv'"
							)->execute(__db);
					} else {
						$this->session->set("g-show_tv", $_POST['nayta']);
						$this->session->set("g-show_stream", $_POST['stream']);
						$query = DB::query(Database::UPDATE,
							"UPDATE   config " . "SET      value = :value " . "WHERE    opt = 'show_tv'"
							);
						$query->param(":value", $_POST['nayta'])->execute(__db);
						$query = DB::query(Database::UPDATE,
							"UPDATE   config " . "SET      value = :value " . "WHERE    opt = 'show_stream'"
							);
						$query->param(":value", $_POST['stream'])->execute(__db);
						$return = array("ret" => true);
					}
					break;
				case "dia_load":
					if (!$param2) {
						$ret = false;
					} else {
						$result = Jelly::query('diat', $param2)->select();
						if ($result->loaded()) {
							$ret = "<br/>" . Form::textarea("loota-" . $param2, $this->utf8($result->data), array("id" => "loota", "class" => "tinymce"));
							$tunniste = $this->utf8($result->tunniste);
						} else {
							$ret = false;
							$tunniste = false;
						}
					}
					$return = array("ret" => $ret, "tunniste" => $tunniste);
					break;
				case "dia_save":
					$post = $_POST;
					if ($post['id'] == 0) { // uus
						$tunniste = $post['ident'];
						$data = $post['cont'];
						Jelly::factory('diat')->set(array("tunniste" => $tunniste,
								"data" => $data
								))->save();
						$ret = true;
					} elseif (!empty($post['id'])) { // vanha
						$tunniste = $post['ident'];
						$data = $post['cont'];
						$row = Jelly::query('diat', $post['id'])->select();
						$row->tunniste = $tunniste;
						$row->data = $data;
						$row->save();
						$ret = true;
					} else {
						// data ei tullu perille :|
						$ret = "Tallennus epäonnistui.";
					}
					$return = array("ret" => $ret);
					break;
				case "dia_delete":
					if (!$param2) {
						$ret = false;
					} else {
						$r = Jelly::query('rulla')->where('selector', '=', $param2)->select();
						$r2 = Jelly::query('frontends')->where('dia', '=', $param2)->select();
						if ($r->count() > 0) {
							$ret = "Diaa käytetään vielä " . $r->instance . " diashowssa. Poista dia sieltä ensin.";
						} elseif ($r2->count() > 0) {
							$ret = "Diaa näytetään tällä hetkellä frontendissä " . $r2->tunniste . ". Poista dia sieltä ensin.";
						} else {
							Jelly::query('diat', $param2)->select()->delete();
							$ret = true;
						}
					}
					$return = array("ret" => $ret);
					break;
				case "stream_delete":
					if (!$param2) {
						$ret = false;
					} else {
						// Globaali asetus
						$q = DB::query(Database::SELECT,
							"SELECT opt " .
                            "FROM   config " .
                            "WHERE  value = :id " .
                            "       AND " .
                            "       opt = 'show_stream'" .
                            "       AND " .
                            "       (select value from config where opt = 'show_tv') = 1"
							);
						$r = $q->param(":id", $param2)->execute(__db);
						// Yksittäinen frontend, joka ei käytä globaalia
						$q2 = Jelly::query('frontends')->where('show_stream', '=', $param2)->where('use_global', '=', '0')->where('show_tv', '=', '1')->select();

						if ($r->count() > 0 || $q2->count() > 0) {
							$ret = "Streamia näytetään parhaillaan. Vaihda toiseen streamiin tai diashowhun ensin.";
						} else {
							Jelly::query('streamit', $param2)->select()->delete();
							$ret = true;
						}
					}
					$return = array("ret" => $ret);
					break;
				case "stream_save":
					$post = $_POST;
					$data = array();
					$err = " ";
					foreach($post as $key => $value) {
						$parts = explode("-", $key);
						$rivi = $parts[1];
						$data[$rivi][$parts[0]] = $value;
					}
					foreach($data as $row => $datat) {
						if (empty($datat["ident"]) && empty($datat["url"])) {
						} elseif (empty($datat["ident"]) || empty($datat["url"])) {
							$err .= "Jokin kenttä jäi täyttämättä. Kyseisen rivin tietoja <strong>EI</strong> ole tallennettu.";
						} else {
							if (empty($datat['jarkka']))
								$datat['jarkka'] = $row + 200;
							if ($row >= 0 && $row < 500) {
								$d = Jelly::query('streamit', $row)->select();
								$d->tunniste = $datat["ident"];
								$d->url = $datat["url"];
								$d->jarjestys = $datat["jarkka"];
								$d->save();
							} elseif ($row >= 500) {
								Jelly::factory('streamit')->set(array("tunniste" => $datat["ident"],
										"url" => $datat["url"],
										"jarjestys" => $datat["jarkka"]
										))->save();
							}
						}
					}
					$return = array("ret" => "Streamit päivitetty.$err Odota hetki, päivitetään listaus...");
					break;
				case "stream_load":
					$text = "";
					$query = Jelly::query('streamit')->order_by('jarjestys')->select();
					if ($query->count() > 0) {
						$result = $query;
					} else {
						$result = false;
					}

					$text .= Form::open(null, array("onsubmit" => "return false;", "id" => "form"));
					$text .= "<table id=\"streamit\" class=\"stats\" style=\"border-right:0px; border-top:0px; border-bottom:0px;\"><thead><tr><th class=\"ui-state-default\">Streamin tunniste</th><th class=\"ui-state-default\">URL</th><th class=\"ui-state-default\">Järjestysnro</th></tr></thead><tbody>";

					if ($result) foreach($result as $row) {
						$text .= "<tr class=\"" . $row->stream_id . "\"><td>" . Form::input("ident-" . $row->stream_id, $row->tunniste, array("class" => "tunniste", "size" => "15", "onkeypress" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td>" . Form::input("url-" . $row->stream_id, $row->url, array("id" => $row->stream_id, "class" => "url", "size" => "35", "onkeypress" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td>" . Form::input("jarkka-" . $row->stream_id, $row->jarjestys, array("size" => "1", "onkeypress" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del ignore\" onclick=\"dele(" . $row->stream_id . ")\">X</a></td><td style=\"border:0px; border-bottom-style: none; padding: 0px;\"><a href=\"javascript:;\" onclick=\"load(" . $row->stream_id . ");\">&nbsp;Esikatsele</a></td></tr>";
					}

					$text .= "</tbody></table>" . Form::close();
					$return = array("ret" => $text);
					break;
				case "frontend_save":
					$post = $_POST;
					$data = array();
					$err = " ";
					foreach($post as $key => $value) {
						$parts = explode("-", $key);
						$rivi = $parts[1];
						$data[$rivi][$parts[0]] = $value;
					}
					foreach($data as $row => $datat) {
						if (empty($datat["use_global"]))
							$datat["use_global"] = 0;
						if (empty($datat["ident"])) {
							$err .= "Jokin frontendin tunniste jäi täyttämättä. Kyseisen frontendin tietoja <strong>EI</strong> ole tallennettu.";
						} else {
							$d = Jelly::query('frontends', $row)->select();
							$d->tunniste = $datat["ident"];
							$d->show_tv = $datat["show_tv"];
							$d->show_stream = $datat["show_stream"];
							$d->show_inst = $datat["show_inst"];
							$d->dia = $datat["dia"];
							$d->use_global = $datat["use_global"];
							$d->save();
						}
					}
					$return = array("ret" => "Frontendit päivitetty.$err Odota hetki, päivitetään listaus...");
					break;
				case "frontend_load":
					$query = Jelly::query('frontends')->where('last_active', '>', DB::expr('DATE_SUB(NOW(),INTERVAL 5 MINUTE)'))->select();
					if ($query->count() > 0) {
						$result = $query;
					} else {
						$result = false;
					}

					$instances = $this->get_instances();

					$query4 = Jelly::query('diat')->order_by('dia_id')->select();
					$diat[0] = "twitter";
					if ($query4->count() > 0)
						foreach($query4 as $row) {
						$diat[$row->dia_id] = $this->utf8($row->tunniste);
					} else
						$diat = false;

					if ($result != false) {
						$query = Jelly::query('streamit')->order_by('jarjestys')->select();
						$ret = array();
						foreach($query as $row) {
							$ret[$row->stream_id] = $row->tunniste;
						}
						$streams = $ret;
						$text = Form::open(null, array("onsubmit" => "return false;", "id" => "form"));
						$text .= "<table id=\"frontendit\" class=\"stats\" style=\"border-right:0px; border-top:0px; border-bottom:0px;\"><thead><tr><th class=\"ui-state-default\">Frontend</th><th class=\"ui-state-default\">Näytä</th><th class=\"ui-state-default\">Käytä globaalia?</th></tr></thead><tbody>";
						foreach($result as $row) {
							if ($row->show_tv == 1) {
								$nayta_stream = "inline";
								$nayta_dia = "none";
								$nayta_inst = "none";
							} elseif ($row->show_tv == 2) {
								$nayta_stream = "none";
								$nayta_dia = "inline";
								$nayta_inst = "none";
							} else {
								$nayta_stream = "none";
								$nayta_dia = "none";
								$nayta_inst = "inline";
							}
							$text .= "<tr class=\"" . $row->f_id . "\"><td>" . Form::input("ident-" . $row->f_id, $row->tunniste, array("size" => "20", "onkeypress" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td>" . Form::select("show_tv-" . $row->f_id, array("Diashow", "Streami", "Yksittäinen dia"), $row->show_tv, array("id" => $row->f_id . "-tv", "onchange" => "check(this.value,\"" . $row->f_id . "\");$(this).addClass(\"new\");$(\"#" . $row->f_id . "-stream\").addClass(\"new\");$(\"#" . $row->f_id . "-dia\").addClass(\"new\");$(\"#" . $row->f_id . "-inst\").addClass(\"new\");")) . Form::select("show_stream-" . $row->f_id, $streams, $row->show_stream, array("id" => $row->f_id . "-stream", "onchange" => "$(this).addClass(\"new\");", "style" => "display:$nayta_stream;")) . Form::select("dia-" . $row->f_id, $diat, $row->dia, array("id" => $row->f_id . "-dia", "onchange" => "$(this).addClass(\"new\");", "style" => "display:$nayta_dia;")) . Form::select("show_inst-" . $row->f_id, $instances, $row->show_inst, array("id" => $row->f_id . "-inst", "onchange" => "$(this).addClass(\"new\");", "style" => "display:$nayta_inst;")) . "</td><td>" . Form::checkbox("use_global-" . $row->f_id, 1, (boolean)$row->use_global, array("onchange" => "$(this).parent().parent().addClass(\"new\");")) . "</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; background-color: transparent;\">&nbsp;</td></tr>";
						}
						$text .= "</tbody></table>" . Form::close();
					} else {
						$text = "<p>Yhtään aktiivista frontendiä ei löytynyt.</p>";
					}
					$return = array("ret" => $text);
					break;
				case "upload":// käsittele tiedoston uploadaukset.
					$upload_handler = Uploadi::instance();

					$this->response->headers('Pragma', 'no-cache');
					$this->response->headers('Cache-Control', 'no-store, no-cache, must-revalidate');
					$this->response->headers('Content-Disposition', 'inline; filename="files.json"');
					$this->response->headers('X-Content-Type-Options', 'nosniff');
					$this->response->headers('Access-Control-Allow-Origin', '*');
					$this->response->headers('Access-Control-Allow-Methods', 'OPTIONS, HEAD, GET, POST, PUT, DELETE');
					$this->response->headers('Access-Control-Allow-Headers', 'X-File-Name, X-File-Type, X-File-Size');

					switch ($this->request->method()) {
						case 'OPTIONS':
							break;
						case 'HEAD':
						case 'GET':
							$upload_handler->get();
							break;
						case 'POST':
							if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
								if ($this->session->get('level', 0) >= 3) $upload_handler->delete();
							} else {
								$upload_handler->post();
							}
							break;
						case 'DELETE':
							if ($this->session->get('level', 0) >= 3) $upload_handler->delete();
							break;
						default:
							header('HTTP/1.1 405 Method Not Allowed');
					}
					break;
				case "ohjelma":// prosessoi ohjelmdakarttatiedosto.
					$post = $_POST;
					// /^O::(.{6,12})::(.{0,20})::(\d+)::(\d+)::(.{0,80})::(.{0,80})::(.{0,15})$(.{0,2000})$/misU
					// ^ ei välttämättä toimi täysin. Rubularin mukaan ei matchaan multi-line kuvauksiin, parin muun testerin mukaan ei matchaa kuvaukseen.
					$ohjelma_data = array();
					$salinimet = array();
					// <siviskoodi>
					$fp = @fopen(__documentroot . "files/" . $post['file'], "r");
					if ($fp === false)
						$ret = "Tiedostoa ei pysytty avaamaan!";
					while (!feof($fp)) { // [päivä] [sali] [alkuaika] [kesto|nimi|järjestäjä|tyyppi|kuvaus]
						$buf = $this->utf8(fgets($fp));
						if (!strncasecmp("O::", $buf, 3)) {
							$flag = 0;
							$tmparr = explode("::", $buf);
							$paiva = trim($tmparr[1]);

							$alkuaika = constant("ALKUAIKA_" . $paiva);

							$salinimi = strtolower(str_replace(" ", "_", trim($tmparr[2])));
							$aika = intval(trim($tmparr[3])) - $alkuaika + 1;
							if (!isset($salinimet[$salinimi]))
								$salinimet[$salinimi] = trim($tmparr[2]);
							$curohjelma = &$ohjelma_data[trim($tmparr[1])][$salinimi][$aika];
							$curohjelma = array("kesto" => trim($tmparr[4]),
								"nimi" => str_replace("&", "&amp;", trim($tmparr[5])),
								"jarjestaja" => str_replace("&", "&amp;", trim($tmparr[6])),
								"tyyppi" => strtolower(trim($tmparr[7])),
								"kuvaus" => "",
								);
						} elseif (isset($curohjelma)) {
							if ($flag) {
								$curohjelma["kuvaus"] .= '</p><p>';
							}
							$flag = 1;
							$curohjelma["kuvaus"] .= str_replace("&", "&amp;", $buf);
						}
					}
					fclose($fp);
					// </siviskoodi>
					if (!isset($ohjelma_data["Lauantai"])) { // tää vaatinee tapahtumakohtasta puukkoa mut..
						$ret = "Tiedoston syntaksi ei ole käyttökelpoinen.";
					} else { // data = ok.
						$q1 = DB::query(Database::DELETE, // eka vanhat pois
							"TRUNCATE ohjelmadata"
							)->execute(__db);
						$error = 0;
						foreach($ohjelma_data as $paiva => $d1) {
							foreach($d1 as $sali => $d2) {
								foreach($d2 as $alkuaika => $data) {
									$query = DB::query(Database::INSERT, // ja uudet tilalle.
										"INSERT INTO ohjelmadata " .
                                        "           (paiva " .
                                        "           ,sali " .
                                        "           ,alku " .
                                        "           ,kesto " .
                                        "           ,nimi " .
                                        "           ,jarjestaja " .
                                        "           ,tyyppi " .
                                        "           ,kuvaus " .
                                        "           ,`update` " .
                                        "           ) " .
                                        "VALUES     (:paiva " .
                                        "           ,:sali " .
                                        "           ,:alku " .
                                        "           ,:kesto " .
                                        "           ,:nimi " .
                                        "           ,:jarjestaja " .
                                        "           ,:tyyppi " .
                                        "           ,:kuvaus " .
                                        "           ,NOW() " .
                                        "           )"
										);
									$query->parameters(array(":paiva" => $paiva,
											":sali" => $sali,
											":alku" => $alkuaika,
											":kesto" => $data['kesto'],
											":nimi" => $data['nimi'],
											":jarjestaja" => $data['jarjestaja'],
											":tyyppi" => $data['tyyppi'],
											":kuvaus" => $data['kuvaus']
											));
									list($insert_id, $affected_rows) = $query->execute(__db);
									if ($affected_rows == 0) {
										$error = 1;
									}
								}
							}
						}
						if ($error) {
							$ret = "Ohjelmakartan päivityksessä tapahtui virhe. Yritä uudelleen.";
						} else {
							$ret = "Ohjelmakartan päivitys onnistui!";
						}
					}
					$return = array("ret" => $ret);
					break;
				case "lastupdate":// x)
					$return = array("ret" => date("d.m.Y H:i"));
					break;
				case "todo_save":
					$post = $_POST;
					trim($post['adder']);
					trim($post['comment']);
					if (empty($post['comment'])) {
						$ret = "";
						if (empty($post['comment']) and empty($post['adder']))
							$ret = "Molemmat kentät ovat tyhjiä, täytä ne ja lisää rivi sen jälkeen uudelleen.";
						elseif (empty($post['comment']))
							$ret = "Viesti puuttuu! Kirjoita se ensin.";
						$return = array("ret" => $ret, "ok" => false);
					} else {
						if (empty($post['adder']))
							$post['adder'] = $this->session->get("user");
						Jelly::factory('logi')->set(array("tag" => $post['tag'],
								"comment" => $post['comment'],
								"adder" => $post['adder'],
								"stamp" => DB::expr("NOW()")
								))->save();
						$return = array("ret" => "Rivi lisätty onnistuneesti.", "ok" => true);
					}
					break;
				case "todo_refresh":
					$query = Jelly::query('logi')->where('hidden', '=', '0')->order_by('stamp', 'DESC')->limit(10)->select();
					$text = "<table class=\"stats\" style=\"color:black\"><tr><th class=\"ui-state-default\">Aika</th><th class=\"ui-state-default\">Tyyppi</th><th class=\"ui-state-default\">Viesti</th><th class=\"ui-state-default\">Lisääjä</th></tr>";
					$types = array("tiedote" => "Tiedote", "ongelma" => "Ongelma", "kysely" => "Kysely", "löytötavara" => "Löytötavara", "muu" => "Muu");
					foreach($query as $row) {
						if (!empty($row->ack)) {
							$text .= "<tr class=\"type-" . $row->tag . " type-" . $row->tag . "-kuitattu\"><td>" . date("d.m. H:i", strtotime($row->stamp)) . "</td><td>" . $types[$row->tag] . "</td><td>" . $row->comment . "</td><td>" . $row->adder . "</td></tr>";
						} else {
							$text .= "<tr class=\"type-" . $row->tag . "\"><td>" . date("d.m. H:i", strtotime($row->stamp)) . "</td><td>" . $types[$row->tag] . "</td><td>" . $row->comment . "</td><td>" . $row->adder . "</td></tr>";
						}
					}
					$text .= "</table>";
					$return = array("ret" => $text);
					break;
				case "todo_search":
					$param1 = $_POST['search'];
					$types = array("tiedote" => "Tiedote", "ongelma" => "Ongelma", "kysely" => "Kysely", "löytötavara" => "Löytötavara", "muu" => "Muu");
					$params = explode(" ", $param1);
					$ids = array();
					if (!empty($param1))foreach($params as $key => $param) {
						if (strtotime($param) || strtotime($param . date("Y"))) {
							if (strstr($param, ":")) {
								$param = date("H:i", strtotime($param));
							} elseif (strstr($param, ".")) {
								$param = date("Y-m-d", strtotime($param));
							} elseif (strstr($param, "today") || strstr($param, "yesterday")) {
								$param = date("Y-m-d", strtotime($param));
							}
						}
						// print $param."\n";
						// $rows = Jelly::query('logi')
						// ->select_column('id')
						// ->or_where('tag','REGEXP','.*'.$param.'.*')
						// ->or_where('comment','REGEXP','.*'.$param.'.*')
						// ->or_where('adder','REGEXP','.*'.$param.'.*')
						// ->or_where('stamp','REGEXP','.*'.$param.'.*')
						// ->order_by('stamp','DESC')
						// ->select();

						$rows = DB::query(Database::SELECT,
							'SELECT   id ' .
                            'FROM    ' . __tableprefix . 'logi ' .
                            'WHERE    tag REGEXP :param ' .
                            '     OR  comment REGEXP :param ' .
                            '     OR  adder REGEXP :param ' .
                            '     OR  stamp REGEXP :param ' .
                            'ORDER BY stamp DESC '
							)->param(':param', '.*' . $param . '.*')->execute(__db);
						$ids[] = $rows->as_array('id');
					}
					// apufunktio, joka palauttaa vain ne avaimet, jotka löytyvät *jokaisesta* arraysta.
					function custom_intersect($arrays)
					{
						$values = array_shift($arrays);
						// Käy loput hakusanat läpi kaventaen hakua koko ajan.
						if (!empty($arrays))foreach($arrays as $array) {
							$data = array_intersect_key($values, $array);
							$values = $data;
						}

						$c = count($arrays);
						if ($c >= 0 && !empty($values))
							$result = array_keys($values);
						else
							$result = array();

						return $result;
					}

					$id_pre = custom_intersect($ids);
					$id_list = implode(",", $id_pre);
					// var_dump($ids);
					// var_dump($id_pre);
					// var_dump($id_list);
					if (!empty($id_list))
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
                            '    AND  id IN(' . $id_list . ') ' .
                            'ORDER BY stamp DESC '
							)->as_object()->execute(__db);
					elseif (!empty($param1))
						$rows = Jelly::query('logi', -1)->select();
					else
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
					$text = "<table class=\"stats\"><tr><th class=\"ui-state-default\">Aika</th><th class=\"ui-state-default\">Tyyppi</th><th class=\"ui-state-default\">Viesti</th><th class=\"ui-state-default\">Lisääjä</th></tr>";

					foreach($rows as $row) {
						if (!empty($row->ack)) {
							$text .= "<tr id=\"" . $row->id . "\" tag=\"" . $row->tag . "\" class=\"type-" . $row->tag . " type-" . $row->tag . "-kuitattu\" title=\"Kuittaaja: " . $row->ack . " (" . date("d.m. H:i", strtotime($row->ack_stamp)) . ")\"><td row=\"" . $row->id . "\">" . date("d.m. H:i", strtotime($row->stamp)) . "</td><td row=\"" . $row->id . "\">" . $types[$row->tag] . "</td><td row=\"" . $row->id . "\">" . $row->comment . "</td><td row=\"" . $row->id . "\">" . $row->adder . "</td></tr>";
						} else {
							$text .= "<tr id=\"" . $row->id . "\" tag=\"" . $row->tag . "\" class=\"type-" . $row->tag . "\"><td row=\"" . $row->id . "\">" . date("d.m. H:i", strtotime($row->stamp)) . "</td><td row=\"" . $row->id . "\">" . $types[$row->tag] . "</td><td row=\"" . $row->id . "\">" . $row->comment . "</td><td row=\"" . $row->id . "\">" . $row->adder . "</td></tr>";
						}
					}
					$text .= "</table>";
					$return = array("ret" => $text);
					break;
				case "todo_ack":
					$param = $_POST['row'];
					$d = Jelly::query('logi', $param)->select();
					$d->ack = $this->session->get('user');
					$d->ack_stamp = DB::expr("NOW()");
					$d->save();
					$return = array("ret" => true);
					break;
				case "todo_unack":
					$param = $_POST['row'];
					$d = Jelly::query('logi', $param)->select();
					$d->ack = "";
					$d->ack_stamp = "0000-00-00 00:00:00";
					$d->save();
					$return = array("ret" => true);
					break;
				case "todo_del":
					$param = $_POST['row'];
					$d = Jelly::query('logi', $param)->select();
					$d->ack = $this->session->get('user');
					$d->ack_stamp = DB::expr("NOW()");
					$d->hidden = 1;
					$d->save();
					$return = array("ret" => true);
					break;
				case "populate_logi":
					$types = array("tiedote" => "Tiedote", "ongelma" => "Ongelma", "kysely" => "Kysely", "löytötavara" => "Löytötavara", "muu" => "Muu");
					$keys = array_keys($types);
					function rand_word()
					{
						return substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', 15)), 0, rand(2, 15));
					}
					$q = DB::query(Database::SELECT,
						'SELECT tag FROM ' . __tableprefix . 'logi'
						)->execute(__db);
					$riveja = $q->count();

					$query = DB::query(Database::INSERT,
						'INSERT INTO ' . __tableprefix . 'logi (tag,comment,adder) ' . 'VALUES (:tag,:comment,:adder)'
						);
					for($i = 1;$i < 600;$i++) {
						$randomi = "";
						$sanoja = rand(0, 10);
						for($y = 0;$y <= $sanoja;$y++) {
							$randomi .= " " . rand_word();
						}
						$kom = $riveja + $i;
						$query->parameters(array(":tag" => $keys[rand(0, 4)], ":comment" => "Kommentti $kom: $randomi", ":adder" => "Automagia"))->execute(__db);
					}
					break;
				case "check":
					$provider = new Model_Public();
					$return = array("ret" => true,
						"page" => $this->session->get("page", 0),
						"dia" => $provider->page(),
						"fcn" => $provider->fcn(),
						"scroller" => $provider->scroller()
						);
					break;
				case "user_del":
					$id = $_POST['row'];
					$u = Jelly::query('user', $id)->select();
					if (strcasecmp($u->kayttis, $this->session->get('user')) == 0) {
						$return = array("ret" => "Et voi poistaa itseäsi!");
					} elseif ($u->level > $this->session->get('level')) {
						$return = array("ret" => "Et voi poistaa itseäsi mahtavampaa!");
					} else {
						$u->delete();
						$return = array("ret" => true);
					}
					break;
				case "user_level":
					$id = $_POST['row'];
					$level = $_POST['level'];
					$u = Jelly::query('user', $id)->select();
					if (strcasecmp($u->kayttis, $this->session->get('user')) == 0) {
						$return = array("ret" => "Et voi muuttaa omaa tasoasi!");
					} elseif ($u->level > $this->session->get('level')) {
						$return = array("ret" => "Et voi muokata itseäsi mahtavampaa!");
					} else {
						$u->level = $level;
						$u->save();
						$levels = array(1 => "Peruskäyttö", 2 => "Laaja käyttö", 3 => "BOFH", 4 => "ÜberBOFH");
						$return = array("ret" => true, "newlevel" => $levels[$level]);
					}
					break;
				case "user_pass":
					$pass = $_POST['pass'];
					$id = $_POST['row'];
					$u = Jelly::query('user', $id)->select();
					$secret = Kohana::$config->load('auth.secret');
					$u->passu = sha1($pass . $secret);
					$u->save();
					$return = array("ret" => true);
					break;
				case "user_new":
					$user = $_POST['user'];
					$pass = $_POST['pass'];
					$level = $_POST['level'];
					if ($level > $this->session->get('level')) {
						$return = array("ret" => "Et voi luoda itseäsi mahtavampaa!");
					} else {
						$secret = Kohana::$config->load('auth.secret');
						$d = Jelly::factory('user')->set(array('kayttis' => $user,
								'passu' => sha1($pass . $secret),
								'level' => $level,
								'last_login' => 0
								))->save();
						$return = array("ret" => true);
					}
					break;
				case "ohjelma_add":
					$post = $_POST;
					if (strcasecmp($post['pituus'], "muu") === 0) {
						$post['kesto'] = $post['muupituus'];
					} else {
						$post['kesto'] = $post['pituus'];
					}
					Jelly::factory('ohjelma')->set(Arr::extract($post, array('otsikko', 'pitaja', 'kategoria', 'kesto', 'kuvaus')))->save();
					$return = array("ret" => true);
					break;
				case "tapahtuma_save":
					$post = $_POST;
					$alkustamp = strtotime($post['start'] . " " . $post['starth'] . ":" . $post['startm']);
					$loppustamp = strtotime($post['stop'] . " " . $post['stoph'] . ":" . $post['stopm']);
					$new = Jelly::query('tapahtuma')->count();
					if ($new == 0) {
						Jelly::factory('tapahtuma')->set(array('alkuaika' => $alkustamp, 'loppuaika' => $loppustamp, 'nimi' => "Tracon 7"))->save();
					} else {
						$d = Jelly::query('tapahtuma')->limit(1)->select();
						$d->alkuaika = $alkustamp;
						$d->loppuaika = $loppustamp;
						$d->save();
					}
					$return = array("ret" => true);
					break;
				case "kategoria_add":
					$post = $_POST;
					Jelly::factory('kategoriat')->set(Arr::extract($post, array('tunniste', 'nimi', 'vari', 'fontti')))->save();
					$return = array("ret" => true);
					break;
				case "slot_add":
					$post = $_POST;
					Jelly::factory('slotit')->set(Arr::extract($post, array('pituus', 'selite')))->save();
					$return = array("ret" => true);
					break;
				case "sali_add":
					$post = $_POST;
					$post['tunniste'] = str_replace(" ", "_", strtolower($post['nimi']));
					Jelly::factory('salit')->set(Arr::extract($post, array('tunniste', 'nimi')))->save();
					$return = array("ret" => true);
					break;
				case "ohjelma_save":
					$post = $_POST;
					$predata = Jelly::query('ohjelma', $post['id'])->select_column('kesto')->select();
					$querytesti = "SELECT id
                     FROM   " . __tableprefix . "ohjelma
                     WHERE  alkuaika BETWEEN '" . date('Y-m-d H:i:s', $post['hour']) . "' AND '" . date('Y-m-d H:i:s', $post['hour'] + $predata->kesto * 60 - 1) . "'
                            AND
                            sali = :sali
                            AND
                            id != :id
                    ";
					$check = DB::query(Database::SELECT, $querytesti)->parameters(array(":sali" => $post['sali'], ":id" => $post['id']))->execute(__db);
					if ($check->count() === 0) {
						$d = Jelly::query('ohjelma', $post['id'])->select();
						$d->alkuaika = $post['hour'];
						$d->sali = $post['sali'];
						$d->save();
						$return = array("ret" => true);
					} else {
						$return = array("ret" => false);
					}

					break;
				case "ohjelma_load":
					$post = $_POST;
					$d = Jelly::query('ohjelma')->where('sali', 'like', $post['sali'])->select();
					$ret = array();
					foreach($d as $row) {
						$lyhyt_otsikko = substr(htmlspecialchars($row->otsikko), 0, 25);
						trim($lyhyt_otsikko);
						if (strlen(htmlspecialchars($row->otsikko)) > 25)
							$lyhyt_otsikko .= "...";
						$checked = "";
						if ($row->hidden == 1)
							$checked = "checked=\"checked\"";
						$ret[] = array("oid" => $row->id, "hour" => strtotime($row->alkuaika), "kategoria" => $row->kategoria, "height" => ($row->kesto - 12 + ($row->kesto / 45 * 3)), "title" => "" . htmlspecialchars($row->otsikko) . "\nPitäjä: " . $row->pitaja . "\nKategoria: " . $row->kategoria . "\nKesto: " . $row->kesto . "min\nKuvaus: " . $row->kuvaus . " ", "nimi" => $lyhyt_otsikko . "<input name=\"piilotettu\" class=\"hid\" type=\"checkbox\" id=\"hidden-" . $row->id . "\" value=\"1\" " . $checked . " /><label class=\"hid\" for=\"hidden-" . $row->id . "\">Piilotettu?</label>");
					}
					$return = array("ret" => true, "ohjelmat" => $ret);
					break;
				case "ohjelma_dash":
					$salit = Jelly::query('salit')->select();
					$ret = "";
					$parser = new Model_Public();
					foreach($salit as $row) {
						$check = "<b>Nyt:</b> [" . $row->tunniste . "-nyt]<br/><b>Seuraavaksi:</b> [" . $row->tunniste . "-next]";
						$parsed = $parser->parse_ohjelmatags($check);
						$ret .= "<h3>" . $row->nimi . "</h3><p>" . $parsed[0] . "</p>";
					}
					$return = array("ret" => $ret);
					break;
				case "tuotanto_save":
					$post = $_POST;
					if (!empty($post['start']) && !empty($post['length']) && !empty($post['event']) && !empty($post['vastuu']) && !empty($post['duunarit'])) {
						$post['start'] = $post['start'] . " " . $post['hours'] . ":" . $post['mins'];
						$categories = implode(',', $post['category']);
						$post['category'] = $categories;
						Jelly::factory('tuotanto')->set(Arr::extract($post, array('priority', 'category', 'type', 'notes', 'start', 'hours', 'mins', 'length', 'event', 'vastuu', 'duunarit')))->save();
						$return = array("ret" => true);
					} else {
						$return = array("ret" => false, "msg" => "Kaikki paitsi lisätietokenttä ovat pakollisia!");
					}
					break;
				case "tuotanto_refresh":
					$data = Jelly::query('tuotanto')->order_by('start', 'ASC')->select();
					$tablebody = "<tablebody>";
					$priority = array('0 - Triviaali', '1 - Matala', '2 - Normaali', '3 - Korkea', '4 - Ehdoton');
					$category = array('ohjelma' => 'Ohjelma', 'viestinta' => 'Viestintä', 'tyovoima' => 'Työvoima', 'info' => 'Info', 'teema' => 'Teema', 'tilat' => 'Tilat', 'logistiikka' => 'Logistiikka', 'turva' => 'Turvallisuus', 'tekniikka' => 'Tekniikka', 'talous' => 'Talous', 'kunnia' => 'Kunniavieras', 'majoitus' => 'Majoitus', 'lipunmyynti' => 'Lipunmyynti', 'muu' => 'Muu');
					$type = array('public' => 'Julkinen', 'internal' => 'Sisäinen', 'ydin' => 'Ydinryhmä', 'note' => 'Huomio/kommentti');
					foreach($data as $row) {
						if ($row->loaded()) {
							$categories = explode(',', $row->category);
							$cats = array();
							foreach($categories as $cate)
							$cats[] = $category[$cate];
							$show_cats = implode(", ", $cats);
							$hilight = "";
							if ((strtotime($row->start) + ($row->length * 60)) >= time() && time() >= strtotime($row->start))
								$hilight = " class=\"new\"";
							$tablebody .= "    <tr$hilight id=\"" . $row->id . "\"><td type=\"prioriteetti\" class=\"prio-".$row->priority."\">" . $priority[$row->priority] . "</td><td type=\"kategoria\">" . $show_cats . "</td><td type=\"tyyppi\">" . $type[$row->type] . "</td><td type=\"alkuaika\">" . date('d.m.Y H:i', strtotime($row->start)) . "</td><td type=\"pituus\">" . $row->length . " min</td><td type=\"eventti\">" . $row->event . "</td><td type=\"lisat\">" . nl2br($row->notes) . "</td><td type=\"vastuullinen\">" . $row->vastuu . "</td><td type=\"tekijat\">" . $row->duunarit . "</td></tr>\n";
						}
					}
					$return = array("ret" => true, "rivit" => $tablebody);
					break;
				case "loads":
					$return = array("ret" => `cat /proc/loadavg|awk '{print $1,$2,$3}'`);
					break;
				case "tuotanto_dash":
					$data = Jelly::query('tuotanto')->where('start', '>=', DB::expr("DATE_SUB(NOW(), INTERVAL length MINUTE)"))->limit(10)->order_by('start', 'ASC')->select();
					$tablebody = "";
					$priority = array('0 - Triviaali', '1 - Matala', '2 - Normaali', '3 - Korkea', '4 - Ehdoton');
					$category = array('ohjelma' => 'Ohjelma', 'viestinta' => 'Viestintä', 'tyovoima' => 'Työvoima', 'info' => 'Info', 'teema' => 'Teema', 'tilat' => 'Tilat', 'logistiikka' => 'Logistiikka', 'turva' => 'Turvallisuus', 'tekniikka' => 'Tekniikka', 'talous' => 'Talous', 'kunnia' => 'Kunniavieras', 'majoitus' => 'Majoitus', 'lipunmyynti' => 'Lipunmyynti', 'muu' => 'Muu');
					$type = array('public' => 'Julkinen', 'internal' => 'Sisäinen', 'ydin' => 'Ydinryhmä', 'note' => 'Huomio/kommentti');
					foreach($data as $row) {
						if ($row->loaded()) {
							$categories = explode(',', $row->category);
							$cats = array();
							foreach($categories as $cate)
							$cats[] = $category[$cate];
							$show_cats = implode(", ", $cats);
							$hilight = "";
							if ((strtotime($row->start) + ($row->length * 60)) <= (time() + 300))// || (time() <= (strtotime($row->start) + 60) && time() >= strtotime($row->start)))
								$hilight = "blink end";
							elseif (time() >= (strtotime($row->start)-300) && time() <= strtotime($row->start))
								$hilight = "start";
							elseif ((strtotime($row->start) + ($row->length * 60)) >= time() && time() >= strtotime($row->start))
								$hilight = "blink";
							$tablebody .= "    <tr class=\"$hilight\" title=\"".$row->notes."\"><td class=\"prio-".$row->priority."\">" . $priority[$row->priority] . "<br/>" . $show_cats . "<br/>" . $type[$row->type] . "<br/>" . date('d.m.Y H:i', strtotime($row->start)) . "</td><td type=\"pituus\">" . $row->length . " min</td><td type=\"eventti\">" . $row->event . "</td><td><span class=\"vastuullinen\">" . $row->vastuu . "</span>, " . $row->duunarit . "</td></tr>\n";
						}
					}
					$return = array("ret" => true, "rivit" => $tablebody);
					break;
				case "instance":
					$this->session->set('instance', (int)$_POST['instance']);
					$return = array("ret" => true);
					break;
				case "dataset_change":
					$dataset = $_POST['dataset'];
					$query = DB::query(Database::UPDATE,
						'UPDATE  config ' . 'SET     value = :data ' . 'WHERE   opt = "tableprefix" '
						)->param(':data', $dataset)->execute(__db);
					$return = array("ret" => true);
					break;
				case "dataset_add":
					$post = $_POST;
					if (!is_numeric($post['prefix'])) {
						$return = array("ret" => "Tunnisteen tulee olla numero.");
					} else {
						Jelly::factory('asetukset')->set(Arr::extract($post, array('prefix', 'tapahtuma')))->save();
						$return = array("ret" => true);
					}
					break;
				case "instance_add":
					$post = $_POST;
					Jelly::factory('instances')->set(Arr::extract($post, array('nimi', 'selite')))->save();
					$return = array("ret" => true);
					break;
				case "tuotanto_del":
					$post = $_POST;
					Jelly::query("tuotanto", $post['row'])->delete();
					$return = array("ret" => true);
					break;
				case "tuotanto_populate":
					$post = $_POST;
					$row = Jelly::query("tuotanto", $post['row'])->select();
					$p["prioriteetti"] = $row->priority;
					$p["kategoria"] = explode(",", $row->category);
					$p["tyyppi"] = $row->type;
					$p["datestart"] = date("d.m.Y", strtotime($row->start));
					$p["tunnit"] = date("G", strtotime($row->start));
					$mins = date("i", strtotime($row->start));
					if($mins < 10)
        				$mins = substr($mins,1,1);
                    $p["minuutit"] = $mins;
					$p["pituus"] = $row->length;
					$p["event"] = $row->event;
					$p["lisat"] = $row->notes;
					$p["vastuu"] = $row->vastuu;
					$p["tekijat"] = $row->duunarit;
					$return = array("ret" => $p);
					break;
				case "tuotanto_edit":
					$post = $_POST;
					$d = Jelly::query("tuotanto", $post['row'])->select();
					$d->priority = $post['priority'];
					$d->category = implode(",", $post['category']);
					$d->type = $post['type'];
					$d->start = $post['start'] . " " . $post['hours'] . ":" . $post['mins'];
					$d->length = $post['length'];
					$d->event = $post['event'];
					$d->notes = $post['notes'];
					$d->vastuu = $post['vastuu'];
					$d->duunarit = $post['duunarit'];
					$d->save();
					$return = array("ret" => true);
					break;
				case "ohjelma_hide":
    				$post = $_POST;
    				$id = explode("-",$post['ohjelma']);
    				$id = $id[1];
    				if(is_numeric($id)){
        				$d = Jelly::query('ohjelma',$id)->select();
        				if($d->hidden == 1){
                            $d->hidden = false;
                            $v = "false";
                        }else{
                            $d->hidden = true;
                            $v = "true";
                        }
                        if($d->kesto == null)
                            $d->kesto = 0;
                        $d->save();
                        $return = array("ret" => true,"value" => $v);
                    }else{
                        if($id == "c"){
                            $return = array("ret"=>true);//salinappi
                        }
                    }
    				break;
    			case "ohjelma_loadedit":
                    $d = Jelly::query("ohjelma",$_POST['ohjelma'])->select();
                    $r['otsikko'] = $d->otsikko;
                    $r['kuvaus'] = $d->kuvaus;
                    $r['pitaja'] = $d->pitaja;
                    $r['kategoria'] = $d->kategoria;
                    $r['pituus'] = $d->kesto;
                    $return = array("ret" => $r);
                    break;
                case "ohjelma_edit":
                    $post = $_POST;
                    if (strcasecmp($post['pituus'], "muu") === 0) {
						$post['kesto'] = $post['muupituus'];
					} else {
						$post['kesto'] = $post['pituus'];
					}
                    $d = Jelly::query('ohjelma',$post['id'])->select();
                    $d->otsikko = $post['otsikko'];
                    $d->kuvaus = $post['kuvaus'];
                    $d->pitaja = $post['pitaja'];
                    $d->kategoria = $post['kategoria'];
                    $d->kesto = $post['kesto'];
                    $d->save();
                    $return = array("ret" => true);
                    break;
                case "ohjelma_del":
                    Jelly::query('ohjelma',$_POST['ohjelma'])->select()->delete();
                    $return = array("ret" => true);
                    break;
                case "tekstari_send":
                    $post = $_POST;
                    $sms = new Nexmo_Message();
                    $d = array();
                    preg_match_all('/(\d{12})/', $post['number'], $numbers, PREG_PATTERN_ORDER);
                    $time = count($numbers);
                    $exec = ($time * 2 / 10) + 2;
                    $exec = ceil($exec);
                    $errors = 0;
                    $jaljella = 0;
                    $sent = 0;
                    //Tekstareiden lähetykseen kuluva aika pyöristettynä seuraavaan sekuntiin + 2 sekuntia varoaikaa lisää.
                    set_time_limit($exec);
                    foreach($numbers[1] as $key => $number){
                        $d[] = $data = $sms->sendText($number,"Tracon",$this->utf8($post['message']));
                        if($data["code"][0] != 0){
                            $errors = 1;
                            $d = $data;
                            break;
                        }
                        $jaljella = $data["credit"];
                        $sent++;
                        usleep(200000);//200ms, 5 tekstaria sekunnissa.
                    }
                    if($errors == 1){
                        $return = array("ret" => "Lähetys epäonnistui! Syy: ".implode(", ",$d["msg"]));
                    }else{
                        if($sent > 1)
                            $m = "t";
                        else
                            $m = "";
                        $return = array("ret" => "Viesti".$m." (".$sent." kpl) lähetetty onnistuneesti! Saldoa jäljellä vielä ".$jaljella." €.");
                    }
                    break;
                case "tekstari_file":
                    $d = array();
                    $sms = new Nexmo_Message();
                    $input = fopen("php://input", "r");
                    $data = stream_get_contents($input);
                    fclose($input);
                    preg_match_all('/(\d{12})[;,](.*)/',$data,$matches, PREG_SET_ORDER);
                    $time = count($matches);
                    $exec = ($time * 2 / 5) + 15;
                    $exec = ceil($exec);
                    $errors = 0;
                    $jaljella = 0;
                    $sent = 0;
                    //Tekstareiden lähetykseen kuluva aika (olettaen että lähetetään tuplamittaisia viestejä) pyöristettynä seuraavaan sekuntiin + 15 sekuntia varoaikaa lisää.
                    set_time_limit($exec);
                    foreach($matches as $row){
                        $d[] = $data = $sms->sendText($row[1],"Tracon",$this->utf8($row[2]));
                        if($data["code"][0] != 0){
                            $errors = 1;
                            $d = $data;
                            break;
                        }
                        if($errors == 0)
                        	$jaljella = $data["credit"];
                        $sent++;
                        for($t=1;$t<=$data["count"][0];$t++){
                        	usleep(200000);//200ms, 5 tekstaria sekunnissa. Myös multi-part huomioitu.
                        }
                    }
                    if($errors == 1){
                        $return = array("ret" => "Lähetys epäonnistui! Viestejä ehdittiin lähettämään onnistuneesti ".$sent." kappaletta. Epäonnistumisen syy: ".implode(", ",$d["msg"]).". Saldoa jäi vielä ".$jaljella." €.","timeout"=>30000);
                    }else{
                        $return = array("success"=>true,"ret" => "Viestit (".$sent." kpl) lähetetty onnistuneesti! Saldoa jäljellä vielä ".$jaljella." €.");
                    }
                    break;

			}
		} else { // Jos käyttäjä ei ole kirjautunut sisään, tai ei ole admin. Estää abusoinnin siis.
			if ($this->session->get("logged_in", false)) {
				$return = array("ret" => "Sinulla ei ole oikeuksia tähän toimintoon.");
			} else {
				$ref = substr_replace(URL::base($this->request), "", $this->request->referer());
				$data = "<p>Sessio on vanhentunut. " . HTML::file_anchor('admin/?return=' . $ref, 'Kirjaudu uudelleen') . ", palaat takaisin tälle sivulle.</p>";
				$return = array("ret" => $data);
			}
		}
		if (isset($benchmark)) {
			// Stop the benchmark
			Profiler::stop($benchmark);
			$return['profiler'] = Profiler::stats(array($benchmark));
		}
		if ($param1 != "upload")// upload ei tykänny ylimääräsestä "" json-palautuksen lopussa.
			print json_encode($return);
	}

	private function get_instances()
	{
		$instances = Jelly::query('instances')->select();
		if ($instances->count() === 0) {
			Jelly::factory('instances')->set(array('rul_id' => 1, 'nimi' => 'Public', 'selite' => 'Julkinen'))->save();
			$instances = Jelly::query('instances')->select();
		}
		$instance_list = array();
		foreach($instances as $row) {
			if ($row->loaded()) {
				$instance_list[$row->inst_id] = $this->utf8($row->nimi);
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
	public function utf8($str)
	{
		if ($this->utf8_compliant($str) == 1) {
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
	public function utf8_compliant($str)
	{
		if (strlen($str) == 0) {
			return true;
		}
		return (preg_match('/^.{1}/us', $str, $ar) == 1);
	}
}

?>
