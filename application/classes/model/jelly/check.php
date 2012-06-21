<?php defined('SYSPATH') or die('No direct script access.');

class Model_Jelly_Check extends Jelly_Meta
{

    function checks(Jelly_Meta $meta){
        $test1 = $this->check_table($meta);
        if($test1 || strcmp($test1,"fixed") === 0){
            $test2 = $this->check_desc($meta);
        }
    }

    function check_table(Jelly_Meta $meta){
        $table = $meta->table();
        $db = Database::instance(__db);
        $c = @$db->list_tables($table);
        if(count($c) == 0 || $c == false){
            $fields = $meta->fields();
            $sql = "CREATE TABLE ".$table." (";
            $parts = array();
            foreach($fields as $field=>$o){
                $parts[] = "`".$o->column."` ".$o->type." NOT NULL ".$o->other;
            }
            $sql2 = implode(", ",$parts);
            $sql .= $sql2.")";
            $query = DB::query(Database::UPDATE,$sql)->execute(__db);
            $return = "fixed";
        }else{
            $return = true;
        }
        return $return;
    }

    function check_desc(Jelly_Meta $meta){
        $table = $meta->table();
        $db = Database::instance(__db);
        $cols = $db->list_columns($table);
        $fields = $meta->fields();
        $checked = array();
        $types = array();
        $drop = array_diff_key($cols,$fields);
        $failed = false;
        foreach($fields as $key=>$value){
            $checked[$key] = array_key_exists($value->column,$cols);
            if($checked[$key] === false){
                $failed = true;
                $types[$key] = "ADD";
            }elseif(strcmp($cols[$key]["data_type"],$value->type) !== 0){
                $failed = true;
                $types[$key] = "MODIFY";
                $checked[$key] = false;
            }elseif(count($drop) > 0){
                $failed = true;
            }
        }
        $fails = array();
        if($failed){
            $fails = array_keys($checked,false);
            $sql = "ALTER TABLE ".$table." ";
            $parts = array();
            foreach($fails as $key=>$field){
                $parts[] = $types[$field]." `".$fields[$field]->column."` ".$fields[$field]->type." NOT NULL ".$fields[$field]->other;
            }
            foreach($drop as $field=>$value){
                $parts[] = "DROP ".$field;
            }
            $sql2 = implode(", ",$parts);
            $sql .= $sql2;
            $query = DB::query(Database::UPDATE,$sql)->execute(__db);
            $return = "fixed";
        }else{
            $return = true;
        }
        return $return;
    }

}
?>