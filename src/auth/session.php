<?php

class Login_Session{


    function __construct()
    {
        session_start();
        // echo '<pre>'; print_r($_SESSION); echo '</pre>';
    }

    function set_to_login($user){

        $_SESSION['user'] = $user;
    }
    function get_user(){
        if (isset($_SESSION['user'])){
        
            return $_SESSION['user'];
        }
        return "";
    }

    function set_to_logout(){
        unset($_SESSION['user']);
    }
}

?>