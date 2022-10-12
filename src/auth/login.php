<?php
    include '../utils/utils.php';

?>


<!DOCTYPE html>
<html>
    <head>
        <title>Login</title>
    </head>

    <body> 

        <h1>Login</h1>
        <form  action="login.php" method="post">

            Username: <input type="text" name="username"> <br>
            Password: <input type="password" name="password"> <br>
            <input type="submit"> <a href="/auth/register.php"> Register </a>
        </form>


        
    </body>
</html>


<?php

?>