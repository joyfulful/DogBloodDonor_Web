<?php

session_start();
header('Content-Type: application/json');
include "../include/config.php";
include "../include/functions.php";
require ("../vendor/facebook-php-sdk/autoload.php");
$appid = "835322439838787";
$appsecret = "53980267a9b75a90936ce42bd79f1f44";

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;

FacebookSession::setDefaultApplication($appid, $appsecret);

$fbtoken = @$_POST["fbtoken"];
$session = new FacebookSession($fbtoken);
try {
    if (!$session->validate()) {
        $session = null;
    }
} catch (Exception $e) {
    $session = null;
}

if ($session != null) {
    //echo "Connect to FB";
    $request = new FacebookRequest($session, 'GET', '/me');
    $response = $request->execute();
    $result = $response->getGraphObject()->asArray();
    include "../include/dbcon.inc.php";
    $fbid = $con->real_escape_string($result["id"]);
    //$email = $result["email"];
    //$name = $result["name"];
    //echo "Your name is : ".$name;
    //echo "<br>Your Email is : ".$email;
    //echo "<br>Your id is : ".$fbid;
    //connect db insert data
    $res = $con->query("SELECT * FROM user WHERE user_fbid = '$fbid' AND user_type = 'fb'");
    if ($res->num_rows == 1) {
        $data = $res->fetch_assoc();
        if ($data["user_role"] == "1") {
            //Create Session
            $userid = $data["user_id"];
            $token = getToken(30);
            $ip = $con->real_escape_string(get_client_ip());
            $con->query("INSERT INTO `user_session`(`session_id`, `token`, `user_id`, `isvalid`, `ip`, `created`, `destroy`)"
                    . " VALUES (null,'$token','$userid',1,'$ip',now(),now())");
            $response2 = array(
                "result" => 1,
                "token" => $token
            );
        }
    } else {
        $response2 = array(
                "result" => 0,
                "reason" => "ยังไม่ได้สมัครสมาชิก"
            );
    }
    echo json_encode($response2);
}