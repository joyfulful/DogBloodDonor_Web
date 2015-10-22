<?php

include "../../include/functions.php";
include "../../include/dbcon.inc.php";
header('Content-Type: application/json');

$from_user_id = getUserIdFromToken($con, @$_POST["token"]);
$to_user_id = $con->real_escape_string($_POST["to_user_id"]);
$lastmessage_id = $con->real_escape_string(@$_POST["lastmessage_id"]);
$queryUser = $con->query("SELECT * FROM pm WHERE ((from_user_id = '$from_user_id' and to_user_id = '$to_user_id') "
        . "OR (from_user_id = '$to_user_id' and to_user_id = '$from_user_id')) and message_id > '$lastmessage_id' "
        . "ORDER BY message_time DESC "
        . "LIMIT 30");
$messagearr = array();
while ($data = $queryUser->fetch_assoc()) {
    $message_id = $data["message_id"];
    $findRead = $con->query("SELECT * FROM pm_read WHERE message_id = '$message_id'");
    if ($findRead->num_rows == 0) {
        //find message to whom
        $findmsg = $con->query("SELECT * FROM pm WHERE message_id = '$message_id'");
        $msgdata = $findmsg->fetch_assoc();
        $msgto = $msgdata["to_user_id"];
        if($msgto == $from_user_id){
        $con->query("INSERT INTO `pm_read`(`pm_read_id`, `message_id`, `message_read_time`) "
                . "VALUES (null,'$message_id',now())");
        }
    }
    
    $time = strtotime($data["message_time"]);
    $timeoutput = date("j",$time)." ".$thai_month_short_arr[date("n",$time)]." ".(date("Y", $time)+543)." , ".date("G:i",$time);
    $message = array(
        "user_id" => $data["from_user_id"],
        "message_id" => $data["message_id"],
        "message" => $data["message"],
        "message_time" => $timeoutput
    );
    array_push($messagearr, $message);
}
echo json_encode($messagearr);
?>