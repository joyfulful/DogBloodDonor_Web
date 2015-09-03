<?php
include "../../include/functions.php";
include "../../include/dbcon.inc.php";
$code = $con->real_escape_string($_GET["code"]);

$res = $con->query("SELECT * FROM user WHERE activate_code = '$code'");

if($res->num_rows == 1){
    $data = $res->fetch_assoc();
    if($data["activate_status"] == "1"){
        echo "You have already activate !";
    }else{
        $user_id = $data["user_id"];
        $con->query("UPDATE user SET activate_status = 1 WHERE user_id = '$user_id'");
        echo "Activation Successful";
    }
}else{
    echo "Actvation Error : Code Not Found !";
}