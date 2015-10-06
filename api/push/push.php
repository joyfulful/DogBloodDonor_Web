<?php

include "./AndroidPusher/Pusher.php";
// https://code.google.com/apis/console/
$apiKey = "AIzaSyBUyZGXH7HDm41X-IxREop0IR8fJNsO7-w";

$regIds = array("APA91bHtMGAicjc7AxEmg3uenx0QmgfFpfzPuNRtNGJs20qmTHp15ZNYY5TSNq-eykwIOsB-4ES7aDe2LGbWp9vuGDxSce6mENz-ob-IagfONRi1FOaH68wxgvgjCS-WU9WsvdyqhAyF",
    "APA91bGAXS3kGz-QXVeCUDoVTKxSnCzoUTtdZzrf2HM5SoylsJTpQlykD9sIDA2yFyYWuem7TXvTPwcQ1onUVKFuNKXFh6z9XwZgf-sEyT8aZit1O_NQ4WJ_ZBGTIw2TnfNzMkiL0tRY",
    "APA91bHdVhPkgp1GqdkssRhJsv_McpnjMPbHjIWaMKKrinP5GNQe8XvcmUkN8fqTIjUIARDrAQI4grgxJcCVKWoD75E7Hi99coGsGVnm7EFyzzHrtSkBKeDyQyb1qvmhfwao_RiujFgz");

//$regId = "APA91bHtMGAicjc7AxEmg3uenx0QmgfFpfzPuNRtNGJs20qmTHp15ZNYY5TSNq-eykwIOsB-4ES7aDe2LGbWp9vuGDxSce6mENz-ob-IagfONRi1FOaH68wxgvgjCS-WU9WsvdyqhAyF";
foreach ($regIds as $i => $regId) {
    $pusher = new AndroidPusher\Pusher($apiKey);
//$pusher->notify($regId, "ทดสอบ","New Message","ว้ากกก","request","1");
    $pusher->notify($regId, "จอยฟูล บี วิว เฮ้", "เด้งดิเว้ยยยยยยยยยย", "newrequest", 1);


    print_r($pusher->getOutputAsArray());
}