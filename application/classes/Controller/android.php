<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
* Admin-controlleri hallintapuolelle..
*
* @author Miika Ojamo <miika@vkoski.net>
*/
class Controller_Android extends Controller{

    public function before(){
        $db = Database::instance();
    	$this->session = Session::instance();
    }

     public function action_index(){
         $return = array("ret","Tuntematon kutsu.");
         print json_encode($return);
     }

     public function action_ajax($param1 = null,$param2 = null){
    	$return = "";
    	//if($this->request->param($param1)) $param1 = $this->request->param($param1);
    	if(($this->session->get('logged_in') and $this->session->get('level') > 0) or $param1 == "login"){//varmistetaan että on kirjauduttu sisään ja oikeudet muokata asioita.
        	switch($param1){
                case "scroller_save":
                    $post = $_POST;
                    $data = array();
                    $err = " ";
                    foreach($post as $key => $value){
                        $parts = explode("-",$key);
                        $rivi = $this->utf8($parts[1]);
                        $data[$rivi][$this->utf8($parts[0])] = $this->utf8($value);//automagiikka <input name="field-id" value="value"> -> $data['id']['field'] = value
                    }
                    foreach($data as $row => $datat){
                        if(empty($datat["text"]) && empty($datat["pos"])){
                        }elseif(empty($datat["text"]) || empty($datat["pos"])){
                            $err .= "Jokin kenttä jäi täyttämättä. Kyseisen rivin tietoja <strong>EI</strong> ole tallennettu.";
                        }else{
                            if(!isset($datat["hidden"]))//checkkaamattomat checkboxit ei tuu mukaan ollenkaan.
                                $datat["hidden"] = false;
                            if($datat["hidden"] == "true")
                                $datat["hidden"] = true;
                            elseif($datat["hidden"] == "false")
                                $datat["hidden"] = false;
                            else
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
                    $return = array("ret"=>"Scroller päivitetty.");
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
                    $text = array();
                    if($result) foreach($result as $row=>$data){
                        $text[$row] = array("id" => $data["id"],"position" => $data["pos"],"text" => $data["text"],"hidden" => $data["hidden"]);
                    }
                    $return = array("ret" => $text);
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
                        $ret = "Scrollerinpala poistettu.";
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
                    }

                    if(!$result){
                        $pos = $id - 499;
                    }else{
                        $pos = $id - 499 + $result[0]["pos"];
                    }
                    $text = array("id" => $id,"pos" => $pos, "diat" => $vaihtoehdot,'time' => Date::seconds(1,1,121),'hidden' => false);
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
                            if(!isset($datat["hidden"]))//checkkaamattomat checkboxit ei tuu mukaan ollenkaan.
                                $datat["hidden"] = false;
                            if($datat["hidden"] == "true")
                                $datat["hidden"] = true;
                            elseif($datat["hidden"] == "false")
                                $datat["hidden"] = false;
                            else
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
                    }

                    if($result) foreach($result as $row=>$data){
                        if($data['type'] == 2)//jos twitter.
                            $selector = 0;//joka on aina ensimmäinen vaihtoehdoista.
                        else
                            $selector = $data['selector'];
                        $text[$row] = array("id"=>$data["id"],'pos' => $data["pos"],"sel" => $selector, 'text' => $vaihtoehdot[$selector],'time' => $data["time"],'hidden'=>(boolean)$data["hidden"]);
                    }
                    $return = array("ret"=>$text);
                    break;
                case "rulla_loadall":
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
                    $vaihtoehdot[0]["id"] = 0;
                    $vaihtoehdot[0]["text"] = "twitter";
                    if($result2) foreach($result2 as $row => $data){
                        $rivi = $row + 1;
                        $vaihtoehdot[$rivi]["text"] = $this->utf8($data['tunniste']);
                        $vaihtoehdot[$rivi]["id"] = $this->utf8($data['id']);
                    }

                    $return = array("ret"=>$vaihtoehdot);
                    break;
                case "rulla_delete":
                    if(!$param2){
                        $ret = "Ongelma rivin poistamisessa";
                    }else{
                        $query = DB::query(Database::DELETE,
                                            "DELETE FROM rulla ".
                                            "WHERE       rul_id = :id"
                                            );
                        $query->param(":id",$param2)->execute(__db);
                        $ret = "Rivi poistettu!";
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
                            $ret = array("loota",$this->utf8($data[0]['data']));
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
                        $ret = "Dia tallennettu!";
                    }else{
                        //data ei tullu perille :|
                        $ret = "Dian tallennus epäonnistui.";
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
                            $ret = "Dia poistettu.";
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
                            $ret = "Stream poistettu";
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

                    if($result) foreach($result as $row => $data){
                        $text = array("id" => $data['stream_id'],"ident" => $data['tunniste'],"url"=>$data['url'],"jarkka"=>$data['jarjestys']);
                    }

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
                            $text = array("id"=>$data['f_id'],"ident"=>$data['tunniste'],"show_tv"=>$data['show_tv'],"show_stream"=>$data['show_stream'],"dia"=>$data['dia'],"use_global"=>(boolean)$data['use_global'],"streams"=>$streams,"diat"=>$diat);
                        }
                    }else{
                        $text = "Yhtään aktiivista frontendiä ei löytynyt.";
                    }
                    $return = array("ret" => $text);
                    break;
                case "login":
                    $auth = new Model_Authi();
                    $login = $auth->auth($_POST['user'],$_POST['pass']);
                    if($login !== false){
                        $this->session->set('logged_in',true);//true/false
                        $this->session->set('level',$login);//>= 1
                        $this->session->set('user',$_POST['user']); //käyttäjätunnus.
                        $return = array("ret" => "login ok");
                    }else{
                        $return = array("ret" => "login failed");
                    }
                    break;
            }
            $ok = true;
    	}else{//Jos käyttäjä ei ole kirjautunut sisään, tai ei ole admin. Estää abusoinnin siis.
        	if(empty($_SERVER['HTTP_REFERER'])) $referer = "";//pitää tehdä vaikeesti koska kohanassa ei oo suoraa tähän funkkaria.
        	else $referer = $_SERVER['HTTP_REFERER'];
            $ref = substr_replace(URL::site('/'), "", $referer);
            $data = "Sessio on vanhentunut. Kirjaudu uudelleen sisään.";
            $return = array("ret" => $data);
            $ok = false;
        }
        $return["session"] = $ok;
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