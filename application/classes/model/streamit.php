<?php defined('SYSPATH') or die('No direct script access.');

class Model_Streamit extends Jelly_Model
{
    public static function initialize(Jelly_Meta $meta)
    {
        // An optional database group you want to use
        $meta->db(__db);

        // The table the model is attached to
        // It defaults to the name of the model pluralized
        $meta->table('streamit');

        // Fields defined by the model
        $meta->fields(array(
            'stream_id' => Jelly::field('primary',array(
                            "column" => "stream_id",
                            "type"   => "bigint unsigned",
                            "other"  => "PRIMARY KEY AUTO_INCREMENT"
                        )),
            'tunniste'  => Jelly::field('string',array(
                            "column" => "tunniste",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
            'url'       => Jelly::field('string',array(
                            "column" => "url",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
            'selite'    => Jelly::field('string',array(
                            "column" => "selite",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
            'jarjestys' => Jelly::field('integer',array(
                            "column" => "jarjestys",
                            "type"   => "int",
                            "other"  => ""
                        ))
        ));

        $check = new Model_Jelly_Check();
        $test = $check->checks($meta);
    }
}

?>