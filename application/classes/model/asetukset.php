<?php defined('SYSPATH') or die('No direct script access.');

class Model_Asetukset extends Jelly_Model
{
    public static function initialize(Jelly_Meta $meta)
    {
        // An optional database group you want to use
        $meta->db(__db);

        // The table the model is attached to
        // It defaults to the name of the model pluralized
        $meta->table('asetukset');

        // Fields defined by the model
        $meta->fields(array(
            'id'        => Jelly::field('primary',array(
                            "column" => "id",
                            "type"   => "bigint",
                            "other"  => "PRIMARY KEY AUTO_INCREMENT"
                        )),
            'prefix'    => Jelly::field('string',array(
                            "column" => "prefix",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
            'tapahtuma' => Jelly::field('string',array(
                            "column" => "tapahtuma",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
        ));
        $check = new Model_Jelly_Check();
        $test = $check->checks($meta);
    }
}

?>