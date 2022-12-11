<?php
    include_once '../utils/utils.php';
class Dinner{

    private $conn;
    private $dinner_array;
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

    function _get_dinner($dinner){
        $sql = sprintf("SELECT dinner.id, name, users.username, users.id as uid FROM dinner LEFT JOIN users ON dinner.author=users.id WHERE dinner.id='%s'", $dinner );
        $result = $this->conn->query($sql);

        if($result->num_rows > 0){
            $row = $result->fetch_assoc();

            return $row;
        }
    }
    function get_dinners(){
        $sql = sprintf("SELECT dinner.id, name, users.username, users.id as uid FROM dinner LEFT JOIN users ON dinner.author=users.id" );

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
            $dinner_array = $result_array;

        }else{

            $result_dic['status'] = "fail or no result";
            $result_dic['response'] = "";
            
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

    function update_dinner($data){
        
        

    }

    // INSERT INTO dinner (name, author) VALUES ("TO_BE_DELETED", 3) ;
    // $dinner is dinner id
    function _delete_dinner($dinner){

        // DELETE FROM dinner WHERE id = 9
        $sql = sprintf("DELETE FROM dinner WHERE id = $dinner");

        $this->conn->query($sql);

    }
    function delete_dinner($data){

        $user = $data["user"];
        $dinner = $data["dinner"];

        
        $result_dic = array();

        if(empty($dinner_array)){
            $dinner_res = json_decode($this->get_dinners());
            $dinner_data = $dinner_res->response;
            if (!empty($dinner_data)){
                $this->dinner_array = $dinner_data;

            }else{
                $result_dic["status"] = "Fetch dinner failed";

                return json_encode($result_dic, JSON_UNESCAPED_UNICODE);
            }

        }


        // print_r( $this->dinner_array);


        $dinner_by_id = $this->_get_dinner($dinner);
        $dinner_uid = $dinner_by_id["uid"] ?? null;

        // echo "dinner id : ",$dinner, "<br/>";
        // echo "user id : ", $user, "<br/>";
        // var_dump($dinner_uid === $user);
        // echo "EQUAL : ", ($dinner_uid===$user), " UID:CID ", $dinner_uid, " : ", $user, "<br/>";

        if(empty($dinner_by_id)){
            $result_dic["status"] =  "DINNER not found";
            
        }else{

            if ($dinner_uid == $user){
                $this->_delete_dinner($dinner);

                $result_dic["status"] = "successful";
            }else{
    
                $result_dic["status"] = "permission deny";
            }
        }
        // print_r($dinner_by_id);
        // echo "dinner author : ", 
        // $this->_delete_dinner($data);



        $response_json = json_encode($result_dic, JSON_UNESCAPED_UNICODE);
        return $response_json;
    }
}

?>