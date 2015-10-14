<?php
function getAllUserSettings($user_id,$con){
    $types = array("request","pm","requester","donator");
    $types_text = array("ประกาศขอเลือด","ข้อความส่วนตัว","การขอรับเลือด","สถานะผู้บริจาค");
    $settings = array();
    foreach ($types as $i => $type) {
        $setting = array(
            "type"=>$type,
            "type_text"=>$types_text[$i],
            "value"=>  getUserSettings($user_id, $type, $con)
        );
        array_push($settings,$setting);
    }
    return $settings;
}

function getUserSettings($user_id, $type, $con) {
    $res = $con->query("SELECT * FROM user_settings WHERE user_id = '$user_id' AND type = '$type'");
    if ($res->num_rows == 0) {
        return 0;
    } else {
        $data = $res->fetch_assoc();
        if ($data["value"] == "1") {
            return 1;
        } else {
            return 0;
        }
    }
}

function setUserSetting($user_id, $type, $value,$con) {
    $res = $con->query("SELECT * FROM user_settings WHERE user_id = '$user_id' AND type = '$type'");
    if ($res->num_rows == 0) {
        $con->query("INSERT INTO `user_settings`(`usersetting_id`, `user_id`, `type`, `value`, `last_update`) "
                . "VALUES (null,'$user_id','$type','$value',now())");
    } else {
        $con->query("UPDATE user_settings SET value = '$value' WHERE user_id = '$user_id' AND type = '$type'");
    }
    if($con->error == ""){
        return 1;
    }else{
        return 0;
    }
}