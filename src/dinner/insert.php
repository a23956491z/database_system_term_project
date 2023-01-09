<?php
    include 'dinner.php';
    include '../auth/session.php';
    
    $login_session = new Login_Session();
    if(empty($login_session->get_user())){
        redirect("/auth/login.php");
    }

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Create Dinner</title>
    </head>

    <body> 

        <h1>Create!</h1>
    <form action="insert.php" method="post">
        <div>
            <span>Dinner name  </span><input type="text" name="name" required>
        </div>
        <div>
            <span>Tages </span><input type="text" name="tags">
        </div>
        <button type="submit" name="submit">Submit</button>
    </form>
        <?php
            $user = $login_session->get_user();
            $user_id = $login_session->get_user_id();

            if(empty($user)){
                redirect("/auth/login.php");
            }

            echo "<h1>Hi! User : ". $user. " with id : ". $user_id ."</h1>";
            if (!empty($_POST)){

                

                $tags = explode(",", $_POST["tags"]);
                $tags = array_map('trim', $tags);

                echo implode(' | ', $tags);

                $data = array(
                    'name' => $_POST["name"],
                    'tags' => $tags,
                    'user' => $user_id
                );

                $dinner = new Dinner();
                $dinner->insert_dinner($data);

                meta_redirect("/dinner/index.php");
            }
        ?>
    </body>
</html>