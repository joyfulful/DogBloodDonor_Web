<?php

header('Content-Type: application/json');
include "../include/functions.php";
include "../include/dbcon.inc.php";
$user_id = getUserIdFromToken($con, @$_POST["token"]);
if ($user_id != 0) {
    $fname = $con->real_escape_string($_POST["firstname"]);
    $lname = $con->real_escape_string($_POST["lastname"]);
    $house_no = $con->real_escape_string($_POST["houseno"]);
    $sub_district = $con->real_escape_string($_POST["sub_district"]);
    $province = $con->real_escape_string($_POST["province"]);
    $postcode = $con->real_escape_string($_POST["postcode"]);
    $phone = $con->real_escape_string($_POST["telno"]);
    $district = $con->real_escape_string($_POST["district"]);
    
    $con->query("UPDATE user_profile SET firstname = '$fname', lastname = '$lname', "
            . "house_no = '$house_no',"
            . "subdistrict = '$sub_district',"
            . "district = '$district',"
            . "province = '$province ',"
            . "postcode = '$postcode',"
            . "telno = '$phone' "
            . " WHERE user_id = $user_id");
    echo $con->error;
    if (isset($_FILES["user_image"])) {
        $target_dir = "userimage/";
        $target_file = $target_dir . $user_id . "_" . $_FILES["user_image"]["name"];
        $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            //save image error
        } else {
            if (move_uploaded_file($_FILES["user_image"]["tmp_name"], $target_file)) {
                //save image success
                $filename = $con->real_escape_string($user_id . "_" . $_FILES["user_image"]["name"]);
                $con->query("UPDATE user_profile SET user_image = '$filename' WHERE user_id = '$user_id'");
            }
        }
    }
    if ($con->error == "") {
        $response = array(
            "result" => 1
        );
    } else {
        $response = array(
            "result" => 0
        );
    }
}else {
        $response = array(
            "result" => 0,
			"error" => "No Token Found"
        );
    }
echo json_encode($response);