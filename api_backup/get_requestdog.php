<?php
//jj
include "../include/functions.php";
include "../include/dbcon.inc.php";
header('Content-Type: application/json');
$user_id = getUserIdFromToken($con, @$_POST["token"]);
$response = array();
$dogresult = $con->query("SELECT * FROM user_dog "
        . "JOIN dog_breeds ON user_dog.breeds_id = dog_breeds.breeds_id "
        . "JOIN blood_type ON blood_type.bloodtype_id = user_dog.dog_bloodtype_id "
        . "WHERE user_dog.user_id = '$user_id' AND user_dog.dog_status = 1");

while ($userdog = $dogresult->fetch_assoc()) {
    //check if dog still have request and cannot make new request
    $dog_id = $userdog["dog_id"];
    $check = $con->query("SELECT * FROM request
WHERE request.request_type = '2'
AND request.request_id NOT IN
	(SELECT donate.request_id FROM donate
     WHERE donate.donate_status != 0)
AND request.for_dog_id = '$dog_id';");
    echo $con->error;
    if($check->num_rows == 0){
        $isOk = true;
        $reason = "";
    }else{
        $isOk = false;
        $reason = "ไม่สามารถขอเลือดได้เนื่องจากมีการขอเลือดค้างอยู่";
    }
    $dog = array(
        "dog_id" => $userdog["dog_id"],
        "dog_name" => $userdog["dog_name"],
        "breeds_id" => $userdog["breeds_id"],
        "breeds_name" => $userdog["breeds_name"],
        "bloodtype_id" => $userdog["bloodtype_id"],
        "bloodtype_name" => $userdog["bloodtype_name"],
        "isOk" => $isOk,
        "reason"=>$reason
    );
    array_push($response, $dog);
}
echo json_encode($response);
?>