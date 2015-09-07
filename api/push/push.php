<?php
include "./AndroidPusher/Pusher.php";
// https://code.google.com/apis/console/
$apiKey = "AIzaSyBUyZGXH7HDm41X-IxREop0IR8fJNsO7-w";
$regId = "APA91bFA5nCWDcDVbaUfdG0BsSZ2edAJyc48lzU4fl0e-JE9OJ47A-yZTQDaxjwWLptSVUelEMI_MaQEkZCgz5LjRkQKDNdAUIMUStjE_bMK_CcHf6MPRVaNdMuwdB6Us4qMe0yhei2h";

$pusher = new AndroidPusher\Pusher($apiKey);
$pusher->notify($regId, "Chakree : หิวจังงเลยยย");

print_r($pusher->getOutputAsArray());