<?php
include "../include/functions.php";
include "../include/dbcon.inc.php";
header('Content-Type: application/json');
$user_id = getUserIdFromToken($con, @$_POST["token"]);
$request_id = $con->real_escape_string(@$_POST["request_id"]);
$dog_id = $con->real_escape_string(@$_POST["dog_id"]);
$res = $con->query("INSERT INTO `donate`(`donate_id`, `request_id`, `dog_id`, `donate_date`,"
        . " `donate_status`, `donate_lastupdate`) "
        . "VALUES (null,'$request_id','$dog_id',0,0,now())");
if($con->error == ""){
    $response = array("result"=>1);
}else{
    $response = array("result"=>0);
}
echo json_encode($response);
?>