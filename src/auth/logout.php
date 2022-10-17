<?php

include "session.php";
include "../utils/utils.php";
$login_session  = new Login_Session();

$login_session->set_to_logout();

redirect("/index.php");

?>