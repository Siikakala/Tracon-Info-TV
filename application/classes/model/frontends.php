<?php defined('SYSPATH') or die('No direct script access.');

class Model_Frontends extends Jelly_Model
{
    public static function initialize(Jelly_Meta $meta)
    {
        // An optional database group you want to use
        $meta->db(__db);

        // The table the model is attached to
        // It defaults to the name of the model pluralized
        $meta->table('frontends');

        // Fields defined by the model
        $meta->fields(array(
            'f_id'        => Jelly::field('primary',array(
                            "column" => "f_id",
                            "type"   => "bigint",
                            "other"  => "PRIMARY KEY AUTO_INCREMENT"
                        )),
            'tunniste'    => Jelly::field('string',array(
                            "column" => "tunniste",
                            "type"   => "tinytext",
                            "other"  => ""
                        )),
            'uuid'        => Jelly::field('string',array(
                            "column" => "uuid",
                            "type"   => "varchar",
                            "other"  => "UNIQUE"
                        )),
            'last_active' => Jelly::field('timestamp',array(
                            "column" => "last_active",
                            "type"   => "timestamp",
                            "format" => "Y-m-d H:i:s",
                            "other"  => ""
                        )),
            'show_tv'     => Jelly::field('integer',array(
                            "column" => "show_tv",
                            "type"   => "int",
                            "other"  => ""
                        )),
            'show_stream' => Jelly::field('integer',array(
                            "column" => "show_stream",
                            "type"   => "int",
                            "other"  => ""
                        )),
            'dia'         => Jelly::field('integer',array(
                            "column" => "dia",
                            "type"   => "int",
                            "other"  => ""
                        )),
            'use_global'  => Jelly::field('boolean',array(
                            "column" => "use_global",
                            "type"   => "tinyint",
                            "other"  => ""
                        )),
            'salt'        => Jelly::field('text',array(
                            "column" => "salt",
                            "type"   => "text",
                            "other"  => ""
                        ))
        ));

        $check = new Model_Jelly_Check();
        $test = $check->checks($meta);
    }
}

?>