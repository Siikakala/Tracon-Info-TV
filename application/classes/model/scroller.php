<?php defined('SYSPATH') or die('No direct script access.');

class Model_Scroller extends Jelly_Model
{
    public static function initialize(Jelly_Meta $meta)
    {
        // An optional database group you want to use
        $meta->db(__db);

        // The table the model is attached to
        // It defaults to the name of the model pluralized
        $meta->table(__tableprefix.'scroller');

        // Fields defined by the model
        $meta->fields(array(
            'scroll_id' => Jelly::field('primary',array(
                            "column" => "scroll_id",
                            "type"   => "bigint unsigned",
                            "other"  => "PRIMARY KEY AUTO_INCREMENT"
                        )),
            'pos'       => Jelly::field('integer',array(
                            "column" => "pos",
                            "type"   => "int",
                            "other"  => ""
                        )),
            'text'      => Jelly::field('string',array(
                            "column" => "text",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
            'style'     => Jelly::field('string',array(
                            "column" => "style",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
            'set'       => Jelly::field('timestamp',array(
                            "column" => "set",
                            "type"   => "timestamp",
                            "format" => "Y-m-d H:i:s",
                            "other"  => "ON UPDATE CURRENT_TIMESTAMP"
                        )),
            'hidden'    => Jelly::field('boolean',array(
                            "column" => "hidden",
                            "type"   => "tinyint",
                            "other"  => ""
                        )),
            'instance'  => Jelly::field('integer',array(
                            "column" => "instance",
                            "type"   => "int",
                            "other"  => "DEFAULT 1"
                        )),
        ));

        $check = new Model_Jelly_Check();
        $test = $check->checks($meta);
    }
}

?>