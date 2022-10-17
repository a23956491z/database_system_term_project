<?php
    include '../utils/utils.php';
    include 'session.php';
    
    $login_session = new Login_Session();
    if(!empty($login_session->get_user())){
        redirect("/index.php");
    }

    Class Register{

        private $conn;
        private $define_error_msg;

        // return 1 if the check passed.
        private function _checker_username_exists($username){

            $sql = sprintf("SELECT username FROM users WHERE username = '%s'" , $username);

            if ($result = $this->conn->query($sql)) {

                if ($result->num_rows > 0) {

                    return $this->define_error_msg["USERNAME_EXIST"];
                }
            }
            return "";
        }

        private function _checker_email_exists($email){

            $sql = sprintf("SELECT username FROM users WHERE email = '%s'" , $email);

            if ($result = $this->conn->query($sql)) {

                if ($result->num_rows > 0) {

                    return $this->define_error_msg["EMAIL_EXIST"];
                }
            }
            return "";
        }
        
        private function _register($username, $password, $email){
            $sql = sprintf("INSERT INTO users(username, password, email) VALUES('%s', '%s', '%s')",
                        $username, $password, $email);
            
            if ($this->conn->query($sql) === TRUE){
                echo $this->sucessful_msg;
            }
            return "";
        }
        function __construct() {
            $this->check_state = 0;
            $this->conn = connect_to_db();
            
            $this->define_error_msg["USERNAME_EXIST"] = "Username is already exists.";
            $this->define_error_msg["EMAIL_EXIST"] = "Email is aleardy exists..";
            $this->sucessful_msg = "Register Sucessfully !";
            $this->failed_msg = "Register Failed by unknowed reason !"; 
        }


        // Please remember to do the dynamic check before register to the db.
        function register($username, $password, $email){
            
            // these checks should be statisfied.
            $error_msg = "";
            $error_msg = $this->_checker_username_exists($username);
            

            if ($_error_msg = $this->_checker_email_exists($email) ) {$error_msg = $_error_msg;} 

            if(empty($error_msg)){
                return $this->_register($username, $password, $email);
            }
            return $error_msg;
        }
    }

    function register_data_static_check($username, $password_1, $password_2, $email){
        
        $define_error_msg["PASSWORD_NOT_MATCH"] = "Password and Repeat Password didn't match.";
        $define_error_msg["PASSWORD_EMPTY"] = "Password field is empty !";
        $define_error_msg["USERNAME_EMPTY"] = "Username field is empty !";
        $define_error_msg["EMAIL_EMPTY"] = "Email field is mepty !";
        

        $error_msg = "";

        // check empty
        if (empty($username)){
            $error_msg = $define_error_msg["USERNAME_EMPTY"];
        }

        if (empty($password_1) || empty($password_2)){
            $error_msg = $define_error_msg["PASSWORD_EMPTY"];
        }

        if (empty($email)){
            $error_msg = $define_error_msg["EMAIL_EMPTY"];
        }

        // check password match
        if ($password_1 != $password_2){
            $error_msg = $define_error_msg["PASSWORD_NOT_MATCH"];
        }

        return $error_msg;


        
    }




?>

<!DOCTYPE html>
<html>
    <head>
        <title>Register</title>
    </head>

    <body> 

        <h1>This is the Register page</h1>
        
        <form  action="register.php" method="post">

            Username: <input type="text" name="username"> <br>
            Password: <input type="password" name="password"> <br>
            Repeat Password: <input type="password" name="repeat_password"> <br>
            Email: <input type="text" name="email"> <br>

        
            <input type="submit"> <a href="/auth/login.php"> Back to Login</a>  
        </form>

        <?php

            $register_helper = new Register();
                        
            // if there is any post data
            if (!empty($_POST))
            {

                $username = $_POST["username"] ?? "" ;
                $password = $_POST["password"] ?? "" ;
                $password2= $_POST["repeat_password"]   ?? "" ;
                $email     = $_POST["email"]    ?? "" ;

                echo "<br><h3>";

                // First part : static check
                $error_msg =  register_data_static_check(    $username ,
                                                            $password,
                                                            $password2,
                                                            $email);
                                                            
                // Second part : dynamic check (check the data from database)
                if ($error_msg == ""){
                    
                    echo $register_helper->register($username, $password, $email);
                }else{
                    echo $error_msg;
                }
                echo "</h3>";
                
            }
        ?>
    </body>
</html>


