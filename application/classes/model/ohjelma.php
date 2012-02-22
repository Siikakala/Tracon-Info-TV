<?php defined('SYSPATH') or die('No direct script access.');

class Model_Ohjelma extends Jelly_Model
{
    public static function initialize(Jelly_Meta $meta)
    {
        // An optional database group you want to use
        $meta->db(__db);

        // The table the model is attached to
        // It defaults to the name of the model pluralized
        $meta->table('ohjelma');

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
            'kesto'     => Jelly::field('integer',array(
                            "column" => "kesto",
                            "type"   => "int",
                            "other"  => ""
                        )),
            'sali'      => Jelly::field('string',array(
                            "column" => "sali",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
            'otsikko'   => Jelly::field('string',array(
                            "column" => "otsikko",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'kuvaus'    => Jelly::field('text',array(
                            "column" => "kuvaus",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'pitaja'    => Jelly::field('string',array(
                            "column" => "pitaja",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
            'kategoria' => Jelly::field('string',array(
                            "column" => "kategoria",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
            'hidden'    => Jelly::field('boolean',array(
                            "column" => "hidden",
                            "type"   => "tinyint",
                            "other"  => "DEFAULT 0"
                        )),
        ));
        $check = new Model_Jelly_Check();
        $test = $check->checks($meta);
    }
}

?>