<?php

header('Content-Type: application/json');
include "../../include/functions.php";
include "../../include/dbcon.inc.php";
include "../../include/settings_functions.inc.php";
$user_id = getUserIdFromToken($con, @$_POST["token"]);

$response = array(
    "result" => 0
);

if ($user_id != 0) {
    $type = $con->real_escape_string(@$_POST["type"]);
    $value = $con->real_escape_string(@$_POST["value"]);
    if ($type != "" & $value != "") {
        if (setUserSetting($user_id, $type, $value,$con)) {
            $response = array(
                "result" => 1
            );
        }
    } else {
        $response = array(
            "result" => 0,
            "errortext" => "No Type, Value Recieved"
        );
    }
}
echo json_encode($response);
