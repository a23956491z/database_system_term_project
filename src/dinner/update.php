<?php
    include 'dinner.php';
    include '../auth/session.php';
    include_once '../utils/utils.php';
    
    $login_session = new Login_Session();
    if(empty($login_session->get_user())){
        redirect("/auth/login.php");
    }
    $user = $login_session->get_user();
    $user_id = $login_session->get_user_id();

    $dinner = new Dinner();

    $id = $_GET['id'] ?? null;
    $valid_dinner_id = False;
    $status_msg = "";

    if(isset($id) and isInteger($id) ){
        
        $dinner_by_id = $dinner->_get_dinner($id);

        if(empty($dinner_by_id)){
            $status_msg = "DINNER NOT FOUND";
            
        }else{
            
            $dinner_uid = $dinner_by_id["uid"];

            if($dinner_uid != $user_id){
                $status_msg = "NOT AUTHENTICATED";
            }else{
                
                $dinner_uname = $dinner_by_id["username"];
                $dinner_name = $dinner_by_id["name"];

                $dinner_tags = $dinner->get_tags($id);
                // echo print_r($dinner_tags);
                $dinner_tags_string = implode(", ", $dinner_tags);

                echo $dinner_uid, " ", $dinner_uname;
            }

        }
    }else{
        $status_msg = "NOT VALID";
    }

    echo $status_msg;

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Update Dinner</title>
    </head>

    <body> 

        <h1>UPDATE!</h1>

        <div>
            <a href="/dinner/index.php">BACK</a>

        </div>
    <form action="update.php" method="post">
            <input type="hidden" name="id" value="<?php echo $id ?? "";?>">
        <div>
            <span>Dinner name  </span><input type="text" name="name" value="<?php echo $dinner_name ?? "";?>" required>
        </div>
        <div>
            <span>Tages </span><input type="text" name="tags" value="<?php echo $dinner_tags_string ?? "";?>">
        </div>
        <div>
            <span>Author </span> 
        </div>

        <button type="submit" name="submit">Submit</button>
    </form>
        <?php
            echo "<h1>Hi! User : ". $user. " with id : ". $user_id ."</h1>";


  
            if (!empty($_POST)){

                
                $tags = explode(",", $_POST["tags"]);
                $tags = array_map('trim', $tags);

                // echo implode(' | ', $tags);

                $data = array(
                    'id' => $_POST['id'],
                    'name' => $_POST["name"],
                    'tags' => $tags,
                    
                );

                
                $dinner->update_dinner($data);

                meta_redirect("/dinner/index.php");
            }
        ?>
    </body>
</html>