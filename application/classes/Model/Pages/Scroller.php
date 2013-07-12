<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Model_Pages_Scroller
 *
 * @package Höylä
 * @author Miika Ojamo aka. Siikakala
 * @copyright Copyright (c) 2012
 * @version $Id$
 * @access public
 */
class Model_Pages_Scroller extends Model_Database {

     //Kind of constructer, what shuold be done before any other method is called.
     public function before(){
         parent::before();
         $session = Session::instance();
     }

    /**
    * Model_Pages_::access() This function is called, when right page has been determined. Checks for
    * user access rights for the model
    *
    * @return minimum access level
    */
    public function access(){

    }

    /**
    * Model_Pages_::css() Returns any custom css what is needed.
    *
    * @return string of custom css
    */
    public function css(){
        return "";
    }

    /**
     * Model_Pages_::page() Returns pages initial code, rendered from view.
     *
     * @param boolean $htmlonly (default:false) return array of html, if only html should be returned without rendered to view.
     * @param boolean $dataonly (default:false) return multi-dimensional array of data, without any html
     * @return string or array of the data.
     */
    public function page($htmlonly = false, $dataonly = false){
        $session = Session::instance();
        $htmlonly = (bool)$htmlonly;
        $dataonly = (bool)$dataonly;
        if($htmlonly === true && $dataonly === true){
            throw new Kohana_Exception("htmlonly and dataonly can't be both true. Choose only one of them.");
        }
        $return = "";

        $query = Jelly::query('scroller')->where('instance','=',$session->get('instance',1))->select();
        $instances = Controller_Admin::get_instances();
        if($query->count() > 0)
            $result = true;
        else
            $result = false;

        if($htmlonly == false && $dataonly == false){
            $view = new view('pages/scroller');
            $view->instances = Form::select('instance',$instances,$session->get('instance',1),array("onChange"=>"set_instance(this.value);window.setTimeout(function(){refresh_data();},200);"));
        }

        if($dataonly == true)
            $tablebody = array();
        else
            $tablebody = "";

        if($result) foreach($query as $data){
            if($dataonly == false){
                $tablebody .= "<tr class=\"".$data->scroll_id."\"><td>".Form::input('pos-'.$data->scroll_id,$data->pos,array("size"=>"1","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".Form::input('text-'.$data->scroll_id,$data->text,array("size"=>"45","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".Form::checkbox('hidden-'.$data->scroll_id,1,(boolean)$data->hidden,array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del ignore\" onclick=\"dele(".$data->scroll_id.")\" >X</a></td></tr>";
            }else{
                $tablebody[$data->scroll_id]["id"] = $data->scroll_id;
                $tablebody[$data->scroll_id]["pos"] = $data->pos;
                $tablebody[$data->scroll_id]["text"] = $data->text;
                $tablebody[$data->scroll_id]["hidden"] = (bool)$data->hidden;
            }
        }
        if($htmlonly == true || $dataonly == true){
            $return = $tablebody;
        }else{
            $view->tablebody = $tablebody;
            $return = $view->render();
        }

        return $return;
    }


    /**
     * Model_Pages_::ajax()
     *
     * @param mixed $subrequest What ajax subrequest is called
     * @param array $attributes Attributes of the call
     * @return array ready to be json-encoded.
     */
    public function ajax($subrequest = false, $attributes = array()){
        $param2 = $attributes;
        $session = Session::instance();
            switch($subrequest){
                case "save":
                    $post = $_POST;
                    $data = array();
                    $err = " ";
                    foreach($post as $key => $value){
                        $parts = explode("-",$key);
                        $rivi = $parts[1];
                        $data[$rivi][$parts[0]] = $value;//automagiikka <input name="data-id" value="value"> -> $data['id']['data'] = value
                    }
                    foreach($data as $row => $datat){
                        if(empty($datat["text"]) && empty($datat["pos"])){
                        }elseif(empty($datat["text"]) || empty($datat["pos"])){
                            $err .= "Jokin kenttä jäi täyttämättä. Kyseisen rivin tietoja <strong>EI</strong> ole tallennettu.";
                        }else{
                            if(!isset($datat["hidden"]))//checkkaamattomat checkboxit ei tuu mukaan ollenkaan.
                                $datat["hidden"] = false;
                            if($row >= 0 && $row < 500){//vanha rivi
                                $row = Jelly::query('scroller',$row)->select();
                                $row->pos      = $datat["pos"];
                                $row->text     = $datat["text"];
                                $row->hidden   = $datat["hidden"];
                                $row->instance = $session->get('instance',1);
                                $row->save();
                            }elseif($row >= 500){//uusi rivi
                                $datat["instance"] = $session->get('instance',1);
                                Jelly::factory('scroller')->set($datat)->save();
                            }
                        }
                    }
                    $return = array("ret"=>"Scroller päivitetty.$err Odota hetki, päivitetään listaus...");
                    break;
                case "load":
                	$query = Jelly::query('scroller')->where('instance','=',$session->get('instance',1))->order_by('pos','ASC')->select();
                    if($query->count() > 0)
                        $result = $query->as_array();
                    else
                        $result = false;
                    $text = Form::open(null, array("onsubmit" => "return false;", "id" => "form"));
                    $text .= "<table id=\"scroller\" class=\"stats\" style=\"border-right:0px; border-top:0px; border-bottom:0px;\"><thead><tr><th class=\"ui-state-default\">Kohta</th><th class=\"ui-state-default\">Teksti</th><th class=\"ui-state-default\">Piilotettu?</th></tr></thead><tbody>";

                    if($result) foreach($result as $row=>$data){
                        $text .= "<tr class=\"".$data["scroll_id"]."\"><td>".Form::input('pos-'.$data["scroll_id"],$data["pos"],array("size"=>"1","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".Form::input('text-'.$data["scroll_id"],$data["text"],array("size"=>"45","onkeypress"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td>".Form::checkbox('hidden-'.$data["scroll_id"],1,(boolean)$data["hidden"],array("onchange"=>"$(this).parent().parent().addClass(\"new\");"))."</td><td style=\"border:0px; border-bottom-style: none; padding: 0px; width:2px;\"><a href=\"javascript:;\" class=\"del ignore\" onclick=\"dele(".$data["scroll_id"].")\" >X</a></td></tr>";
                    }
                    $text .= "</tbody></table>".Form::close();
                    $return = array("data" => $text);
                    break;
                case "delete":
                    if(!$param2){
                        $ret = false;
                    }else{
                        Jelly::query('scroller',$param2)->select()->delete();
                        $ret = true;
                    }
                    $return = array("ret" => $ret);
                    break;
                case "page":
                    $return = array("ret" => true, "data" => $this->page(), "js" => $this->tbi());
                    break;
            }
        return $return;
    }

    /**
     * Model_Pages_::tbi() To Be Included. What else should be included, and where?
     *
     * @return array of data, what should be included.
     */
    public function tbi(){
        $return = "\n<script type=\"text/javascript\" src=\"".URL::site("/")."js/pages/scroller.js\"></script>";

        return $return;
    }
}


?>
