<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Authaus-modeli Aniki-authausta varten.
 *
 * @author Miika Ojamo <miika@vkoski.net>
 *
 */
class Model_Authi extends Model_Database{

    /**
     * Authaus anikin foorumeiden käyttäjätietokantaa vasten.
     * @param string $user Käyttäjätunnus
     * @param string $pass Salasana
     */
    public function auth($user,$pass,$ip){
        $secret = Kohana::$config->load('auth.secret');
        $l = Jelly::query('user')->where('kayttis','=',$user)->and_where('passu','=',sha1($pass.$secret))->limit(1)->select();

        if($l->loaded()){
            $tulos = true;
            $level = $l->level;
            $l->last_login = DB::expr("NOW()");
            $l->ip = $ip;
            $l->save();
        }else
            $tulos = false;

        if($tulos)
            $return = $level;
        else
            $return = false;

        return $return;
    }
}
?>