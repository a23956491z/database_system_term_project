<?php

function debug_to_console($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

function connect_to_db(){

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    // db information
    $host = 'db';
    $user = 'web_basic_client';
    $pass = 'r^mvVKMc*k2DneH9';
    $mydatabase = 'dinner_picker';

    $conn = "";
    try{
        $conn = new mysqli($host, $user, $pass, $mydatabase);
    } catch (mysqli_sql_exception $e){
        echo "Interal Server ERROR";
    }

    return $conn;
}
?>