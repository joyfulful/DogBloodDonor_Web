<?php

include "../../include/functions.php";
include "../../include/dbcon.inc.php";
include "../../include/pm_functions.inc.php";
header('Content-Type: application/json');

$user_id = getUserIdFromToken($con, @$_POST["token"]);
if($user_id == 0){
    die();
}
$response = getThread($user_id, $con);
echo json_encode($response);
?>