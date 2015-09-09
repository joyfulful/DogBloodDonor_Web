<?php
include "./AndroidPusher/Pusher.php";
// https://code.google.com/apis/console/
$apiKey = "AIzaSyBUyZGXH7HDm41X-IxREop0IR8fJNsO7-w";
$regId = "APA91bFLOeBWmuHKiyLZDFqM7-VzpEAeHMMp04pgHA3lf_VokJ-kRKp1e2VezUGGnUnjhSlMvzHBHClMl2M_a11vvyQwEP_BMhkBnK5z_Ib7omF8qdyFi2m7bqcfjdhtnq5hZEHPIsvY";

$pusher = new AndroidPusher\Pusher($apiKey);
$pusher->notify($regId, "ทดสอบ","New Message","ว้ากกก","request","1");

print_r($pusher->getOutputAsArray());