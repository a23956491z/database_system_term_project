<?php
    include "auth/session.php";

    $login_session  = new Login_Session();


?>


<!DOCTYPE html>
<html>



<body>



<?php

$current_user = $login_session->get_user();

echo "<h2>";
if(!empty($current_user)){
    echo "You're Logined! Welcom: ".$current_user;

    echo "<br><a href=\"auth/logout.php\">Logout</a>";
    echo "<br><a href=\"dinner/index.php\">Dinner</a>";
}
else{
    echo "You're unauthenticated !!! <a href=\"auth/login.php\">Login</a>";
}

echo "</h2>";
?>


</body>
</html>
