<?php
    include '../utils/utils.php';
    class Dinner{

        private $conn;
        function __construct(){
            $this->conn  = connect_to_db();
        }

        // return a arrray with tags
        function get_tags($dinner_id){
            $sql = sprintf("SELECT * FROM dinner_tag INNER JOIN tag ON dinner_tag.id=tag.id WHERE dinner='%s' ", $dinner_id );
            $result = $this->conn->query($sql);

            $tags = array();
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    array_push($tags, $row["name"]);
                }
            }

            return $tags;
        }
        function get_dinners(){
            $sql = sprintf("SELECT dinner.id, name, users.username, users.id as uid FROM dinner INNER JOIN users ON dinner.author=users.id" );

            $result = $this->conn->query($sql);

            if($result->num_rows > 0){

                $result_array = array();
                while($row = $result->fetch_assoc()){
                    
                    $tags = $this->get_tags($row["id"]);

                    $row_result["id"] = $row["id"];
                    $row_result["username"] = $row["username"];
                    $row_result["dinner"] = $row["name"];
                    $row_result["tags"] = $tags;
                    
                    array_push($result_array, $row_result);
                    // encode array to json with utf8 support
                    
                    
                    // echo "id: ". $row["id"]. " - Name: ". $row["name"]. " - Author: ".$row["username"]. "- Tags". implode($tags);
                }
                $response_json = json_encode($result_array, JSON_UNESCAPED_UNICODE);
                echo $response_json;
            }else{
                echo "no result...";
            }
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Dinner</title>
    </head>

    <body> 

        <h1>Dinner!</h1>



        <?php
            $dinner = new Dinner();
            $dinner->get_dinners();
        ?>
    </body>
</html>