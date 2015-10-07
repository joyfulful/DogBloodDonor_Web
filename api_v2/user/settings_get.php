<?php
header('Content-Type: application/json');
include "../../include/functions.php";
include "../../include/dbcon.inc.php";
include "../../include/settings_functions.inc.php";
$user_id = getUserIdFromToken($con, @$_POST["token"]);
$response = array();
if ($user_id != 0) {
    $response = getAllUserSettings($user_id, $con);
}
echo json_encode($response);