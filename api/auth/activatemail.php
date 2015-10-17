<?php

include "../../include/functions.php";
include "../../include/dbcon.inc.php";
$code = $con->real_escape_string($_POST["code"]);

$res = $con->query("SELECT * FROM user WHERE activate_code = '$code'");
$message = "";
$result = 0;
if ($res->num_rows == 1) {
    $data = $res->fetch_assoc();
    if ($data["activate_status"] == "1") {
        $result = 2;
        $message = "You have already activate !";
    } else {
        $user_id = $data["user_id"];
        $con->query("UPDATE user SET activate_status = 1 WHERE user_id = '$user_id'");
        $message = "Activation Successful";
        $result = 1;
    }
} else {
    $message = "Actvation Error : Code Not Found !";
    $result = 0;
}
$response = array(
    "result" => $result,
    "message" => $message
);
echo json_encode($response);
?>