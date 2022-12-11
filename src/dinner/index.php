<?php

    include 'dinner.php';
    include '../auth/session.php';
    $login_session = new Login_Session();


    $dinner = new Dinner();
    $response = $dinner->get_dinners();

    $decoded = json_decode($response);
    $status = $decoded->status;

    
    $data = $decoded->response;


?>
<!DOCTYPE html>
<html>
    <head>
        <title>Dinner</title>

        <style>
            table, th, td {
                border: 1px solid black;
            }
        </style>
    </head>

    <body> 

        <h1>Dinner!</h1>
        
        
        <?php 
            if(isset($_GET['response']) ){
                echo $_GET['response'];
            }
            // echo $response;
            // echo "<p>status : ".$status. "</p>"; 
        ?>
        
        <p><a href="/dinner/insert.php">Insert a new dinner</a></p>


        <?php

            if(!empty($data)){
                
                

                echo "
                <table>
                    <tr>
                        <th>Dinner</th>
                        <th>Author</th>
                        <th>Tags</th>
                        <th></th>
                    </tr>
                ";
                foreach($data as &$d){
                    echo "<tr>";
        


                    $delete_button = sprintf('<a href="/dinner/delete.php?id=%s">Delete</a>', $d->id);
                    echo sprintf(
                        "
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        <td>%s</td>
                        ", 
                        $d->dinner, 
                        $d->username, 
                        implode(", ", $d->tags),
                        $delete_button);
                    echo "</tr>";
                }

                echo "<table>";
            }else{
                echo "NO RESULT";
            }



            $user = $login_session->get_user();
            
            if(!empty($user)){
                echo "USER: ".$user, "<br/>";
                echo "<br><a href=\"/auth/logout.php\">Logout</a><br/>";
                
            }
            else{
                echo "<a href=\"/auth/login.php\">Login</a><br/>";
            }
            
  
        

        ?>
        
    </body>
</html>