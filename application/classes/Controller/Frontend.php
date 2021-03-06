<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Frontend extends Controller {


    //vastaa constructoria, mutta sitä ei tarvitse overloadata.
    public function before(){
    	$db = Database::instance();
    	$this->session = Session::instance();
    	//$halp = new Halp();
    	$this->view = new View('start');
        $this->view->js  = "\n<script type=\"text/javascript\" src=\"".URL::site('/')."jquery/jquery-2.1.1.min.js\"></script>";
        // $this->view->js  .= "\n<script type=\"text/javascript\" src=\"".URL::site('/')."jquery/jquery.mobile.custom.min.js\"></script>";
    	// $this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::site('/')."jquery/jquery-ui.min.js\"></script>";
        // $this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::site('/')."jquery/jquery.fullscreenr.js\"></script>";
        // $this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::site('/')."flowplayer/flowplayer-3.2.6.min.js\"></script>";
        // $this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::site('/')."jquery/kinetic-v3.10.1.min.js\"></script>";
        // $this->view->js .= "\n<script src=\"http://yui.yahooapis.com/3.4.0/build/yui/yui-min.js\"></script>"; //tätä ei toistaiseksi käytetä.
    	if(!defined("__tableprefix")){
            $tb = DB::query(Database::SELECT,"SELECT value FROM config WHERE opt = 'tableprefix'")->execute(__db)->get('value',date('Y'));
            if($tb == 0){
                $tb = "dev";
            }
            define("__tableprefix",$tb);
        }
        $this->view->css  = HTML::style("css/".__tableprefix."-tv.css");
        // $this->view->css .= HTML::style("css/hide_cursor.css");
    }

    public function action_to_tv(){
        $this->redirect("tv",302);
    }

	public function action_index()
	{
    	$this->view->js .= '
    	<script type="text/javascript">
    	var baseurl = \''.URL::site('/').'\';
        </script>
    	';
    	$this->view->js .= "\n<script type=\"text/javascript\" src=\"".URL::site('/')."js/pages/frontend.js\"></script>";
    	$this->view->text = "<span style=\"font-size:80px\">Tervetuloa seuraamaan Tracon 9:n inforuutua.</span><br><br>Odota hetki, synkronoidutaan inforuutujärjestelmään.";
		$this->response->body($this->view->render());
	}

} // End Frontend

?>
