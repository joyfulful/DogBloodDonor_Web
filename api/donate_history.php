<?php
include "../include/functions.php";
include "../include/dbcon.inc.php";
header('Content-Type: application/json');
$user_id = getUserIdFromToken($con, @$_POST["token"]);
$request_id = $con->real_escape_string(@$_POST["request_id"]);
$dog_id = $con->real_escape_string(@$_POST["dog_id"]);
$response = array();

if ($user_id != 0) {
    $res = $con->query("SELECT donate.dog_id as donator_dog_id, donate.donate_date, request.for_dog_id as requester_dog_id, user_dog.dog_name as requester_dog_name FROM donate
LEFT JOIN request ON request.request_id = donate.request_id 
LEFT JOIN user_dog ON request.for_dog_id = user_dog.dog_id
WHERE donate.donate_status = 1
AND request.request_type = 2
AND donate.dog_id = '$dog_id'
ORDER BY donate_date DESC");
   while($data = $res->fetch_assoc()){
    array_push($response,$data);
   }
}
echo json_encode($response);
?>