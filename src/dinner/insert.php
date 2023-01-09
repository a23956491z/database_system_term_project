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

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
   

    </head>

    <body> 
    <div class="container">

    <nav class="nav py-3">
            <a class="nav-link active" href="/dinner/index.php">Home Page</a>
            <a class="nav-link" href="/report.html">Report</a>
            <a class="nav-link" href="/figure.html">Diagrams</a>
            
    
            <?php
                        $user = $login_session->get_user();
                
                        if(!empty($user)){
                            echo "<span class = 'p-2'>USER: ".$user, "  </span>";
                            echo "<a href=\"/auth/logout.php\" type='button' class='btn btn-dark'>Logout</a>";
                            
                        }
                        else{
                            echo "<a type='button' class='btn btn-info' href=\"/auth/login.php\">Login</a>";
                        }
                        
                ?>
        </nav>


        <h1>Create!</h1>

        <form action="insert.php" method="post">
            <div class="form-group row py-2">
                <div class="col">
                    <h3>Dinner name  </h3>
                </div>
                <div class="col-8">
                    <input type="text" name="name"  class="form-control" required>
                </div>
            </div>
            <div class="form-group row py-2">
                <div class="col">
                    <h3>Tages </h3>
                </div>
                <div class="col-8">
                    <input type="text"  class="form-control" name="tags">
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <button type="submit" name="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
        </form>
        <?php
            $user = $login_session->get_user();
            $user_id = $login_session->get_user_id();

            if(empty($user)){
                redirect("/auth/login.php");
            }

            // echo "<h1>Hi! User : ". $user. " with id : ". $user_id ."</h1>";
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

    </div>
    </body>
</html>