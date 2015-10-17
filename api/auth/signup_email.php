<?php
header('Content-Type: application/json');
include "../../include/dbcon.inc.php";
include "../../include/functions.php";
$email = $con->real_escape_string(@$_POST["email"]);
$password = md5(@$_POST["password"]);
$res = $con->query("SELECT * FROM user WHERE email = '$email' AND user_type = 'ma'");
if ($res->num_rows == 0) {
    //User is okay to register
    $activate_code = getToken(6);
    include "../../include/emailfunctions.php";
    sendActivatemail($activate_code,$email);
    $res1 = $con->query("INSERT INTO `user`( `user_fbid`, `password`, `email`, `register_date`, "
            . "`user_type`, `user_role`, activate_status, activate_code)"
            . " VALUES ('','$password','$email',now(),'ma','1','0','$activate_code')");
    
    $lastid = $con->insert_id;
    $res2 = $con->query("INSERT INTO `user_profile`(user_id)"
                . " VALUES ('$lastid')");
    
    //Create Session
    $userid = $lastid;
    $token = getToken(30);
    $ip = $con->real_escape_string(get_client_ip());
    $con->query("INSERT INTO `user_session`(`session_id`, `token`, `user_id`, `isvalid`, `ip`, `created`, `destroy`)"
            . " VALUES (null,'$token','$userid',1,'$ip',now(),now())");
    $response = array(
        "result" => 1,
        "token" => $token
    );
} else {
    $response = array(
        "result" => 0,
        "reason" => "คุณได้สมัครสมาชิกไปแล้ว โปรดเข้าสู่ระบบในหน้าแรกค่ะ"
    );
}
echo json_encode($response);