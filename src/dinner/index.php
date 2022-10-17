<?php

    include 'dinner.php';


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
        <p><a href="/dinner/insert.php">Insert a new dinner</a></p>


        <?php 
            echo $response;
            echo "<p>status : ".$status. "</p>"; 
        ?>
        <table>
            <tr>
                <th>Dinner</th>
                <th>Author</th>
                <th>Tags</th>
            </tr>

        <?php

            
            foreach($data as &$d){
                echo "<tr>";
                echo sprintf("<td>%s</td><td>%s</td><td>%s</td>", $d->dinner, $d->username, implode(", ", $d->tags));
                echo "</tr>";
            }

            
        ?>
        <table>
    </body>
</html>