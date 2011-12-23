<?php defined('SYSPATH') or die('No direct script access.');

class Model_Public extends Model_Database {

     public function before(){
         parent::before();
         $session = Session::instance();
     }

     public function page(){
         $session = Session::instance();
        $return = "";

        if($session->get("global",1) == 1){//2. parametri = mihin defaultataan. Käytetäänkö globaalia hallintaa vai yksilöityä
            $query = DB::query(Database::SELECT,//joo, on tyhmää tehdä kahta integeriä varten oma table, ja pistää ne vielä omiks riveikseen. "It seemed like good idea at the time"
                                "SELECT  opt ".
                                "       ,value ".
                                "FROM    config"
                                )->execute(__db);
            foreach($query as $row){
                $session->set($row['opt'],$row['value']);//survotaan sessioon. Tuolta tulee siis show_tv ja show_stream.
            }
        }else{//yksilöityä.
            $query = DB::query(Database::SELECT,
                                "SELECT   show_tv ".
                                "        ,show_stream ".
                                "        ,dia ".
                                "FROM     frontends ".
                                "WHERE    uuid = '".$session->get("uid",false)."' ".
                                "LIMIT    1"
                                )->execute(__db);
            $result = $query->as_array();
            $session->set("show_tv",$result[0]['show_tv']);
            $session->set("show_stream",$result[0]['show_stream']);
            $session->set("dia",$result[0]['dia']);
        }

        switch($session->get("show_tv",0)){
            case 1://stream
                $old = $session->get("old_stream",false);//onko streami vaihtunut?
                $stream = $session->get("show_stream");
                $session->set("old_stream",$stream);

                if($old == $stream){//nothing has changed.
                    $return = array("changed" => false);

                }else{//Siirrytään streamiin, tai vaihdetaan toiseen streamiin.
                    $query = DB::query(Database::SELECT,//kaivetaan urli.
                                        "SELECT    url ".
                                        "FROM      streamit ".
                                        "WHERE     stream_id = :id"
                                        );
                    $query->param(":id",$stream);
                    $result = $query->execute(__db)->as_array();
                    $return = array("changed" => true, "part" => "video", "palautus" => "<a id=\"player\" href=\"".$result[0]['url']."\" style=\"display:block;width:960px;height:490px;\"></a>", "video" => $result[0]['url']);
                }
                break;
            case 2://staattinen dia
                $old_d = $session->get("old_dia",false);//vastaava kuin streameilla.
                $dia = $session->get("dia");
                $session->set("old_dia",$dia);
                if($old_d == $dia){
                    $return = array("changed" => false);
                }else{
                    if($session->get("dia") != 0){ //dia-id 0 = twitter-feed
                        $query2 = DB::query(Database::SELECT,
                                            "SELECT    data ".
                                            "FROM      diat ".
                                            "WHERE     dia_id = ".$dia
                                            )->execute(__db);
                        if($query2->count() > 0)
                            $result2 = $query2->as_array();
                        else
                            $result2 = false;

                        if($result2 !== false){
                            $return = array("changed" => true, "part" => "text", "palautus" => $this->utf8($result2[0]["data"]));
                        }else{
                            $return = array("changed" => false);
                        }
                    }else{
                        $return = array("changed" => true, "part" => "twitter", "palautus" => "");
                    }
                }
                break;
            default://diashow
                $query = DB::query(Database::SELECT,//montakos niitä näytettäviä dioja oli diashowssa..?
                                    "SELECT pos ".
                                    "FROM   rulla ".
                                    "WHERE  hidden = 0"
                                    )->execute(__db);
                $max = $query->count();
                $max--;
                if($session->get("time")){//jos timelimit on olemassa
                    $timestamp = $session->get("timestamp") + $session->get("time");
                    if(time() > $timestamp){//onko diaa näytetty jo tarpeeksi kauan?
                        $new_page = $session->get("page",0) + 1;
                        if($max){
                            if($new_page > $max){
                                $new_page = 0;
                            }
                        }else{
                            $new_page = 0;
                        }
                        $session->set("page",$new_page);
                        $return = $this->get_diadata();
                    }else{
                        $return = array("changed" => false);
                    }
                }else{
                    $session->set("timestamp",time());
                    $session->set("time",1);
                    $session->set("page",0);
                    $return = array("changed" => false);
                }

                break;
        }
        return $return;
     }

     public function get_diadata(){
        $session = Session::instance();
        $query1 = DB::query(Database::SELECT,//kaivetaas diashow-data kannasta.
                            "SELECT    type ".
                            "         ,`time` ".
                            "         ,selector ".
                            "FROM      rulla ".
                            "WHERE     hidden = 0 ".
                            "ORDER BY  pos "
                            )->execute(__db);
        if($query1->count() > 0)
            $result1 = $query1->as_array();
        else
            $result1 = false;

        $kohta = $session->get("page",0);
        switch($result1[$kohta]["type"]){
            case 1://dia
                $query2 = DB::query(Database::SELECT,
                                    "SELECT  data ".
                                    "FROM    diat ".
                                    "WHERE   dia_id = ".$result1[$kohta]["selector"]
                                    )->execute(__db);
                if($query2->count() > 0)
                    $result2 = $query2->as_array();
                else
                    $result2 = false;

                //allaoleva on aivan kaamea putkitus mutta: kannasta saatu data varmistetaan utf-8:ksi, jonka jälkeen parsitaan vielä ohjelmakarttatagit.
                $parsed = $this->parse_ohjelmatags($this->utf8($result2[0]["data"]));//[0] = parsittu teksti [1] = näytetäänkö progresspie
                $return = array("changed" => true, "part" => "text", "palautus" => $parsed[0], "pie" => $parsed[1]);
                $session->set("timestamp",time());//koska dia on vaihdettu
                $session->set("time",$result1[$kohta]["time"]);//kauanko näytetään.
                break;
            case 2://twitter
                $return = array("changed" => true, "part" => "twitter", "palautus" => "");
                $session->set("timestamp",time());
                $session->set("time",$result1[$kohta]["time"]);
                break;
        }//switch
        return $return;
     }

     /**
    * Tämä action vastaa clientin tunnistamisesta.
    */
    public function fcn(){ //Frontend Client Name
        $session = Session::instance();
        Cookie::$salt = "Suola on perseessäs";//Koska kohana haluaa kekseilleen suolan.

    	if(Cookie::get("uid",false) == false){//jos keksiä ei ole.
            $uuid = md5(uniqid(rand(), true));//generoidaan uusi uuid.
            Cookie::set("uid",$uuid,2419200);//keksin parasta ennen-päiväykseen neljä viikkoa.
            $session->set("uid",$uuid);
            $query = DB::query(Database::INSERT,
                                "INSERT INTO frontends ".
                                "           (tunniste ".
                                "           ,uuid ".
                                "           ,last_active ".
                                "           )".
                                "VALUES     ('Undefined' ".
                                "           ,'".$uuid."' ".
                                "           ,NOW() ".
                                "           )"
                              )->execute(__db);
        }elseif(!$session->get("uid",false)){//jos uuid-dataa ei ole sessiossa, mutta keksi löytyy.
            $session->set("uid",Cookie::get("uid"));
            $query = DB::query(Database::SELECT,
                                "SELECT   tunniste ".
                                "        ,use_global ".
                                "FROM     frontends ".
                                "WHERE    uuid = '".Cookie::get("uid")."'"
                                )->execute(__db);
            if($query->count() < 0){//keksi elää pidempään kuin data kannassa.
                //eiolee.
                $uuid = Cookie::get("uid");
                $query = DB::query(Database::INSERT,
                                    "INSERT INTO frontends ".
                                    "           (tunniste ".
                                    "           ,uuid ".
                                    "           ,last_active ".
                                    "           )".
                                    "VALUES     ('Undefined' ".
                                    "           ,'".$uuid."' ".
                                    "           ,NOW() ".
                                    "           )"
                                  )->execute(__db);
            }else{
                $result = $query->as_array();
                $session->set("global",$result[0]['use_global']);
                $session->set("client",$result[0]['tunniste']);
            }
        }else{//jos keksi ja sessiodata on kunnossa.
            $query = DB::query(Database::UPDATE,//ilmiannetaan frontendin aktiivisuus
                                "UPDATE frontends ".
                                "SET    last_active = NOW() ".
                                "WHERE  uuid = '".$session->get("uid")."'"
                                )->execute(__db);
            $query2 = DB::query(Database::SELECT,//varmistetaan tuorein data.
                                "SELECT  use_global ".
                                "       ,tunniste ".
                                "FROM    frontends ".
                                "WHERE   uuid = '".$session->get("uid")."'"
                                )->execute(__db);
            $result = $query2->as_array();
            $session->set("global",$result[0]['use_global']);
            $session->set("client",$result[0]['tunniste']);
        }

        $client_name = $session->get("client","Undefined");

        $ret = array("name"=>$client_name);
        return $ret;
    }

    public function scroller($override = false){//scrollerin päivitys.
        $scroll = false;//oletuksena scroller ei ole muuttunut
        $session = Session::instance();
        if(!$session->get("scrollstamp")){//jos edellisen päivityksen aikaleimaa ei löydy..
            $session->set("scrollstamp",time());//asetetaan aikaleima,
            $override = true;//ja pakotetaan päivitys.
        }

        $query1 = DB::query(Database::SELECT,//Koska scrolleria on päivitetty viimeksi?
                            "SELECT MAX(`set`) as \"max\"".
                            "FROM   scroller"
                            )->execute(__db);
        if($query1->count() > 0)
            $result1 = $query1->as_array();
        else
            $result1 = false;//scroller on tyhjä.

        if($result1){
            $stamp = strtotime($result1[0]["max"]);//unix-timestampiksi muunnos
            if($stamp > $session->get("scrollstamp") || $override){//jos kannasta löytyy tuoreampaa dataa kuin viimeisin päivitys TAI jos kyseessä on pakotettu päivitys
                $query2 = DB::query(Database::SELECT,//haetaan kaikki piilottamattomat scrollerinpalat.
                                    "SELECT   text ".
                                    "FROM     scroller ".
                                    "WHERE    hidden = 0 ".
                                    "ORDER BY pos"
                                    )->execute(__db);
                if($query2->count() > 0)
                    foreach($query2 as $row){
                        $scrolli[] = $this->utf8($row['text']);
                    }
                $scroll = implode(" &raquo; ",$scrolli);
            }//else = mikään ei muuttunut -> palauttaa falsen
        }else
            $scroll = "Info-TV";//jos scroller olisi muuten tyhjä, näytetään ainakin "Tracon Info-TV"

        if($scroll)
            $return = array("changed" => true,"palautus"=>$scroll);
        else
            $return = array("changed"=>false);
        $session->set("scrollstamp",time());//muistetaan vielä asettaa se päivitysaika sessiomuuttujaan.
        return $return;
    }

     public function parse_ohjelmatags($text){
        $text = $this->utf8($text);
        $days = array(1=>"Maanantai",2=>"Tiistai",3=>"Keskiviikko",4=>"Torstai",5=>"Perjantai",6=>"Lauantai",0=>"Sunnuntai");
        //* //<- poista ensimmäinen kauttaviiva jos haluat määritellä ajankohdan manuaalisesti
        if(date("w") == 6 or date("w") == 0){//jos on lauantai tai sunnuntai
            $paiva = $days[date("w")]; //viikonpäivä
            $nyt = date("G") - constant("ALKUAIKA_".$paiva) + 1 ;//koska muuten kellon tunnin liian vähän. G = 24h-tunnit ilman etunollaa.
            $force_days = false;
        }else{
        //*/
            $paiva = "Lauantai";
            $nyt = 1;//7 on hyvä debuggi lauantaille.
            $force_days = true; //muuta true:ksi jollet debuggaa!
        }//<- kommentoi myös tämä.
        if($nyt < 1)//päivän eka ohjelmaslotti on 1
            $nyt = 1;
        preg_match_all("/(\[.{1,20}\])/",$text,$matches);//etsi kaikki 1-20 merkin mittaiset [tagit]
        $spin = array(false);

        $korvaajat = array();
        $korvattavat = array();
        //print_r($matches);
        foreach($matches[1] as $key => $match){//käydään kaikki tagit läpi.
            if($match == "[aika]"){//tässä tagissa ei ole väliviivaa, joten erillisprosessointi.
                $a = constant("ALKUAIKA_".$paiva) + $nyt - 1;//koska muuten eletään tuntia liian pitkällä. Kompensoidaan siis aikaisempi kompensointi.
                $b = constant("ALKUAIKA_".$paiva) + $nyt;
                $korvaa = $a." - ".$b;
                $text = str_replace("[aika]",$korvaa,$text);
            }else{//"väliviivalliset"
                $parts = explode("-",$match);
                if(count($parts) == 1){//typotettu tagi
                    $korvaaja2 = "-";
                    $text = str_replace($match,$korvaaja2,$text);
                    break;
                }
                $sali = str_replace(" ","_",strtolower(substr($parts[0],1)));//salin nimi ([Iso Sali -> iso_sali)
                $koska = substr($parts[1],0,-1);//nyt|next

                //huomio allaolevasta: Mikäli jokin osa tagista typotettu, tai ko. salissa ei ole (enää) ohjelmaa, tagi korvataan viivalla (-). Koodi osaa katsoa seuraavan vuorokauden puolelle.
                if(strncasecmp($koska,"nyt",3) == 0){
                    $query = DB::query(Database::SELECT,//kaivetaan kannasta tällä hetkellä halutussa salissa pyörivä ohjelma.
                                        "SELECT  nimi ".
                                        "       ,alku ".
                                        "       ,kesto ".
                                        "       ,alku+kesto as \"loppu\" ".//tämä on periaatteessa turhia tässä queryssä.
                                        "FROM    ohjelmadata ".
                                        "WHERE   sali = :sali ".
                                        "        AND ".
                                        "        ($nyt >= alku AND $nyt < alku+kesto) ".
                                        "        AND ".
                                        "        paiva = :paiva ".
                                        "LIMIT   1"
                                        );
                    $query->parameters(array(":sali"  => $sali,
                                             ":paiva" => $paiva
                                            ));
                    $result = $query->execute(__db);
                    if($result->count() > 0){
                        $data = $result->as_array();
                    }else{
                        $data = false;
                    }
                    if($data){//ohjelmanumero löytyi / ohjelmaa on.
                        $korvaaja = $this->utf8($data[0]['nimi']);
                        if($force_days)
                            $korvaaja = "(".substr($paiva,0,2).") ".$korvaaja;
                        $korvaaja = '<div id="'.$key.'" class="timer fill"></div>'.$korvaaja;
                        if(45 <= ((($data[0]['kesto']/($nyt - $data[0]['alku'] + 1)) * 60) - 15)){//(((02/(7-5+1))*60) + 02 - 15)*100
                            $ny = idate("i");
                            $positio = (((int)$nyt - (int)$data[0]['alku']) * 60) + $ny;
                            $spin[$key] = ((int)$positio * 100) / (((int)$data[0]['kesto'] * 60) - 15);
                            if($spin[$key]>100)
                                $spin[$key] = 100;
                            elseif($spin[$key]<0)
                                $spin[$key] = 0;
                        }else
                            $spin[$key] = 100;
                    }else{//ohjelmaa ei ole.
                        $korvaaja = "-";
                    }
                    $text = str_replace($match,$korvaaja,$text);//korvataan kaikki kyseiset tagit.
                }elseif(strncasecmp($koska,"next",4) == 0){
                    $query2 = DB::query(Database::SELECT,//kaivetaan halutun salin seuraava ohjelmanumero.
                                        "SELECT  nimi ".
                                        "       ,alku ".
                                        "       ,kesto ".
                                        "       ,alku+kesto as \"loppu\" ".
                                        "FROM    ohjelmadata ".
                                        "WHERE   sali = :sali ".
                                        "        AND ".
                                        "        alku > $nyt".
                                        "        AND ".
                                        "        paiva = :paiva ".
                                        "LIMIT   1"
                                        );
                    $query2->parameters(array(":sali"  => $sali,
                                              ":paiva" => $paiva
                                             ));
                    $result2 = $query2->execute(__db);
                    if($result2->count() > 0){
                        $data2 = $result2->as_array();
                    }else{
                        $data2 = false;
                    }
                    if($data2){
                        $a = constant("ALKUAIKA_".$paiva) + $data2[0]['alku'] - 1;
                        $b = constant("ALKUAIKA_".$paiva) + $data2[0]['loppu'] - 1;
                        $korvaaja2 = $a." - ".$b." ".$this->utf8($data2[0]['nimi']);
                        if($force_days)
                            $korvaaja2 = "(".substr($paiva,0,2).") ".$korvaaja2;
                    }elseif(strncasecmp($paiva,"Lauantai",8) == 0){
                        $query3 = DB::query(Database::SELECT,//kaivetaan halutun salin seuraava ohjelmanumero seuraavana päivänä.
                                            "SELECT  nimi ".
                                            "       ,alku ".
                                            "       ,kesto ".
                                            "       ,alku+kesto as \"loppu\" ".
                                            "FROM    ohjelmadata ".
                                            "WHERE   sali = :sali ".
                                            "        AND ".
                                            "        alku > 0".
                                            "        AND ".
                                            "        paiva = :paiva ".
                                            "LIMIT   1"
                                            );
                        $query3->parameters(array(":sali"  => $sali,
                                                  ":paiva" => "Sunnuntai"
                                                 ));
                        $result3 = $query3->execute(__db);
                        if($result3->count() > 0){
                            $data3 = $result3->as_array();
                        }else{
                            $data3 = false;
                        }
                        if($data3){
                            $a = constant("ALKUAIKA_Sunnuntai") + $data3[0]['alku'] - 1;
                            $b = constant("ALKUAIKA_Sunnuntai") + $data3[0]['loppu'] - 1;
                            $korvaaja2 = "(Su) ".$a." - ".$b." ".$this->utf8($data3[0]['nimi']);
                        }else{
                            $korvaaja2 = "-";
                        }
                    }else{
                        $korvaaja2 = "-";
                    }
                    $text = str_replace($match,$korvaaja2,$text);
                }else{//tagi typotettu eli tagia ei löydy.
                    $korvaaja2 = "-";
                    $text = str_replace($match,$korvaaja2,$text);
                }

            }

        }


        return array($text,$spin);
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