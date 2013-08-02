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
		}
	}

	function sms_outbox($job){
		$nexmo = new Nexmo_Message();
		$from = Konana::$config->load('auth.nexmo.number');
		do{
			$data = Jelly::query('smsoutbox')->where('processed','=','0')->select();
			foreach($data as $row){
				$response = $nexmo->sendText("+".$row->to,$from,$row->text,$row->id);
				$parts = $response->messagecount;
				if(is_array($response->messages)){
					foreach($response->messages as $msg){
						if($msg->status === 0){
							//Everything went well.
							$row->messageId = $msg->messageid;
							$row->status = "Sent";
							$row->d_stamp = DB::expr('NOW()');
							$row->processed = 1;
							$row->save();
						}elseif($msg->status === 1){
							//Throttled.
							sleep(1);
						}else{
							$row->status = "Sending FAILED! Reason: " . $msg->errortext;
							$row->d_stamp = DB::expr('NOW()');
							$row->processed = 1;
							$row->save();
						}
						usleep(200000); //200ms
					}
				}
			}
		}while(Jelly::query('smsoutbox')->where('processed','=','0')->count() == 0);
	}

}
?>
