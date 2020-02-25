<?php

class Db_object {
    

    static function find_all(){

        global $database;

        return static::find_by_query("SELECT * FROM " . static::$db_table . " ");


    }

    public static function find_by_id($user_id){

        global $database;

        $result_set = static::find_by_query("SELECT * FROM " . static::$db_table . " WHERE "  .static::$db_id. "= '{$user_id}' LIMIT 1 ");

        return !empty($result_set) ? array_shift($result_set) : false;

    }


    public static function find_by_query($sql){

        global $database;

        $result_set= $database->query($sql);

        $the_object_array = array();

        while($row=mysqli_fetch_array($result_set)){

            //echo '<pre>',print_r($row,1),'</pre>';


            $the_object_array[] = static::instantation($row);
        }

        return $the_object_array;

    }


    public static function instantation($the_record){

        $calling_class = get_called_class();

        $the_object = new $calling_class;

        //        $the_object->id         = $found_user['id'];
        //        $the_object->username   = $found_user['username'];
        //        $the_object->password   = $found_user['password'];
        //        $the_object->first_name = $found_user['first_name'];
        //        $the_object->last_name  = $found_user['last_name'];

        foreach ($the_record as $the_attribute => $value){

            if($the_object->has_the_attribute($the_attribute)){

                $the_object->$the_attribute=$value;

            }
        }

        return $the_object;
    }

     //                                     STRING 
    private function has_the_attribute($the_attribute){
        
        //                         ARRAY 

        $object_properties = get_object_vars($this);

        return array_key_exists($the_attribute, $object_properties);

    } 

    protected function properties() {

        //        return get_object_vars($this);

        $properties = array();

        foreach(static::$db_table_fields as $db_field){

            if(property_exists($this, $db_field)){

                $properties[$db_field] = $this->$db_field;
            }

        }

        return $properties;

    }


    protected function clean_properties(){

        global $database;

        $clean_properties = array();

        foreach($this->properties() as $key => $value){

            $clean_properties[$key] = $database->escape_string($value);

        }

        return $clean_properties;
    }


    public function save(){

        return isset($this->id) ? $this->update() : $this->create();

    }



// CREATE METHOD - CRUD PART 1

    public function create(){

        global $database;

        $properties = $this->properties();

        $sql  = "INSERT INTO " .static::$db_table. "(". implode(",",array_keys($properties))  .")";
        $sql .= "VALUES ('". implode("','", array_values($properties))  ."')";

        //BEFORE ABSTRACTION
        //        $sql .= $database->escape_string($this->username). "', '";
        //        $sql .= $database->escape_string($this->password). "', '";
        //        $sql .= $database->escape_string($this->first_name). "', '";
        //        $sql .= $database->escape_string($this->last_name). "') ";



        if($database->query($sql)){

            $this->id=$database->the_insert_id();

            return true;

        } else {

            return false;

        }

    } // CREATE METHOD



//UPDATE METHOD
    
    public function update(){

        global $database;

        $properties = $this->properties();

        $properties_pairs = array();

        foreach ($properties as $key => $value) {

            $properties_pairs[] = "{$key}='{$value}'";
        }

        $sql  = "UPDATE ".static::$db_table. " SET ";
        $sql .= "username= '".$database->escape_string($this->username). "', ";
        $sql .= "password= '".$database->escape_string($this->password). "', ";
        $sql .= "first_name= '".$database->escape_string($this->first_name). "', ";
        $sql .= "last_name= '".$database->escape_string($this->last_name). "' ";
        $sql .= " WHERE id= ".$database->escape_string($this->id);

        //KEEP ABOVE SPACE BEFORE " WHERE" -> STRING CONCATENATE ELIMINATES SPACES?

        $database->query($sql);

        return (mysqli_affected_rows($database->connection) == 1) ? true : false;

    }//UPDATE METHOD


//DELETE METHOD

    public function delete(){

        global $database;

        $sql  = "DELETE FROM " .static::$db_table. " users ";
        $sql .= " WHERE id= ".$database->escape_string($this->id);
        $sql .= " LIMIT 1";

        $database->query($sql);

        return (mysqli_affected_rows($database->connection) == 1) ? true : false;

    } //DELETE METHOD



}

?>