<?php

include "../../include/functions.php";
include "../../include/dbcon.inc.php";
header('Content-Type: application/json');
//isset($_POST["message"]) เช็คว่าต้องส่ง message มาด้วย

$user_id = getUserIdFromToken($con, @$_POST["token"]);
if ($user_id != 0 & isset($_POST["message"])) {
    $to_user_id = $con->real_escape_string($_POST["to_user_id"]);
    $message = $con->real_escape_string($_POST["message"]);
    $queryUser = $con->query("INSERT INTO `pm`(`message_id`, `from_user_id`, `to_user_id`, "
            . "`message`, `message_time`) "
            . "VALUES (null, '$user_id','$to_user_id','$message',now())");
    
    $result = 0;
}
$response = array(
    "result"=> $result
);
 echo json_encode($response);
?>