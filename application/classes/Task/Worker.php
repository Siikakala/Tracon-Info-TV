<?php defined('SYSPATH') or die('No direct script access.');

class Task_Worker extends Minion_Task {

	protected function _execute(array $params){
		$worker = new GearmanWorker();
		$worker->addServer();
		$worker->addFunction('process_smsoutbox',array($this, 'sms_outbox'));
		while ($worker->work()){
			if($worker->returnCode() != GEARMAN_SUCCESS){
				echo "return code was " . $worker->returnCode() . ". Exiting..\n";
				break;
			}
			usleep(20000);//20ms
		}
	}

	//Statuscodes: 1 = Added to queue, 2 = Sent, 3 = Delivered, 1xx = Error in sending (where xx is code what nexmo returned or 99 for unknown), 200 = Error in delivery, 300 = Expired message.

	function sms_outbox($job){
		set_time_limit(0);
		$log = Log::instance();
		$log->add(Log::INFO,"Starting SMS sending process.");
		$nexmo = new Nexmo_Message();
		//$from = Konana::$config->load('auth.nexmo.number');
		$from = '+3584573950776'; //for now, have to fix this before production.
		do{
			$data = Jelly::query('smsoutbox')->where('processed','=','0')->select();
			foreach($data as $row){
				$log->add(Log::INFO,"* Sending message to +:to. Text: >>:text<<, identifier :id", array(":to" => $row->to, ":text" => $row->text, ":id" => $row->id));
				$response = $nexmo->sendText("+".$row->to,$from,$row->text,$row->id);
				$parts = $response->messagecount;
				$log->add(Log::INFO,"** Message sent in :parts parts.",array(":parts" => $parts));
				if(is_array($response->messages)){
					foreach($response->messages as $msg){
						$log->add(Log::INFO,"*** Part status: :status",array(":status" => $msg->status));
						if($msg->status == 0){
							//Everything went well.
							$row->messageId = $msg->messageid;
							$row->status = "Sent";
							$row->d_stamp = DB::expr('NOW()');
							$row->processed = 1;
							$row->statuscode = 2;
							$row->save();
						}elseif($msg->status == 1){
							//Throttled.
							$log->add(Log::INFO,"** Message throttled, not processed.");
							sleep(1);
						}else{
							if(isset($msg->errortext)){
								$log->add(Log::INFO,"** Message failure reason :reason.",array(":reason" => $msg->errortext));
								$row->status = "Sending FAILED! Reason: " . $msg->errortext;
								$row->statuscode = "1".(substr("00".$msg->status,-2)); //so nexmo status 3 will convert to to 103.
							}else{
								$log->add(Log::INFO,"** Message failure reason unknown.");
								$row->status = "Sending FAILED! Reason: Unknown.";
								$row->statuscode = "199";
							}
							$row->d_stamp = DB::expr('NOW()');
							$row->processed = 1;
							$row->save();
						}
						usleep(200000); //200ms
					}
				}
				$log->write();
			}
		}while(Jelly::query('smsoutbox')->where('processed','=','0')->count() > 0);
		unset($data);
		$log->add(Log::INFO,"SMS sending process completed. Waiting for next round.");
		sleep(3);
		$log->write();
		return true;
	}

}
?>
