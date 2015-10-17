<?php
header('Content-Type: application/json');
include '../../include/functions.php';
include '../../include/dbcon.inc.php';
include '../../include/push_functions.inc.php';
$user_id = getUserIdFromToken($con, @$_POST["token"]);
$token = $con->real_escape_string(@$_POST["token"]);
$device_id = $con->real_escape_string(@$_POST["device_id"]);
$res = $con->query("UPDATE user_session SET isvalid = 0 WHERE token = '$token'");
if($device_id != ""){
    unRegisterDevice($user_id, $device_id, $con);
}
if ($con->error == "") {
    $response = array(
        "result" => 1
    );
} else {
    $response = array(
        "result" => 0
    );
}
echo json_encode($response);
?>