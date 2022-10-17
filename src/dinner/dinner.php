<?php
    include '../utils/utils.php';
class Dinner{

    private $conn;
    function __construct(){
        $this->conn  = connect_to_db();
    }

    // SELECT * FROM dinner_tag INNER JOIN tag ON dinner_tag.id=tag.id WHERE dinner=6

    // return a arrray with tags
    function get_tags($dinner_id){
        $sql = sprintf("SELECT * FROM dinner_tag INNER JOIN tag ON dinner_tag.tag=tag.id WHERE dinner='%s' ", $dinner_id );
        $result = $this->conn->query($sql);

        $tags = array();
        if($result->num_rows > 0){
            // echo $result->num_rows;
            while($row = $result->fetch_assoc()){
                array_push($tags, $row["name"]);
            }
        }
        // echo implode(", ", $tags);

        return $tags;
    }
    function get_dinners(){
        $sql = sprintf("SELECT dinner.id, name, users.username, users.id as uid FROM dinner INNER JOIN users ON dinner.author=users.id" );

        $result = $this->conn->query($sql);
        
        $result_dic = array();
        if($result->num_rows > 0){
            
            
            $result_array = array();
            while($row = $result->fetch_assoc()){
                
                $tags = $this->get_tags($row["id"]);
                
                $row_result["id"] = $row["id"];
                $row_result["username"] = $row["username"];
                $row_result["dinner"] = $row["name"];
                $row_result["tags"] = $tags;
                
                array_push($result_array, $row_result);
                
            }
            $result_dic['status'] = "successful";
            $result_dic['response'] = $result_array; 
                

        }else{

            $result_dic['status'] = "fail or no result";
            
        }
        // encode array to json with utf8 support
        $response_json = json_encode($result_dic, JSON_UNESCAPED_UNICODE);
        return $response_json;
    }

    // this function always return a tag id
    //      if the tag is not exist, this function would insert that tag into database.
    function check_and_insert_tag($tag){
        $sql = sprintf("SELECT id FROM tag WHERE name='%s'", $tag );
        $result = $this->conn->query($sql);

        if($result->num_rows > 0){
            return $result->fetch_array(MYSQLI_NUM)[0];
        }

        $sql =sprintf("INSERT INTO tag (name) VALUES ('%s')", $tag );
        $this->conn->query($sql);

        return $this->conn->insert_id;

    }
    function _insert_dinner_tag($dinner, $tag){
        $sql = sprintf("INSERT INTO dinner_tag (dinner, tag) VALUES ('%s', '%s')", $dinner, $tag);
        $this->conn->query($sql);
    }
    function _insert_dinner($dinner, $user){
        $sql = sprintf("INSERT INTO dinner (name, author) VALUES ('%s', '%s')", $dinner, $user);

        $this->conn->query($sql);

        return $this->conn->insert_id;
    }
    function insert_dinner($data){
        
        echo "<br>";

        // insert dinner with user and name
        $dinner_id = $this->_insert_dinner($data["name"], $data["user"]);
        echo "dinner id : ". $dinner_id;

        // insert dinner-tag with dinner id and tag id
        foreach ($data["tags"] as &$tag){
            $tag_id = $this->check_and_insert_tag($tag); 
            $this->_insert_dinner_tag($dinner_id, $tag_id);   
        }
    }
}

?>