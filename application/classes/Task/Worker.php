<?php defined('SYSPATH') or die('No direct script access.');

class Task_Worker extends Minion_Task {

	protected function _execute(array $params){
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