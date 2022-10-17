<?php
    include '../utils/utils.php';
    include 'session.php';
    
    $login_session = new Login_Session();
    if(!empty($login_session->get_user())){
        redirect("/index.php");
    }

    class Login{

        private $conn;
        private $login_session;
        function __construct($session){
            $this->conn  = connect_to_db();
            $this->login_session = $session;
        }

        private function _login($username, $password){

            $sql = sprintf("SELECT id FROM users WHERE username = '%s' AND password = '%s'" , $username, $password);

            if ($result = $this->conn->query($sql)) {

                if ($result->num_rows == 1) {

                        return $result->fetch_array(MYSQLI_NUM)[0];
                    }
                    
                
            }
    

            return 0;
        }

        function login($username, $password){
            if($user_id = $this->_login($username, $password) !== 0){

                $this->login_session->set_to_login($username, $user_id);
                
                meta_redirect();
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
                $login_helper = new Login($login_session);

                if (!empty($_POST))
                {

                    $username = $_POST["username"] ?? "" ;
                    $password = $_POST["password"] ?? "" ;
                    
                    echo "<h1>";
                    $login_msg = $login_helper->login($username, $password);
                    echo $login_msg;
                    echo "</h1>";

                }
        ?>
    </body>
</html>
