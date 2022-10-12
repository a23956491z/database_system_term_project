<?php
    include '../utils/utils.php';

    class Login{

        private $conn;

        function __construct(){
            $this->conn  = connect_to_db();
        }

        private function _login($username, $password){

            $sql = sprintf("SELECT password FROM users WHERE username = '%s'" , $username);

            if ($result = $this->conn->query($sql)) {

                if ($result->num_rows == 1) {

                    $fetched_password = $result->fetch_assoc()["password"];
                    if ($password === $fetched_password){
                        return 1;
                    }
                    
                }
            }

            return 0;
        }

        function login($username, $password){
            if($this->_login($username, $password) === 1){
                return "Logined!";
            }else{
                return "Login failed!";
            }
            
        }
    }
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


        <?php
            $login_helper = new Login();

            if (!empty($_POST))
            {

                $username = $_POST["username"] ?? "" ;
                $password = $_POST["password"] ?? "" ;
                
                echo "<h1>";
                echo $login_helper->login($username, $password);
                echo "</h1>";
            }
        ?>
    </body>
</html>


<?php

?>