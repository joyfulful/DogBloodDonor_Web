<?php

header('Content-Type: application/json');
include "../../include/dbcon.inc.php";
include "../../include/functions.php";
$result = array(
    "result" => 0
);
$code = $con->real_escape_string(@$_POST["code"]);
$res = $con->query("SELECT * FROM user WHERE forgot_code = '$code'");
if ($res->num_rows == 1) {
    $data = $res->fetch_assoc();
    $result = array(
        "result" => 1,
        "email" => $data["email"]
    );
}
echo json_encode($result);