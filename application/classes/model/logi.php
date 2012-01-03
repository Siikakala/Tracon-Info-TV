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
            'id'       => Jelly::field('primary'),
            'tag'      => Jelly::field('string'),
            'comment'  => Jelly::field('text'),
            'adder'    => Jelly::field('string'),
            'stamp'    => Jelly::field('timestamp')
        ));
    }
}

?>