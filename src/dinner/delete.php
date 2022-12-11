<?php
    include_once '../utils/utils.php';
    include_once '../auth/session.php';
    include_once 'dinner.php';

    $login_session = new Login_Session();
    $dinner = new Dinner();

    function isInteger($input){
        return(ctype_digit(strval($input)));
    }

    if(isset($_GET['id']) and isInteger($_GET['id']) ){
        $id = $_GET['id'];


        // echo "ID : ", $id, "<br/>";


    $user_id = $login_session->get_user_id();
    // echo $user_id, "<br/>";


    $data = array(
        'dinner' => $id,
        'user' => $user_id
    );

    $decoded_response  = json_decode($dinner->delete_dinner($data));
    // echo "delete status : " ,$decoded_response->status, "<br/>";

    $response = $decoded_response->status;
}else{
    // echo "Invalid id";

    $response = "Invalid ID";
}

    $uri_with_parameter = sprintf("/dinner/index.php?response=%s", $response);
    meta_redirect($uri_with_parameter);

?>