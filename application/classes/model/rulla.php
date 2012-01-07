<?php defined('SYSPATH') or die('No direct script access.');

class Model_Rulla extends Jelly_Model
{
    public static function initialize(Jelly_Meta $meta)
    {
        // An optional database group you want to use
        $meta->db(__db);

        // The table the model is attached to
        // It defaults to the name of the model pluralized
        $meta->table('rulla');

        // Fields defined by the model
        $meta->fields(array(
            'rul_id'   => Jelly::field('primary',array(
                            "column" => "rul_id",
                            "type"   => "bigint unsigned",
                            "other"  => "PRIMARY KEY AUTO_INCREMENT"
                        )),
            'pos'      => Jelly::field('integer',array(
                            "column" => "pos",
                            "type"   => "int",
                            "other"  => ""
                        )),
            'type'     => Jelly::field('integer',array(
                            "column" => "type",
                            "type"   => "int",
                            "other"  => ""
                        )),
            'time'     => Jelly::field('integer',array(
                            "column" => "time",
                            "type"   => "int",
                            "other"  => ""
                        )),
            'selector' => Jelly::field('integer',array(
                            "column" => "selector",
                            "type"   => "int",
                            "other"  => ""
                        )),
            'hidden'   => Jelly::field('boolean',array(
                            "column" => "hidden",
                            "type"   => "tinyint",
                            "other"  => ""
                        ))
        ));

        $check = new Model_Jelly_Check();
        $test = $check->checks($meta);
    }
}

?>