<?php

function getLastMessage($user_id1, $user_id2, $con) {
    $res = $con->query("SELECT * FROM pm WHERE "
            . "(from_user_id = '$user_id1' AND to_user_id = '$user_id2') "
            . "OR "
            . "(from_user_id = '$user_id2' AND to_user_id = '$user_id1') "
            . "ORDER BY message_time DESC LIMIT 1");
    return $res->fetch_assoc();
}

function checkIsRead($user_id, $message_id, $con) {
    $res = $con->query("SELECT * FROM pm_read WHERE message_id = '$message_id'");
    if ($res->num_rows > 0) {
        return true;
    } else {
        return false;
    }
}

function getRecipentsUserIdByUserId($user_id, $con) {
    $user_ids = array();
    $sendtores = $con->query("SELECT * FROM pm WHERE from_user_id = '$user_id' OR to_user_id = '$user_id'");
    while ($data = $sendtores->fetch_assoc()) {
        $userid1 = $data["from_user_id"];
        $userid2 = $data["to_user_id"];
        if (!in_array($userid1, $user_ids) & $user_id != $userid1) {
            array_push($user_ids, $userid1);
        }
        if (!in_array($userid2, $user_ids) & $user_id != $userid2) {
            array_push($user_ids, $userid2);
        }
    }
    sort($user_ids);
    return $user_ids;
}

function getUserDetail($user_id, $con) {
    if ($user_id != 0) {
        $res = $con->query("SELECT user.email as email, user_profile.firstname as fname, user_profile.lastname as lname, user_profile.user_image
                        FROM user 
                        LEFT JOIN user_profile ON user.user_id = user_profile.user_id
                         WHERE user.user_id = '$user_id'");
        $data = $res->fetch_assoc();
        $userdata = array(
            "user_id" => $user_id,
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
    return $userdata;
}

function getThread($user_id, $con) {
    $threads = array();
    $recipents = getRecipentsUserIdByUserId($user_id, $con);
    foreach ($recipents as $recipent) {
        $lastmsg = getLastMessage($user_id, $recipent, $con);
        $userdetail = getUserDetail($recipent, $con);
        $isRead = checkIsRead($user_id, $lastmsg["message_id"], $con);
        
        $thai_month_short_arr = Array("", "ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค.");

        $time = strtotime($lastmsg["message_time"]);
        $timeoutput = date("j", $time) . " " . $thai_month_short_arr[date("n", $time)] . " ".(date("Y", $time)+543)." , " . date("G:i", $time);
        $thread = array(
            "user" => $userdetail,
            "last_message_id" => $lastmsg["message_id"],
            "last_message" => $lastmsg["message"],
            "last_message_time" => $timeoutput,
            "last_message_realtime" => $lastmsg["message_time"],
            "isRead" => $isRead
        );
        array_push($threads, $thread);
    }
    usort($threads, 'date_compare');
    return $threads;
}

function date_compare($a, $b) {
    $t1 = strtotime($a['last_message_realtime']);
    $t2 = strtotime($b['last_message_realtime']);
    return $t2 - $t1;
}

function sendMessage($from_user_id, $to_user_id, $message, $con) {
    $con->query("INSERT INTO `pm`(`message_id`, `from_user_id`, `to_user_id`, "
            . "`message`, `message_time`) "
            . "VALUES (null, '$from_user_id','$to_user_id','$message',now())");
    $user = getUserDetail($from_user_id, $con);
    pushToUser($to_user_id, "Dog Blood Donor PM", $user["firstname"] . ": " . $message, "pm", $from_user_id, $con);
    if ($con->error == "") {
        return 1;
    } else {
        return 0;
    }
}
