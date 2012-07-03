<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Frontend extends Controller {


    //vastaa constructoria, mutta sitä ei tarvitse overloadata.
    public function before(){
    	$db = Database::instance();
    	$this->session = Session::instance();
    	//$halp = new Halp();
    	$this->view = new View('start');
        $this->view->js  = "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."jquery/jquery-1.7.min.js\"></script>";
    	$this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."jquery/jquery-ui-1.8.16.custom.min.js\"></script>";
        $this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."jquery/jquery.validate.js\"></script>";
        $this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."jquery/jquery.metadata.js\"></script>";
        $this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."jquery/jquery.framerate.js\"></script>";
        $this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."flowplayer/flowplayer-3.2.6.min.js\"></script>";
        $this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."js/widget.js\"></script>";
        $this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."jquery/kinetic-v3.10.1.min.js\"></script>";
        //$this->view->js .= "\n<script src=\"http://yui.yahooapis.com/3.4.0/build/yui/yui-min.js\"></script>"; //tätä ei toistaiseksi käytetä.
    	if(!defined("__tableprefix")){
            $tb = DB::query(Database::SELECT,"SELECT value FROM config WHERE opt = 'tableprefix'")->execute(__db)->get('value',date('Y'));
            if($tb == 0){
                $tb = "dev";
            }
            define("__tableprefix",$tb);
        }
        $this->view->css = html::style("css/".__tableprefix."-tv.css");
    }

    public function action_to_tv(){
        $this->request->redirect("tv");
    }

	public function action_index()
	{
    	$this->view->js .= '
    	<script type="text/javascript">
    	var baseurl = \''.URL::base($this->request).'\';
        </script>
    	';
    	$this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::base($this->request)."js/pages/frontend.js\"></script>";
    	$this->view->text = "Tervetuloa seuraamaan Tracon VI:n inforuutua.<br><br>Odota hetki, synkronoidutaan inforuutujärjestelmään.";
		$this->response->body($this->view->render());
	}

} // End Frontend

?>