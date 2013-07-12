<?php defined('SYSPATH') or die('No direct script access.');

class Model_Tuotanto extends Jelly_Model
{
    public static function initialize(Jelly_Meta $meta)
    {
        // An optional database group you want to use
        $meta->db(__db);

        // The table the model is attached to
        // It defaults to the name of the model pluralized
        $meta->table(__tableprefix.'tuotanto');

        // Fields defined by the model
        $meta->fields(array(
            'id'        => Jelly::field('primary',array(
                            "column" => "id",
                            "type"   => "bigint",
                            "other"  => "PRIMARY KEY AUTO_INCREMENT"
                        )),
            'start'     => Jelly::field('timestamp',array(
                            "column" => "start",
                            "type"   => "timestamp",
                            "format" => "Y-m-d H:i:s",
                            "other"  => "DEFAULT 0"
                        )),
            'length'    => Jelly::field('integer',array(
                            "column" => "length",
                            "type"   => "int",
                            "other"  => ""
                        )),
            'priority'  => Jelly::field('string',array(
                            "column" => "priority",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
            'category'  => Jelly::field('string',array(
                            "column" => "category",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'type'  => Jelly::field('string',array(
                            "column" => "type",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'event'     => Jelly::field('text',array(
                            "column" => "event",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'notes'     => Jelly::field('text',array(
                            "column" => "notes",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'vastuu'    => Jelly::field('string',array(
                            "column" => "vastuu",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
            'duunarit'  => Jelly::field('string',array(
                            "column" => "duunarit",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
        ));
        $check = new Model_Jelly_Check();
        $test = $check->checks($meta);
    }
}

?>