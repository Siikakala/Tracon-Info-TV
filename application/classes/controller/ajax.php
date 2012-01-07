<?php defined('SYSPATH') or die('No direct script access.');
/**
* Admin-controlleri hallintapuolelle..
*
* @author Miika Ojamo <miika@vkoski.net>
*/
class Controller_Ajax extends Controller{

    public function before(){
        $db = Database::instance();
    	$this->session = Session::instance();
    }

    /**
    * Warning! Casting Magics ahead!
    *
    * Tässä metodissa tapahtuu siis 90% koko järjestelmän toiminnallisuudesta.
    */
    public function action_ajax(){
        $param1 = $this->request->param('param1',null);
        $param2 = $this->request->param('param2',null);
    	$return = "";
    	$this->session->set('results',array());
    	$kutsut = array(
        	"infotv-common" => array(
                  "kutsut" =>
                      array("scroller_save","scroller_load","scroller_delete","scroller_delete","rulla_row","rulla_save","rulla_load","rulla_delete","tv","dia_load","dia_save","dia_delete","stream_load","frontend_load","upload","ohjelma","lastupdate"),
                  "level"  => 1
                  ),
            "infotv-adv" => array(
                  "kutsut" =>
                      array("stream_delete","stream_save","frontend_save"),
                  "level"  => 3
                  ),
            "logi-common" => array(
                  "kutsut" =>
                      array("todo_save","todo_refresh","todo_search"),
                  "level"  => 1
                  ),
            "logi-adv" => array(
                  "kutsut" =>
                      array("populate_logi"),
                  "level"  => 3
                  ),
            "public" => array(
                  "kutsut" =>
                      array("check"),
                  "level" => 0
                  )
              );

        function search($array,$key,$search){
            $data = array_search($search,$array["kutsut"]);
            if($data === false){
            }else{
                array_push($_SESSION['results'],$key);
            }
        }
        array_walk($kutsut,"search",$param1);
        $resultsi = $this->session->get('results',null);
        if(empty($resultsi)){
            $kutsu_ok = false;
            throw new Kohana_Exception("Kutsua :param1 ei löydy",array(":param1"=>$param1),E_WARNING);
        }else{
            $kutsu_ok = true;
        }
        if(count($resultsi) > 1)
            throw new Kohana_Exception("Kutsu määritelty useampaan kertaan. :param1 määritelty ryhmissä :kutsut.",array(":param1"=>$param1,":kutsut"=>implode(", ",$resultsi)),E_NOTICE);

        if($kutsu_ok !== true){
            $return = array("ret"=>false);
        }elseif($this->session->get('level',0) >= $kutsut[$resultsi[0]]['level']){//varmistetaan että on kirjauduttu sisään ja oikeudet muokata asioita.
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
                        $streams = $ret;
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
                    // /^O::(.{6,12})::(.{0,20})::(\d+)::(\d+)::(.{0,80})::(.{0,80})::(.{0,15})$(.{0,2000})$/misU
                    // ^ ei välttämättä toimi täysin. Rubularin mukaan ei matchaan multi-line kuvauksiin, parin muun testerin mukaan ei matchaa kuvaukseen.
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
                  trim($post['adder']);
                  trim($post['comment']);
                  if(empty($post['comment']) or empty($post['adder'])){
                      $ret = "";
                      if(empty($post['comment']) and empty($post['adder']))
                          $ret = "Molemmat kentät ovat tyhjiä, täytä ne ja lisää rivi sen jälkeen uudelleen.";
                      elseif(empty($post['comment']))
                          $ret = "Viesti puuttuu! Kirjoita se ensin.";
                      elseif(empty($post['adder']))
                          $ret = "Lisääjä on pakko ilmoittaa.";
                      $return = array("ret" => $ret,"ok"=>false);
                  }else{
                      Jelly::factory('logi')
                                  ->set(array(
                                      "tag"     => $post['tag'],
                                      "comment" => $post['comment'],
                                      "adder"   => $post['adder']
                                  ))->save();
                      $return = array("ret"=>"Rivi lisätty onnistuneesti.","ok"=>true);
                  }
                  break;
              case "todo_refresh":
                    $query = DB::query(Database::SELECT,
                                       'SELECT   tag '.
                                       '        ,comment '.
                                       '        ,adder '.
                                       '        ,stamp '.
                                       'FROM     logi '.
                                       'ORDER BY stamp DESC '.
                                       'LIMIT    20'
                                       )->execute(__db);
                    $text = "<table class=\"stats\" style=\"color:black\"><tr><th>Aika</th><th>Tyyppi</th><th>Viesti</th><th>Lisääjä</th></tr>";
                    $types = array("tiedote"=>"Tiedote","ongelma"=>"Ongelma","kysely"=>"Kysely","löytötavara"=>"Löytötavara","muu"=>"Muu");
                    foreach($query as $row){
                        $text .= "<tr class=\"type-".$row['tag']."\"><td>".date("d.m. H:i",strtotime($row['stamp']))."</td><td>".$types[$row['tag']]."</td><td>".$row['comment']."</td><td>".$row['adder']."</td></tr>";
                    }
                    $text .= "</table>";
                    $return = array("ret"=>$text);
                    break;
              case "todo_search":
                    $param = $_POST['search'];
                    $types = array("tiedote"=>"Tiedote","ongelma"=>"Ongelma","kysely"=>"Kysely","löytötavara"=>"Löytötavara","muu"=>"Muu");
                    $rows = Jelly::select('logi')
                                    ->or_where('tag','REGEXP','.*'.$param.'.*')
                                    ->or_where('comment','REGEXP','.*'.$param.'.*')
                                    ->or_where('adder','REGEXP','.*'.$param.'.*')
                                    ->or_where('stamp','REGEXP','.*'.$param.'.*')
                                    ->order_by('stamp','DESC')
                                    ->execute();
                    $text = "<table class=\"stats\"><tr><th>Aika</th><th>Tyyppi</th><th>Viesti</th><th>Lisääjä</th></tr>";

                    foreach($rows as $row){
                        $text .= "<tr class=\"type-".$row->tag."\"><td>".date("d.m. H:i",strtotime($row->stamp))."</td><td>".$types[$row->tag]."</td><td>".$row->comment."</td><td>".$row->adder."</td></tr>";
                    }
                    $text .= "</table>";
                    $return = array("ret"=>$text);
                    break;
              case "populate_logi":
                    $types = array("tiedote"=>"Tiedote","ongelma"=>"Ongelma","kysely"=>"Kysely","löytötavara"=>"Löytötavara","muu"=>"Muu");
                    $keys = array_keys($types);
                    function rand_word(){
                        return substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',15)),0,rand(2,15));
                    }
                    $q = DB::query(Database::SELECT,
                                    'SELECT tag FROM logi'
                                    )->execute(__db);
                    $riveja = $q->count();

                    $query = DB::query(Database::INSERT,
                                        'INSERT INTO logi (tag,comment,adder) '.
                                        'VALUES (:tag,:comment,:adder)'
                                        );
                    for($i=1;$i<600;$i++){
                        $randomi = "";
                        $sanoja = rand(0,10);
                        for($y=0;$y<=$sanoja;$y++){
                            $randomi .= " ".rand_word();
                        }
                        $kom = $riveja + $i;
                        $query->parameters(array(":tag"=>$keys[rand(0,4)],":comment"=>"Kommentti $kom: $randomi",":adder"=>"Automagia"))->execute(__db);
                    }
                    break;
              case "check":
                    $provider = new Model_Public();
                    if(date("s")%20 == 0)
                        $over = true;
                    else
                        $over = false;
                    $return = array(
                        "ret" => true,
                        "page" => $this->session->get("page",0),
                        "dia" => $provider->page(),
                        "fcn" => $provider->fcn(),
                        "scroller" => $provider->scroller($over)
                        );
                    break;
            }
    	}else{//Jos käyttäjä ei ole kirjautunut sisään, tai ei ole admin. Estää abusoinnin siis.
        	if($this->session->get("logged_in",false)){
                $return = array("ret" => "Sinulla ei ole oikeuksia tähän toimintoon.");
            }else{
                $ref = substr_replace(URL::base($this->request), "", $this->request->referer());
                $data = "<p>Sessio on vanhentunut. ".html::file_anchor('admin/?return='.$ref,'Kirjaudu uudelleen').", palaat takaisin tälle sivulle.</p>";
                $return = array("ret" => $data);
            }
        }
        if($param1 != "upload")//upload ei tykänny ylimääräsestä "" json-palautuksen lopussa.
            print json_encode($return);
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
}
?>