<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Model_Pages_
 *
 * @package Höylä
 * @author Miika Ojamo aka. Siikakala
 * @copyright Copyright (c) 2012
 * @version $Id$
 * @access public
 */
class Model_Pages_ extends Model_Database {

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

    }

    /**
     * Model_Pages_::page() Returns pages initial code, rendered from view.
     *
     * @param boolean $htmlonly (default:false) return array of html, if only html should be returned without rendered to view.
     * @param boolean $dataonly (default:false) return multi-dimensional array of data, without any html
     * @return string or array of the data.
     */
    public function page($htmlonly = false, $dataonly = false){
        $htmlonly = (bool)$htmlonly;
        $dataonly = (bool)$dataonly;
        if($htmlonly === true && $dataonly === true){
            throw Kohana_Exception("htmlonly and dataonly can't be both true. Choose only one of them.");
        }

    }


    /**
     * Model_Pages_::ajax()
     *
     * @param mixed $subrequest What ajax subrequest is called
     * @param array $attributes Attributes of the call
     * @return array ready to be json-encoded.
     */
    public function ajax($subrequest = false, $attributes = array()){

    }

    /**
     * Model_Pages_::tbi() To Be Included. What else should be included, and where?
     *
     * @return array of data, what should be included.
     */
    public function tbi(){

    }
}


?>
