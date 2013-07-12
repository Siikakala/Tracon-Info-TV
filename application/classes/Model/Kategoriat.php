<?php defined('SYSPATH') or die('No direct script access.');

class Model_Kategoriat extends Jelly_Model
{
    public static function initialize(Jelly_Meta $meta)
    {
        // An optional database group you want to use
        $meta->db(__db);

        // The table the model is attached to
        // It defaults to the name of the model pluralized
        $meta->table(__tableprefix.'kategoriat');

        // Fields defined by the model
        $meta->fields(array(
            'id'        => Jelly::field('primary',array(
                            "column" => "id",
                            "type"   => "bigint",
                            "other"  => "PRIMARY KEY AUTO_INCREMENT"
                        )),
            'tunniste'  => Jelly::field('string',array(
                            "column" => "tunniste",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'nimi'      => Jelly::field('text',array(
                            "column" => "nimi",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'vari'      => Jelly::field('text',array(
                            "column" => "vari",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'fontti'    => Jelly::field('text',array(
                            "column" => "fontti",
                            "type"   => "text",
                            "other"  => ""
                        ))
        ));
        $check = new Model_Jelly_Check();
        $test = $check->checks($meta);
    }
}

?>