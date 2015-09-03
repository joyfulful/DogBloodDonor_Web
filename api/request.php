<?php

header('Content-Type: application/json');
include "../include/dbcon.inc.php";
include "../include/functions.php";
//ประกาศตัวเเปร ที่รับค่า input มาจาก mobile 
$user_id = getUserIdFromToken($con, @$_POST["token"]);
$dog_id = $con->real_escape_string($_POST["dog_id"]);
$symptoms = $con->real_escape_string($_POST["symptoms"]);
$place_id = $con->real_escape_string($_POST["place_id"]);
$duedate = $con->real_escape_string($_POST["duedate"]);
$volume = $con->real_escape_string($_POST["volume"]);

$resultd = $con->query("SELECT * FROM user_dog WHERE dog_id = '$dog_id'");
$data = $resultd->fetch_assoc();

$bloodtype_id = $data['dog_bloodtype_id'];
$result = $con->query("SELECT * FROM hospital_bloodstore hb JOIN hospital_dog hd ON hb.hospital_dogid = hd.hospital_dogid "
        . "WHERE hd.bloodtype_id = '$bloodtype_id' and hb.status = 1 and hb.exp_date > now() ORDER BY hb.exp_date ASC");
echo $con->error;

if ($result->num_rows == 0) {
    $result = $con->query("INSERT INTO `request`(`request_id`, `from_user_id`, `for_dog_id`, `symptoms`, `place_id`, `duedate`, `request_type`, `bloodstore_id`, `created_time`, `amount_volume`) "
            . "VALUES (null,'$user_id','$dog_id','$symptoms','$place_id','$duedate',2,'',now(),'$volume')");
    if ($con->error == '') {
        $result1 = 2;
    } else {
        $result1 = 0;
    }
} else {
    $data = $result->fetch_assoc();
    $hospitaluser_id = $data['hospitaluser_id'];
    $bloodstroe_id = $data['bloodstore_id'];
    $result = $con->query("SELECT * FROM hospital_user hu JOIN hospital h ON hu.hospital_id=h.hospital_id "
            . "WHERE hu.hospital_userid = '$hospitaluser_id'");
    echo $con->error;
    $data = $result->fetch_assoc();
    $hospital_id = $data['hospital_id'];
    $hospital_name = $data['hospital_name'];
    $hospital_address = $data['hospital_address'];
    $hospital_phone = $data['hospital_phone'];
    $hospital_contact = $data['hospital_contact'];
    $result = $con->query("INSERT INTO `request`(`request_id`, `from_user_id`, `for_dog_id`, `symptoms`, `place_id`, `duedate`, `request_type`, `bloodstore_id`, `created_time`, `amount_volume`) "
            . "VALUES (null,'$user_id','$dog_id','$symptoms','$place_id','$duedate',1,'$bloodstroe_id',now(),'$volume')");
    $to_user_id = $user_id;
    $message = "มีเลือดที่".$hospital_name." โทรศัพท์ติดต่อ ".$hospital_phone." ที่อยู่ " .$hospital_address;
    $queryUser = $con->query("INSERT INTO `pm`(`message_id`, `from_user_id`, `to_user_id`, "
            . "`message`, `message_time`) "
            . "VALUES (null, '0','$to_user_id','$message',now())");
    if ($con->error == '') {
        $result1 = 1;
    } else {
        $result1 = 0;
    }
}

$response = array(
    "result" => $result1
);
echo json_encode($response);
?>

