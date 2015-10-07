<?php

header('Content-Type: application/json');
include "../include/dbcon.inc.php";
include "../include/functions.php";
$email = $con->real_escape_string(@$_POST["email"]);

$res = $con->query("SELECT * FROM user WHERE email = '$email' AND user_type = 'ma'");
if ($res->num_rows == 1) {
    $data = $res->fetch_assoc();
    $user_id = $data["user_id"];
    $forgot_code = getToken(6);
    include "../include/emailfunctions.php";
    $con->query("UPDATE user SET forgot_code = '$forgot_code' WHERE user_id = '$user_id'");
    $result = sendForgotmail($forgot_code, $email);
    if ($result == 1) {
        $response = array(
            "status" => 1
        );
    }
} else {
    $response = array(
        "status" => 0,
        "errortext" => "ไม่พบอีเมล์นี้ในระบบ"
    );
}
echo json_encode($response);
