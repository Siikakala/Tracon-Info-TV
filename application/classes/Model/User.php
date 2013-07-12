<?php defined('SYSPATH') or die('No direct script access.');

class Model_User extends Jelly_Model
{
    public static function initialize(Jelly_Meta $meta)
    {
        // An optional database group you want to use
        $meta->db(__db);

        // The table the model is attached to
        // It defaults to the name of the model pluralized
        $meta->table('kayttajat');

        // Fields defined by the model
        $meta->fields(array(
            'u_id'       => Jelly::field('primary',array(
                            "column" => "u_id",
                            "type"   => "int unsigned",
                            "other"  => "PRIMARY KEY AUTO_INCREMENT"
                        )),
            'kayttis'    => Jelly::field('string',array(
                            "column" => "kayttis",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
            'passu'      => Jelly::field('string',array(
                            "column" => "passu",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
            'level'      => Jelly::field('integer',array(
                            "column" => "level",
                            "type"   => "int",
                            "other"  => ""
                        )),
            'last_login' => Jelly::field('timestamp',array(
                            "column" => "last_login",
                            "type"   => "timestamp",
                            "format" => "Y-m-d H:i:s",
                            "other"  => "DEFAULT 0"
                        )),
            'ip'        => Jelly::field('string',array(
                            "column" => "ip",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
        ));

        $check = new Model_Jelly_Check();
        $test = $check->checks($meta);
    }
}

?>