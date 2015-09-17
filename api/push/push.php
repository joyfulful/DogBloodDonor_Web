<?php
include "./AndroidPusher/Pusher.php";
// https://code.google.com/apis/console/
$apiKey = "AIzaSyBUyZGXH7HDm41X-IxREop0IR8fJNsO7-w";
$regId = "APA91bF5ZShSAfvZJgqsxIuJG3cgHIg0Sv5wrEiCrL5ptqakLNry3Nz4x8mGzj8h-6xs8uxSb1q7_e5r4npyUkRY1nmgkbrWp3eAv9kwLATQVEEos1AlwEnqgg8x2tacJN4Rw4WytsnL";

$pusher = new AndroidPusher\Pusher($apiKey);
//$pusher->notify($regId, "ทดสอบ","New Message","ว้ากกก","request","1");
$pusher->notify($regId, "มีสุนัขต้องการเลือดหมู DEA 1.1 ด่วน !", "แจ้งเตือนการขอเลือด", "newrequest", 1);


print_r($pusher->getOutputAsArray());