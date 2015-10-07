<?php

header('Content-Type: application/json');
include "../../include/dbcon.inc.php";
include "../../include/functions.php";
include "../../include/pm_functions.inc.php";
include "../../include/push_functions.inc.php";
$user_id = getUserIdFromToken($con, @$_POST["token"]);
$dog_id = $con->real_escape_string($_POST["dog_id"]);
$symptoms = $con->real_escape_string($_POST["symptoms"]);
$place_id = $con->real_escape_string($_POST["place_id"]);
$duedate = $con->real_escape_string($_POST["duedate"]);
$volume = $con->real_escape_string($_POST["volume"]);

$user = getUserById($user_id, $con);
$dog = getDogById($dog_id, $con);
$bloodtype = getBloodTypeById($dog["dog_bloodtype_id"], $con);
$bloodstores = getBloodStoreByBloodTypeId($dog["dog_bloodtype_id"], $con);

$result = 0;

if (sizeof($bloodstores) > 0) {
    //Found Blood In Hospital Blood Store
    //create bloodstore_id string
    $bloodstore_ids = array();
    foreach ($bloodstores as $key => $bloodstore) {
        array_push($bloodstore_ids, $bloodstore["bloodstore_id"]);
    }
    $bloodstore_id = implode(",", $bloodstore_ids);
    $con->query("INSERT INTO `request`(`request_id`, `from_user_id`, `for_dog_id`, `symptoms`, `place_id`,"
            . " `duedate`, `request_type`, `bloodstore_id`, `created_time`, `amount_volume`) "
            . "VALUES (null,'$user_id','$dog_id','$symptoms','$place_id','$duedate',1,'$bloodstore_id',now(),'$volume')");
    //save to db (request_type = 1)
    $senthospital_id = array(); //store sent hospital id
    $message = "การขอรับเลือดของ" . $dog["dog_name"] . " เมื่อ " . date("j") . " " . $thai_month_short_arr[date("n")] . " " . (date("Y") + 543) . " " .
            "พบเลือดกรุ๊ป " . $bloodtype["bloodtype_name"] . " ที่โรงพยาบาลดังนี้";
    foreach ($bloodstores as $key => $bloodstore) {
        if (!in_array($bloodstore["hospital_id"], $senthospital_id)) {
            array_push($senthospital_id, $bloodstore["hospital_id"]);
            //sent pm to user
            $hospital = getHospitalById($bloodstore["hospital_id"], $con);
            $message.= "<br><br><b>" . sizeof($senthospital_id) . ". " . $hospital["hospital_name"] . "</b><br>"
                    . "โทรศัพท์ติดต่อ : " . $hospital["hospital_phone"] . "<br>ที่อยู่ : " . $hospital["hospital_address"];
        }
    }
    sendMessage(0, $user_id, $message, $con); // call pm functions
    if ($con->error == "") {
        $result = 1;
    }
} else {
    //Create Request
    //save to db (request_type = 2)
    $con->query("INSERT INTO `request`(`request_id`, `from_user_id`, `for_dog_id`, `symptoms`, "
            . "`place_id`, `duedate`, `request_type`, `bloodstore_id`, `created_time`, `amount_volume`) "
            . "VALUES (null,'$user_id','$dog_id','$symptoms','$place_id','$duedate',2,'',now(),'$volume')");
    $request_id = $con->insert_id;
    $bloodtype_id = $dog["dog_bloodtype_id"];
    //find users that have dog that have this bloodtype_id
    if ($bloodtype_id == "1" | $bloodtype_id == "2") {
        $findUsers = $con->query("SELECT DISTINCT user_id FROM user_dog WHERE dog_bloodtype_id NOT IN (1,2)");
    } else {
        $findUsers = $con->query("SELECT DISTINCT user_id FROM user_dog WHERE dog_bloodtype_id = '$bloodtype_id'");
    }
    while ($userstopush = $findUsers->fetch_array()) {
        $user_id_to_push = $userstopush[0];
        if ($user_id != $user_id_to_push) {
            pushToUser($user_id_to_push, "แจ้งเตือนการขอเลือด", "มีสุนัขต้องการเลือดหมู่ " . $bloodtype["bloodtype_name"] . " ด่วน !", "request", $request_id, $con);
        }
    }
    if ($con->error == "") {
        $result = 2;
    }
}
$response = array(
    "result" => $result
);
echo json_encode($response);
?>