<?php
header('Content-Type: application/json');
include "../../include/dbcon.inc.php";
include "../../include/functions.php";
$code = $con->real_escape_string(@$_POST["code"]);
$res = $con->query("SELECT * FROM user WHERE forgot_code = '$code'");
if ($res->num_rows == 1) {
    $data = $res->fetch_assoc();
    $user_id = $data["user_id"];
    $password = md5($_POST["password"]);
    $con->query("UPDATE user SET forgot_code = '', password = '$password' WHERE user_id = '$user_id'");
    $result = array(
        "result"=>1
    );
} else {
    $result = array(
        "result"=>0
    );
}
echo json_encode($result);
?>