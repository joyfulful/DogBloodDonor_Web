<?php
header('Content-Type: application/json');
include "../../include/dbcon.inc.php";
include "../../include/functions.php";

$user_id = getUserIdFromToken($con,@$_POST["token"]);
$dog_id = $con->real_escape_string($_POST["dog_id"]);
$res = $con->query("SELECT * FROM user_dog WHERE dog_id = '$dog_id' AND user_id = '$user_id' AND dog_status = 1");
$result = 0;
$errortext = "";
if($res->num_rows > 0){
    $update = $con->query("UPDATE user_dog SET dog_status = 0 WHERE dog_id = '$dog_id'");
    if($update == 1){
        $result = 1;
    }else{
        $result = 0;
        $errortext = "Update Error";
    }
}else{
    $result = 0;
    $errortext = "Dog Not Found !";
}
$response = array(
    "result"=>$result,
    "message"=>$errortext
);
echo json_encode($response);
?>