<?php

header('Access-Control-Allow-Origin: *');
include "../../include/dbcon.inc.php";
include "../../include/functions.php";
$user_id = getUserIdFromToken($con, @$_POST["token"]);
$devid = $con->real_escape_string($_POST["deviceid"]);

$findDevId = $con->query("SELECT * FROM user_deviceid WHERE device_id = '$devid'");
if ($findDevId->num_rows == 0) {
    $con->query("INSERT INTO `user_deviceid`(`id`, `user_id`, `device_id`, `last_update`, `created_at`, `status`) "
            . "VALUES (null,'$user_id','$devid',now(),now(),1)");
}
if($con->error == ""){
    echo "ok";
}else{
    echo "error";
}
?>