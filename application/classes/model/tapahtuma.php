<?php defined('SYSPATH') or die('No direct script access.');

class Model_Tapahtuma extends Jelly_Model
{
    public static function initialize(Jelly_Meta $meta)
    {
        // An optional database group you want to use
        $meta->db(__db);

        // The table the model is attached to
        // It defaults to the name of the model pluralized
        $meta->table(__tableprefix.'tapahtumaconfig');

        // Fields defined by the model
        $meta->fields(array(
            'id'        => Jelly::field('primary',array(
                            "column" => "id",
                            "type"   => "bigint",
                            "other"  => "PRIMARY KEY AUTO_INCREMENT"
                        )),
            'alkuaika'  => Jelly::field('timestamp',array(
                            "column" => "alkuaika",
                            "type"   => "timestamp",
                            "format" => "Y-m-d H:i:s",
                            "other"  => "DEFAULT 0"
                        )),
            'loppuaika' => Jelly::field('timestamp',array(
                            "column" => "loppuaika",
                            "type"   => "timestamp",
                            "format" => "Y-m-d H:i:s",
                            "other"  => "DEFAULT 0"
                        )),
            'nimi'      => Jelly::field('string',array(
                            "column" => "nimi",
                            "type"   => "text",
                            "other"  => ""
                        ))
        ));
        $check = new Model_Jelly_Check();
        $test = $check->checks($meta);
    }
}

?>