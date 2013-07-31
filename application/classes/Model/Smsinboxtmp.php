<?php defined('SYSPATH') or die('No direct script access.');

class Model_Smsinboxtmp extends Jelly_Model
{
    public static function initialize(Jelly_Meta $meta)
    {
        // An optional database group you want to use
        $meta->db(__db);

        // The table the model is attached to
        // It defaults to the name of the model pluralized
        $meta->table('sms_tmp_inbox');

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
            'from'      => Jelly::field('text',array(
                            "column" => "from",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'text'      => Jelly::field('text',array(
                            "column" => "text",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'stamp'     => Jelly::field('timestamp',array(
                            "column" => "stamp",
                            "type"   => "timestamp",
                            "format" => "Y-m-d H:i:s",
                            "other"  => "DEFAULT 0"
                        )),
            'concatref'=> Jelly::field('text',array(
                            "column" => "concatref",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'concattotal'=> Jelly::field('text',array(
                            "column" => "concattotal",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'concatpart'=> Jelly::field('text',array(
                            "column" => "concatpart",
                            "type"   => "text",
                            "other"  => ""
                        )),
        ));
        $check = new Model_Jelly_Check();
        $test = $check->checks($meta);
    }
}

?>