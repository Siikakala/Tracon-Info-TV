<?php defined('SYSPATH') or die('No direct script access.');

class Model_Logi extends Jelly_Model
{
    public static function initialize(Jelly_Meta $meta)
    {
        // An optional database group you want to use
        $meta->db(__db);

        // The table the model is attached to
        // It defaults to the name of the model pluralized
        $meta->table('logi');

        // Fields defined by the model
        $meta->fields(array(
            'id'        => Jelly::field('primary',array(
                            "column" => "id",
                            "type"   => "bigint",
                            "other"  => "PRIMARY KEY AUTO_INCREMENT"
                        )),
            'tag'       => Jelly::field('string',array(
                            "column" => "tag",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'comment'   => Jelly::field('text',array(
                            "column" => "comment",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'adder'     => Jelly::field('string',array(
                            "column" => "adder",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
            'stamp'     => Jelly::field('timestamp',array(
                            "column" => "stamp",
                            "type"   => "timestamp",
                            "format" => "Y-m-d H:i:s",
                            "other"  => "DEFAULT CURRENT_TIMESTAMP"
                        )),
            'ack'       => Jelly::field('string',array(
                            "column" => "ack",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),

            'ack_stamp' => Jelly::field('timestamp',array(
                            "column" => "ack_stamp",
                            "type"   => "timestamp",
                            "format" => "Y-m-d H:i:s",
                            "other"  => "DEFAULT 0"
                        )),
        ));
        $check = new Model_Jelly_Check();
        $test = $check->checks($meta);
    }
}

?>