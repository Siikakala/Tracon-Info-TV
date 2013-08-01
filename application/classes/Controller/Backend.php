<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Backend extends Controller {

    //vastaa constructoria, mutta sitä ei tarvitse overloadata.
    public function before(){
    	$db = Database::instance();
    	$this->session = Session::instance();
    }

    public function action_kill(){//jos tulee tarve tappaa sessio
        session_destroy();
    }

    public function action_fail(){
        throw new Kohana_Exception('Testierrori');
    }

    public function action_process_nexmo(){
        
        $which = $this->request->param("type");

        if($which == "inbound"){
            //Inbound message
            $nexmo = new Nexmo_Message();
            if ($nexmo->inboundText()) {
                //we got message.
                if ($nexmo->concat) {
                    $data = array('from' => $nexmo->from, 'messageId' => $nexmo->message_id, 'text' => $nexmo->text, 'stamp' => $nexmo->timestamp, 'concatref' => $nexmo->concatref, 'concattotal' => $nexmo->concattotal, 'concatpart' => $nexmo->concatpart);
                    Jelly::factory('smsinboxtmp')->set($data)->save();
                    $this->check_concat_messages($nexmo->concatref);
                }else{
                    $data = array('from' => $nexmo->from, 'messageId' => $nexmo->message_id, 'text' => $nexmo->text, 'stamp' => $nexmo->timestamp);
                    Jelly::factory('smsinbox')->set($data)->save();
                }
                print "200 OK";
            }
        }elseif ($which == 'delivery') {
            //Delivery report
            $nexmo = new Nexmo_Receipt();
            if ($nexmo->exists()) {
                switch ($nexmo->status) {
                    case $nexmo::STATUS_DELIVERED:
                        //sukset
                        $d = Jelly::query('smsoutbox',$nexmo->clientref)->select();
                        $d->status = "Delivered";
                        $d->d_timestamp = DB::expr('NOW()');
                        $d->save();
                        print "200 OK";
                        break;
                    case $nexmo::STATUS_FAILED:
                        $d = Jelly::query('smsoutbox',$nexmo->clientref)->select();
                        $d->status = "FAILED!";
                        $d->d_timestamp = DB::expr('NOW()');
                        $d->save();
                        print "200 OK";
                        break;
                    case $nexmo::STATUS_EXPIRED:
                        $d = Jelly::query('smsoutbox',$nexmo->clientref)->select();
                        $d->status = "Expired";
                        $d->d_timestamp = DB::expr('NOW()');
                        $d->save();
                        print "200 OK";
                        break;
                    case $nexmo::STATUS_BUFFERED:
                        $d = Jelly::query('smsoutbox',$nexmo->clientref)->select();
                        $d->status = "Waiting for delivery...";
                        $d->d_timestamp = DB::expr('NOW()');
                        $d->save();
                        print "200 OK";
                        break;
                    default:
                        # code...
                        break;
                }
            }
        }
    }

    public function check_concat_messages($ref_id = null){
        if(empty($ref_id)){
            $datas = Jelly::query('smsinboxtmp')->select_column(DB::expr('DISTINCT `concatref`'))->select();
            foreach($datas as $row){
                $this->check_concat_messages($row->concatref);
            }
        }else{
            $count = Jelly::query('smsinboxtmp')->where('concatref','=',$ref_id)->count();
            $data_one = Jelly::query('smsinboxtmp')->where('concatref','=',$ref_id)->limit(1)->select();
            if($data_one->concattotal <= $count){
                $data = Jelly::query('smsinboxtmp')->select_column(array(DB::expr('DISTINCT `messageId`'),'text'))->where('concatref','=',$ref_id)->order_by('concatpart')->select();
                $text = "";
                foreach ($data as $row) {
                    $text .= $row->text;
                }
                Jelly::factory('smsinbox')->set(array('from' => $data_one->from, 'messageId' => $data_one->messageId, 'text' => $text, 'stamp' => $data_one->stamp))->save();
                Jelly::query('smsinboxtmp')->where('concatref','=',$ref_id)->delete();
            }else{
                //print "meni elseen";
            }
        }
    }

    public function action_messages(){
        $ref_id = $this->request->param("param1");
        $this->check_concat_messages($ref_id);
        print "Reference id: $ref_id. SMS:t prosessoitu.";
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
