<?php
    include "auth/session.php";

    $login_session  = new Login_Session();


?>


<!DOCTYPE html>
<html>

<head>

    <title>Dinner picker</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
   
</head>

<body>



    <?php

    // $current_user = $login_session->get_user();

    // echo "<h2>";
    // if(!empty($current_user)){
    //     echo "You're Logined! Welcom: ".$current_user;

    //     echo "<br><a href=\"auth/logout.php\">Logout</a>";
    //     // echo "<br><a href=\"dinner/index.php\">Dinner</a>";
    // }
    // else{
    //     echo "You're unauthenticated !!! <a href=\"auth/login.php\">Login</a>";
    // }

    // echo "</h2>";
    ?>

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

        <h3><a href="/report.html"> Report</a> </h3> <h3><a href="/figure.html"> Diagrams</a> </h3>
<h2><a href="/dinner/index.php"> Dinner page </a> </h2>
</div>

</body>
</html>
