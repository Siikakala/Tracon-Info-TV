<?php defined('SYSPATH') or die('No direct script access.');

class Model_User extends Jelly_Model
{
    public static function initialize(Jelly_Meta $meta)
    {
        // An optional database group you want to use
        $meta->db(__db);

        // The table the model is attached to
        // It defaults to the name of the model pluralized
        $meta->table('kayttajat');

        // Fields defined by the model
        $meta->fields(array(
            'u_id'      => Jelly::field('primary'),
            'kayttis'   => Jelly::field('string'),
            'passu'     => Jelly::field('string'),
            'level'     => Jelly::field('integer')
        ));
    }
}

?>