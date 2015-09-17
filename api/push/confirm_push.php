<?php
date_default_timezone_set('Asia/Bangkok');
echo "Running Confirm Push Script at : " . date('l jS \of F Y h:i:s A') . "\n";
include "../../include/dbcon.inc.php";
include "../push/AndroidPusher/Pusher.php";
$apiKey = "AIzaSyBUyZGXH7HDm41X-IxREop0IR8fJNsO7-w";
$findrequest = $con->query("SELECT * FROM request WHERE date(duedate) < date(now() + INTERVAL 7 DAY) "
        . "AND request_id NOT IN (SELECT request_id FROM donate WHERE donate_status = 1 OR donate_status = 2)");
if ($findrequest->num_rows > 0) {
    echo "Found " . $findrequest->num_rows . " Requests to push\n";
    while ($data = $findrequest->fetch_assoc()) {
        $user_id = $data["from_user_id"];
        $request_id = $data["request_id"];

        echo "Pushing to userid=" . $user_id . " from request_id=" . $request_id . " device_count=";

        $findRegisId = $con->query("SELECT * FROM user_deviceid WHERE user_id = '$user_id'");
        echo $findRegisId->num_rows."\n";
        while ($regisid = $findRegisId->fetch_assoc()) {
            $regId = $regisid["device_id"];
            $pusher = new AndroidPusher\Pusher($apiKey);
            $pusher->notify($regId, "กรุณายืนยันการขอเลือดสุนัขของคุณ", "แจ้งเตือนการยืนยันผู้บริจาค", "confirmalert", $request_id);
            $output = $pusher->getOutputAsArray();
            echo "success=".$output["success"]." failure=".$output["failure"]."\n";
        }
    }
} else {
    echo "No Request Found\n";
}
echo "Program Finish at : " . date('l jS \of F Y h:i:s A') . "\n";

