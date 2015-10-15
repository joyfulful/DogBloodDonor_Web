<?php

include "../../include/functions.php";
include "../../include/dbcon.inc.php";
include "../../include/pm_functions.inc.php";
include "../../include/push_functions.inc.php";
header('Content-Type: application/json');
$user_id = getUserIdFromToken($con, @$_POST["token"]);
$result = 0;
if ($user_id != 0 & isset($_POST["message"])) {
    $to_user_id = $con->real_escape_string($_POST["to_user_id"]);
    $message = $con->real_escape_string($_POST["message"]);
    $result = sendMessage($user_id, $to_user_id, $message, $con);
}
$response = array(
    "result" => $result
);
echo json_encode($response);
?>