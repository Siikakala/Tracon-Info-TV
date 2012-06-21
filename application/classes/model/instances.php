<?php defined('SYSPATH') or die('No direct script access.');

class Model_Instances extends Jelly_Model
{
    public static function initialize(Jelly_Meta $meta)
    {
        // An optional database group you want to use
        $meta->db(__db);

        // The table the model is attached to
        // It defaults to the name of the model pluralized
        $meta->table(__tableprefix.'instances');

        // Fields defined by the model
        $meta->fields(array(
            'inst_id'   => Jelly::field('primary',array(
                            "column" => "inst_id",
                            "type"   => "bigint unsigned",
                            "other"  => "PRIMARY KEY AUTO_INCREMENT"
                        )),
            'nimi'      => Jelly::field('string',array(
                            "column" => "nimi",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'selite'    => Jelly::field('string',array(
                            "column" => "selite",
                            "type"   => "text",
                            "other"  => ""
                        )),
        ));

        $check = new Model_Jelly_Check();
        $test = $check->checks($meta);
    }
}

?>