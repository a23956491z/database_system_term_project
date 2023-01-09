<?php
    include '../utils/utils.php';
    include 'session.php';
    
    $login_session = new Login_Session();
    if(!empty($login_session->get_user())){
        redirect("/dinner/index.php");
    }

    class Login{

        private $conn;
        private $login_session;
        function __construct($session){
            $this->conn  = connect_to_db();
            $this->login_session = $session;
        }

        private function _login($username, $password){
            
            // this is insanely bad practice
            // because we sent the user-typed unhashed password in request
            // $sql = sprintf("SELECT id FROM users WHERE username = '%s' AND password = '%s'" , $username, $password);
            
            $sql = sprintf("SELECT id,password FROM users WHERE username = '%s'" , ( $this->conn->real_escape_string($username)));

            if ($result = $this->conn->query($sql)) {

                if ($result->num_rows == 1) {

                        $result_arr = $result->fetch_array( MYSQLI_ASSOC);
                        
                        if(password_verify($password, $result_arr["password"])){
                            
                            return $result_arr["id"];
                        }
                        ;
                        // return $result_arr[0];
                        
                    }
                    
                
            }
    

            return 0;
        }

        function login($username, $password){
            if( ($user_id = $this->_login($username, $password) ) !== 0){

                $this->login_session->set_to_login($username, $user_id);
                
                meta_redirect("/dinner/index.php");
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

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
   
    </head>

    <body> 
    <div class="container">

    <nav class="nav py-3">
        <a class="nav-link active" href="/dinner/index.php">Home Page</a>
        <a class="nav-link" href="/report.html">Report</a>
        <a class="nav-link" href="/figure.html">Diagrams</a>
        
 
    </nav>

        <h1>Login</h1>

        <form  action="login.php" method="post">
            
                <div class="form-group row py-2">
                    <div class="col">
                        <h3>Username: </h3>
                    </div>
                    <div class="col-10">
                        <input   class="form-control" type="text" name="username"> 
                    </div>
                </div>
            
            <div class="form-group row py-2">
                <div class="col">
                    <h3>Password: </h3>
                </div>
                <div class="col-10">
                    <input  class="form-control" type="password" name="password"> 
                </div>
            </div>

            <div class="row py-2">
                <div class="col">
                <input type="submit" class="btn btn-primary" > <a href="/auth/register.php" type="button" class="btn btn-info"> Register </a> 
            </div> </div>
            
        </form>

        <div class="row py-2">
            <div class="col py-2">
        <a href="/dinner/index.php" type='button' class='btn btn-secondary'> Back </a> 
</div>
        </div>
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
    </div>
    </body>
</html>
