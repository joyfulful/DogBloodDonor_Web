<?php

include "../include/functions.php";
include "../include/dbcon.inc.php";
header('Content-Type: application/json');
$user_id = getUserIdFromToken($con, @$_POST["token"]);
$request_id = $con->real_escape_string(@$_POST["request_id"]);
$dog_id = $con->real_escape_string(@$_POST["dog_id"]);
$isDonate = $con->real_escape_string(@$_POST["isDonate"]);
if ($user_id != 0) {
    $res = $con->query("UPDATE donate SET donate_status = '$isDonate' , donate_date = now() "
            . "WHERE request_id = '$request_id' AND dog_id ='$dog_id' ");
    echo $con->error;
    if ($con->error == "") {
        $response = array("result" => 1);
    } else {
        $response = array("result" => 0);
    }
}
echo json_encode($response);

?>