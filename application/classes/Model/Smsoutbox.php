<?php defined('SYSPATH') or die('No direct script access.');

class Model_Smsoutbox extends Jelly_Model
{
    public static function initialize(Jelly_Meta $meta)
    {
        // An optional database group you want to use
        $meta->db(__db);

        // The table the model is attached to
        // It defaults to the name of the model pluralized
        $meta->table('sms_outbox');

        // Fields defined by the model
        $meta->fields(array(
            'id'        => Jelly::field('primary',array(
                            "column" => "id",
                            "type"   => "bigint",
                            "other"  => "PRIMARY KEY AUTO_INCREMENT"
                        )),
            'messageId' => Jelly::field('text',array(
                            "column" => "messageId",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'to'        => Jelly::field('text',array(
                            "column" => "to",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'text'      => Jelly::field('text',array(
                            "column" => "text",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'sender'    => Jelly::field('text',array(
                            "column" => "sender",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'processed' => Jelly::field('boolean',array(
                            "column" => "processed",
                            "type"   => "boolean",
                            "other"  => ""
                        )),
            'status'    => Jelly::field('string',array(
                            "column" => "status",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
            'statuscode'=> Jelly::field('integer',array(
                            "column" => "statuscode",
                            "type"   => "int",
                            "other"  => ""
                        )),
            'stamp' => Jelly::field('timestamp',array(
                            "column" => "stamp",
                            "type"   => "timestamp",
                            "format" => "Y-m-d H:i:s",
                            "other"  => "DEFAULT 0"
                        )),
            'd_stamp' => Jelly::field('timestamp',array(
                            "column" => "d_stamp",
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