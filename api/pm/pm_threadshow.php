<?php

include "../../include/functions.php";
include "../../include/dbcon.inc.php";
header('Content-Type: application/json');

$user_id = getUserIdFromToken($con, @$_POST["token"]);
//Test : $user_id = '1';
$messagearr = array();
$queryUserid = $con->query("SELECT DISTINCT to_user_id FROM pm WHERE from_user_id = '$user_id'
UNION
SELECT DISTINCT from_user_id FROM pm WHERE to_user_id = '$user_id'");
while ($user_id_data = $queryUserid->fetch_array()) {
    $thisuser_id = $user_id_data[0];
    $queryMessageDetail = $con->query("SELECT * FROM pm WHERE from_user_id = '$thisuser_id' OR to_user_id = '$thisuser_id' ORDER BY message_time DESC LIMIT 1");
    $messagedata = $queryMessageDetail->fetch_array();
    $message_id = $messagedata["message_id"];
    if ($user_id == $messagedata["from_user_id"]) {
        $isRead = true;
    } else {
        $checkReadRes = $con->query("SELECT * FROM pm_read WHERE message_id = '$message_id'");
        if ($checkReadRes->num_rows == 0) {
            $isRead = false;
        } else {
            $isRead = true;
        }
    }
    //get user details
    if ($thisuser_id != 0) {
        $res = $con->query("SELECT user.email as email, user_profile.firstname as fname, user_profile.lastname as lname, user_profile.user_image
                        FROM user 
                        LEFT JOIN user_profile ON user.user_id = user_profile.user_id
                         WHERE user.user_id = '$thisuser_id'");
        echo $con->error;
        $data = $res->fetch_assoc();
        $userdata = array(
            "user_id" => $thisuser_id,
            "email" => $data["email"],
            "firstname" => $data["fname"],
            "lastname" => $data["lname"],
            "user_image" => $data["user_image"]
        );
    } else {
        $userdata = array(
            "user_id" => 0,
            "email" => "system@chakree.me",
            "firstname" => "ข้อความจากระบบ",
            "lastname" => "",
            "user_image" => ""
        );
    }
    $currentuser = array(
        "user" => $userdata, //ตรงนี้ต้องแก้เป็น user object
        "last_message_id" => $messagedata["message_id"],
        "last_message" => $messagedata["message"],
        "last_message_time" => $messagedata["message_time"],
        "isRead" => $isRead
    );
    array_push($messagearr, $currentuser);
}
usort($messagearr, 'date_compare');
echo json_encode($messagearr);

function date_compare($a, $b) {
    $t1 = strtotime($a['last_message_time']);
    $t2 = strtotime($b['last_message_time']);
    return $t2 - $t1;
}

?>