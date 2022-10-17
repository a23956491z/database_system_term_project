<?php

class Login_Session{


    function __construct()
    {
        session_start();
        // echo '<pre>'; print_r($_SESSION); echo '</pre>';
    }

    function set_to_login($user, $id){

        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $id;
    }
    function get_user(){
        if (isset($_SESSION['user'])){
        
            return $_SESSION['user'];
        }
        return "";
    }
    function get_user_id(){
        if (isset($_SESSION['user_id'])){
        
            return $_SESSION['user_id'];
        }
        return "";
    }

    function set_to_logout(){
        unset($_SESSION['user']);
        unset($_SESSION['user_id']);
    }
}

?>