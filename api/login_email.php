<?php

header('Content-Type: application/json');
include "../include/dbcon.inc.php";
include "../include/functions.php";
$email = $con->real_escape_string(@$_POST["email"]);
$password = md5(@$_POST["password"]);
$res = $con->query("SELECT * FROM user WHERE email = '$email' AND user_type = 'ma'");
if ($res->num_rows == 1) {
    $data = $res->fetch_assoc();
    if ($data["password"] == $password) {
		if ($data["user_role"] == "1" && $data["activate_status"] == "1") {
            //Username & Password are Correct 
            //Create Session
            $userid = $data["user_id"];
            $token = getToken(30);
            $ip = $con->real_escape_string(get_client_ip());
            $con->query("INSERT INTO `user_session`(`session_id`, `token`, `user_id`, `isvalid`, `ip`, `created`, `destroy`)"
                    . " VALUES (null,'$token','$userid',1,'$ip',now(),now())");            
            $response = array(
                "result" => 1,
                "token" => $token
            );
        } else {
            //Not Allow Log into App
            $response = array(
                "result" => 0,
                "reason" => "ผู้ใช้นี้ไม่สามารถเข้าใช้งาน Application ได้"
            );
        }
    } else {
        //Username is Correct, Password is Wrong
        $response = array(
            "result" => 0,
            "reason" => "รหัสผ่านผิด"
        );
    }
} else {
    $response = array(
        "result" => 0,
        "reason" => "อีเมล์ผิด"
    );
}
echo json_encode($response);