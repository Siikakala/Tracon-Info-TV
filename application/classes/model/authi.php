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
    public function auth($user,$pass){
        $query = DB::query(Database::SELECT,
                            "SELECT level ".
                            "FROM   kayttajat ".
                            "WHERE  kayttis = :user ".
                            "       AND ".
                            "       passu = :pass"
                            );
        $query->parameters(array(":user" => strtolower($user),
                                 ":pass" => sha1($pass)
                                ));
        $result = $query->execute(__db);
        if($result->count() > 0)
            $tulos = $result->as_array();
        else
            $tulos = false;

        if($tulos)
            $return = $result[0]["level"];
        else
            $return = false;

        return $return;
    }
}
?>