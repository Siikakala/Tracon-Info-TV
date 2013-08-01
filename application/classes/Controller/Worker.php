<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Worker extends Controller {

	function action_worker(){
		$worker = new GearmanWorker();
		$worker->addServer();
		$worker->addFunction('process_smsoutbox','sms_outbox');
		while ($worker->work()){
			if($worker->returnCode() != GEARMAN_SUCCESS){
				echo "return code was " . $worker->returnCode() . ". Exiting..\n";
				break;
			}
		}
	}

	function sms_outbox($job){

	}

?>