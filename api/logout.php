<?php
header('Content-Type: application/json');
include '../include/config.php';
include '../include/functions.php';
include '../include/dbcon.inc.php';

$user_id = getUserIdFromToken($con,@$_POST["token"]);
$token = $con->real_escape_string(@$_POST["token"]);
$res = $con->query("UPDATE user_session SET isvalid = 0 WHERE token = '$token'");
if($con->error == ""){
    $response = array(
        "result" => 1
    );
}else{
    $response = array(
        "result" => 0
    );
}
echo json_encode($response);
?>