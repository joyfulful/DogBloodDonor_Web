<?php

header('Content-Type: application/json');
include "../include/dbcon.inc.php";
include "../include/functions.php";
include "push/AndroidPusher/Pusher.php";
//ประกาศตัวเเปร ที่รับค่า input มาจาก mobile 
$user_id = getUserIdFromToken($con, @$_POST["token"]);
$dog_id = $con->real_escape_string($_POST["dog_id"]);
$symptoms = $con->real_escape_string($_POST["symptoms"]);
$place_id = $con->real_escape_string($_POST["place_id"]);
$duedate = $con->real_escape_string($_POST["duedate"]);
$volume = $con->real_escape_string($_POST["volume"]);

$resultd = $con->query("SELECT * FROM user_dog "
        . "JOIN blood_type ON user_dog.dog_bloodtype_id = blood_type.bloodtype_id WHERE dog_id = '$dog_id'");
$data = $resultd->fetch_assoc();
$bloodtype_name = $data["bloodtype_name"];
$bloodtype_id = $data['dog_bloodtype_id'];
$result = $con->query("SELECT * FROM hospital_bloodstore hb JOIN hospital_dog hd ON hb.hospital_dogid = hd.hospital_dogid "
        . "WHERE hd.bloodtype_id = '$bloodtype_id' and hb.status = 1 and hb.exp_date > now() ORDER BY hb.exp_date ASC ");
echo $con->error;

if ($result->num_rows == 0) {
    $result = $con->query("INSERT INTO `request`(`request_id`, `from_user_id`, `for_dog_id`, `symptoms`, `place_id`, `duedate`, `request_type`, `bloodstore_id`, `created_time`, `amount_volume`) "
            . "VALUES (null,'$user_id','$dog_id','$symptoms','$place_id','$duedate',2,'',now(),'$volume')");
    if ($con->error == '') {
        $result1 = 2;
        $request_id = $con->insert_id;
        //push to every user device
        //first ... find bloodtype_id from dog_id
        $findBloodType = $con->query("SELECT user_dog.dog_bloodtype_id, blood_type.bloodtype_name FROM user_dog "
                . "JOIN blood_type ON blood_type.bloodtype_id = user_dog.dog_bloodtype_id WHERE dog_id = '$dog_id'");
        $BloodTypedata = $findBloodType->fetch_array();
        $bloodtype_id = $BloodTypedata[0];
        $bloodtype_name = $BloodTypedata[1];
        //find users that have dog that have this bloodtype_id
        if ($bloodtype_id == "1" | $bloodtype_id == "2") {
            $findUsers = $con->query("SELECT user_id FROM user_dog WHERE dog_bloodtype_id != 1 AND dog_bloodtype_id != 2");
        } else {
            $findUsers = $con->query("SELECT user_id FROM user_dog WHERE dog_bloodtype_id = '$bloodtype_id'");
        }
        while ($userstopush = $findUsers->fetch_array()) {
            $user_id = $userstopush[0];
            $findDevId = $con->query("SELECT * FROM user_deviceid WHERE user_id = '$user_id'");
            while ($deviddata = $findDevId->fetch_assoc()) {
                $devid = $deviddata["device_id"];
                $pusher = new AndroidPusher\Pusher();
                $pusher->notify($devid, "มีสุนัขต้องการเลือดหมู่ " . $bloodtype_name . " ด่วน !", "แจ้งเตือนการขอเลือด", "newrequest", $request_id);
            }
        }
    } else {
        $result1 = 0;
    }
} else {
    $lasthospital_id = 0;
    while ($data = $result->fetch_assoc()) {
        $hospitaluser_id = $data['hospitaluser_id'];
        $bloodstroe_id = $data['bloodstore_id'];
        $result2 = $con->query("SELECT * FROM hospital_user hu JOIN hospital h ON hu.hospital_id=h.hospital_id "
                . "WHERE hu.hospital_userid = '$hospitaluser_id'");
        echo $con->error;
        $data2 = $result2->fetch_assoc();
        $hospital_id = $data2['hospital_id'];
        if($lasthospital_id == $hospital_id){
            continue;
        }
        $lasthospital_id = $hospital_id;
        $hospital_name = $data2['hospital_name'];
        $hospital_address = $data2['hospital_address'];
        $hospital_phone = $data2['hospital_phone'];
        $hospital_contact = $data2['hospital_contact'];
        $resultin = $con->query("INSERT INTO `request`(`request_id`, `from_user_id`, `for_dog_id`, `symptoms`, `place_id`, `duedate`, `request_type`, `bloodstore_id`, `created_time`, `amount_volume`) "
                . "VALUES (null,'$user_id','$dog_id','$symptoms','$place_id','$duedate',1,'$bloodstroe_id',now(),'$volume')");
        $to_user_id = $user_id;
        $message = "มีเลือดกรุ๊ป " . $bloodtype_name . " ที่ " . $hospital_name . " โทรศัพท์ติดต่อ " . $hospital_phone . " ที่อยู่ " . $hospital_address;
        $queryUser = $con->query("INSERT INTO `pm`(`message_id`, `from_user_id`, `to_user_id`, "
                . "`message`, `message_time`) "
                . "VALUES (null, '0','$to_user_id','$message',now())");
    }
    if ($con->error == '') {
        $result1 = 1;
        //push message to user's device (optional if bee didn't handel in-app notification)
    } else {
        $result1 = 0;
    }
}

$response = array(
    "result" => $result1
);
echo json_encode($response);
?>

