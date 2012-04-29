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
            $d = Jelly::query('frontends')->where('uuid','=',$session->get("uid",false))->limit(1)->select();
            $session->set("show_tv",$d->show_tv);
            $session->set("show_stream",$d->show_stream);
            $session->set("dia",$d->dia);
        }

        switch($session->get("show_tv",0)){
            case 1://stream
                $old = $session->get("old_stream",false);//onko streami vaihtunut?
                $stream = $session->get("show_stream");
                $session->set("old_stream",$stream);

                if($old == $stream){//nothing has changed.
                    $return = array("changed" => false);

                }else{//Siirrytään streamiin, tai vaihdetaan toiseen streamiin.
                    $d = Jelly::query('streamit',$stream)->select();
                    $return = array("changed" => true, "part" => "video", "palautus" => "<a id=\"player\" href=\"".$d->url."\" style=\"display:block;width:960px;height:490px;\"></a>", "video" => $d->url);
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
                        $d = Jelly::query('diat',$dia)->select();
                        if($d->count() > 0)
                            $result2 = true;
                        else
                            $result2 = false;

                        if($result2){
                            $return = array("changed" => true, "part" => "text", "palautus" => $this->utf8($d->data));
                        }else{
                            $return = array("changed" => false);
                        }
                    }else{
                        $return = array("changed" => true, "part" => "twitter", "palautus" => "");
                    }
                }
                break;
            default://diashow
                $session->set("old_stream",-1);
                $session->set("old_dia",-1);
                $max = Jelly::query('rulla')->where('hidden','=','0')->count();//montakos niitä näytettäviä dioja oli diashowssa..?
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
                        $y = Jelly::query('frontends')->where('uuid','=',$session->get("uid"))->limit(1)->select();
                        $y->dia = $new_page;
                        $y->save();
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
        $query1 = Jelly::query('rulla')->where('hidden','=','0')->select();
        if($query1->count() > 0)
            $result1 = $query1->as_array();
        else
            $result1 = false;

        $kohta = $session->get("page",0);
        switch($result1[$kohta]["type"]){
            case 1://dia
                $d = Jelly::query('diat',$result1[$kohta]["selector"])->select();
                if($d->count() > 0)
                    $result2 = true;
                else
                    $result2 = false;

                //allaoleva on aivan kaamea putkitus mutta: kannasta saatu data varmistetaan utf-8:ksi, jonka jälkeen parsitaan vielä ohjelmakarttatagit.
                $parsed = $this->parse_ohjelmatags($this->utf8($d->data));//[0] = parsittu teksti [1] = näytetäänkö progresspie
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
            Jelly::factory('frontends')
                    ->set(array(
                            "tunniste"    => "Undefined",
                            "uuid"        => $uuid,
                            "last_active" => DB::expr("NOW()"),
                            "show_tv"     => -1,
                            "show_stream" => -1,
                            "dia"         => -1,
                            "use_global"  => 1
                            ))->save();
        }elseif($session->get("uid",false) == false){//jos uuid-dataa ei ole sessiossa, mutta keksi löytyy.
            $session->set("uid",Cookie::get("uid"));
            $query = Jelly::query('frontends')->where('uuid','=',Cookie::get("uid"))->limit(1)->select();
            if(!$query->loaded()){//keksi elää pidempään kuin data kannassa.
                //eiolee.
                $uuid = Cookie::get("uid");
                Jelly::factory('frontends')
                        ->set(array(
                            "tunniste"    => "Undefined",
                            "uuid"        => $uuid,
                            "last_active" => DB::expr("NOW()"),
                            "show_tv"     => -1,
                            "show_stream" => -1,
                            "dia"         => -1,
                            "use_global"  => 1
                            ))->save();
                $session->set("global",1);
                $session->set("client","Undefined");
            }else{
                $session->set("global",$query->use_global);
                $session->set("client",$query->tunniste);
            }
        }else{//jos keksi ja sessiodata on kunnossa.
            $y = Jelly::query('frontends')->where('uuid','=',$session->get("uid"))->limit(1)->select();
            if(!$y->loaded()){
                Cookie::delete("uid");
            }else{
                $y->last_active = DB::expr("NOW()");
                $y->save();
            }
            $d = Jelly::query('frontends')->where('uuid','=',$session->get("uid"))->limit(1)->select();
            $session->set("global",$d->use_global);
            $session->set("client",$d->tunniste);
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
        if(!$session->get("override")){//jos edellisen päivityksen aikaleimaa ei löydy..
            $session->set("override",time());//asetetaan aikaleima,
            $override = true;//ja pakotetaan päivitys.
        }elseif(($session->get("override")+20)<time()){
            $override = true;
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
                $query2 = Jelly::query('scroller')->where('hidden','=','0')->select();
                if($query2->count() > 0)
                    foreach($query2 as $row){
                        $scrolli[] = $this->utf8($row->text);
                    }
                $scroll = implode(" &raquo; ",$scrolli);
                $session->set("override",time());
                $session->set("scrollstamp",time());//muistetaan vielä asettaa se päivitysaika sessiomuuttujaan.
            }//else = mikään ei muuttunut -> palauttaa falsen
        }else
            $scroll = "Info-TV";//jos scroller olisi muuten tyhjä, näytetään ainakin "Tracon Info-TV"

        if($scroll)
            $return = array("changed" => true,"palautus"=>$scroll);
        else
            $return = array("changed"=>false);

        return $return;
    }

     public function parse_ohjelmatags($text){
        $text = $this->utf8($text);
        $days = array(1=>"Maanantai",2=>"Tiistai",3=>"Keskiviikko",4=>"Torstai",5=>"Perjantai",6=>"Lauantai",7=>"Sunnuntai");
        $paiva = $days[date("N")]; //viikonpäivä
        $force_days = false;
        preg_match_all("/(\[.{1,20}\])/",$text,$matches);//etsi kaikki 1-20 merkin mittaiset [tagit]
        $spin = array(false);

        $korvaajat = array();
        $korvattavat = array();
        //print_r($matches);
        foreach($matches[1] as $key => $match){//käydään kaikki tagit läpi.
            if($match == "[aika]"){//tässä tagissa ei ole väliviivaa, joten erillisprosessointi.
                $a = date("H");
                $b = $a+1;
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

                //huomio allaolevasta: Mikäli jokin osa tagista typotettu, tai ko. salissa ei ole (enää) ohjelmaa, tagi korvataan viivalla (-). Koodi osaa katsoa seuraavien vuorokauden puolelle.
                if(strncasecmp($koska,"nyt",3) == 0){
                    $query = DB::query(Database::SELECT,//kaivetaan kannasta tällä hetkellä halutussa salissa pyörivä ohjelma.
                                        "SELECT  otsikko ".
                                        "       ,alkuaika ".
                                        "       ,kesto ".
                                        "FROM    ".__tableprefix."ohjelma ".
                                        "WHERE   sali = :sali ".
                                        "        AND ".
                                        "        UNIX_TIMESTAMP() BETWEEN UNIX_TIMESTAMP(alkuaika) AND (UNIX_TIMESTAMP(alkuaika)+(kesto*60)) ".
                                        "LIMIT   1"
                                        );
                    $query->parameters(array(":sali"  => $sali
                                            ));
                    $result = $query->execute(__db);
                    if($result->count() > 0){
                        $data = $result->as_array();
                    }else{
                        $data = false;
                    }
                    if($data){//ohjelmanumero löytyi / ohjelmaa on.
                        $korvaaja = $this->utf8($data[0]['otsikko']);
                        if($force_days)
                            $korvaaja = "(".substr($paiva,0,2).") ".$korvaaja;
                        $korvaaja = '<div id="'.$key.'" class="timer fill"></div>'.$korvaaja;
                        $start = strtotime($data[0]['alkuaika']);
                        $stop = $start + $data[0]['kesto'] * 60;
                        $len = $stop - $start;
                        $nyt = time();
                        $pos = $stop - $nyt;
                        $pros = ($pos/$len)*100;
                        if($pros > 100)
                            $pros = 100;
                        $spin[$key] = round($pros,1);

                    }else{//ohjelmaa ei ole.
                        $korvaaja = "-";
                    }
                    $text = str_replace($match,$korvaaja,$text);//korvataan kaikki kyseiset tagit.
                }elseif(strncasecmp($koska,"next",4) == 0){
                    $query2 = DB::query(Database::SELECT,//kaivetaan halutun salin seuraava ohjelmanumero.
                                        "SELECT   otsikko ".
                                        "        ,alkuaika ".
                                        "        ,kesto ".
                                        "FROM     ".__tableprefix."ohjelma ".
                                        "WHERE    sali = :sali ".
                                        "         AND ".
                                        "         alkuaika > NOW() ".
                                        "ORDER BY alkuaika ASC ".
                                        "LIMIT   1"
                                        );
                    $query2->parameters(array(":sali"  => $sali
                                             ));
                    $result2 = $query2->execute(__db);
                    if($result2->count() > 0){
                        $data2 = $result2->as_array();
                    }else{
                        $data2 = false;
                    }
                    if($data2){
                        $a = date("H:i",strtotime($data2[0]['alkuaika']));
                        $b = date("H:i",strtotime($data2[0]['alkuaika'])+$data2[0]['kesto']*60);
                        $korvaaja2 = $a." - ".$b." ".$this->utf8($data2[0]['otsikko']);
                        if(date("N") !== date("N",strtotime($data2[0]['alkuaika'])) || $force_days)
                            $korvaaja2 = "(".substr($days[date("N",strtotime($data2[0]['alkuaika']))],0,2).") ".$korvaaja2;
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