<?php
header('Access-Control-Allow-Origin: *');
include "../../include/dbcon.inc.php";
include "../../include/functions.php";
include "../../include/push_functions.inc.php";

$user_id = getUserIdFromToken($con, @$_POST["token"]);
$devid = $con->real_escape_string($_POST["deviceid"]);

registerDevice($user_id, $devid, $con);
?>