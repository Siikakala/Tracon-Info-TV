<?php defined('SYSPATH') or die('No direct script access.');

class Model_Leap_User extends DB_ORM_Model {

   public function __construct() {
       parent::__construct();
       $this->fields = array(
           'u_id' => new DB_ORM_Field_Integer($this, array(
               'max_length' => 10,
               'nullable'   => FALSE,
               'unsigned'   => TRUE
           )),
           'kayttis' => new DB_ORM_Field_String($this, array(
               'max_length' => 50,
               'nullable'   => FALSE,
           )),
           'passu' => new DB_ORM_Field_String($this, array(
               'max_length' => 32,
               'nullable'   => FALSE,
           )),
           'level' => new DB_ORM_Field_Integer($this, array(
               'max_length' => 11,
               'nullable'   => FALSE,
           )),
       );
       $this->relations = array();
   }

   public static function data_source() {
       return __db;
   }

   public static function table() {
       return 'kayttajat';
   }

   public static function primary_key() {
       return array('u_id');
   }

}
?>