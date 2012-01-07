<?php defined('SYSPATH') or die('No direct script access.');

class Model_Diat extends Jelly_Model
{
    public static function initialize(Jelly_Meta $meta)
    {
        // An optional database group you want to use
        $meta->db(__db);

        // The table the model is attached to
        // It defaults to the name of the model pluralized
        $meta->table('diat');

        // Fields defined by the model
        $meta->fields(array(
            'dia_id'    => Jelly::field('primary',array(
                            "column" => "dia_id",
                            "type"   => "bigint unsigned",
                            "other"  => "PRIMARY KEY AUTO_INCREMENT"
                        )),
            'tunniste'  => Jelly::field('string',array(
                            "column" => "tunniste",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
            'data'      => Jelly::field('text',array(
                            "column" => "data",
                            "type"   => "text",
                            "other"  => ""
                        )),
            'jarjestys' => Jelly::field('integer',array(
                            "column" => "jarjestys",
                            "type"   => "smallint",
                            "other"  => ""
                        )),
            'hidden'    => Jelly::field('boolean',array(
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