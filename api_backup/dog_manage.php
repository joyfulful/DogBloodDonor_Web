<?php

header('Content-Type: application/json');
include "../include/dbcon.inc.php";
include "../include/functions.php";
$result = 0;
$errortext = "";
if ($_POST["isNewDog"] == "1") {
    //Add New Dog
    $user_id = getUserIdFromToken($con, @$_POST["token"]);
    $breeds_id = $con->real_escape_string($_POST["breeds_id"]);
    $newbreeds_name = $con->real_escape_string(@$_POST["newbreeds_name"]);
    $dog_name = $con->real_escape_string($_POST["dog_name"]);
    $dog_gender = $con->real_escape_string($_POST["dog_gender"]);
    $dog_birthyear = $con->real_escape_string($_POST["dog_birthyear"]);
    $bloodtype_id = $con->real_escape_string($_POST["bloodtype_id"]);
    $dog_weight = $con->real_escape_string($_POST["dog_weight"]);
    $disease_id = $con->real_escape_string($_POST["disease_id"]);
    if ($breeds_id == "0") {
        $res = $con->query("INSERT INTO dog_breeds values(null,'$newbreeds_name')");
        $breeds_id = $con->insert_id;
    }
    $res = $con->query("INSERT INTO `user_dog`(`dog_id`, `user_id`, `breeds_id`, `dog_name`, `dog_gender`,"
            . " `dog_birthyear`, `dog_bloodtype_id`,`disease_id`, `dog_weight`, `dog_image`, `dog_status`) "
            . "VALUES (null,'$user_id','$breeds_id','$dog_name','$dog_gender',"
            . "'$dog_birthyear','$bloodtype_id','$disease_id','$dog_weight','',1)");
    $dog_id = $con->insert_id;
    if (isset($_FILES["dog_image"])) {
        $target_dir = "dogimage/";
        $target_file = $target_dir . $dog_id . "_" . $_FILES["dog_image"]["name"];
        $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            //save image error
        } else {
            if (move_uploaded_file($_FILES["dog_image"]["tmp_name"], $target_file)) {
                //save image success
                $filename = $con->real_escape_string($dog_id . "_" . $_FILES["dog_image"]["name"]);
                $con->query("UPDATE user_dog SET dog_image = '$filename' WHERE dog_id = '$dog_id'");
            }
        }
    }
    if ($res == 1) {
        $result = 1;
    } else {
        $result = 0;
        $errortext = "Insert Error";
    }
} else {
    //Edit Old Dog
    $dog_id = $con->real_escape_string($_POST["dog_id"]);
    $user_id = getUserIdFromToken($con, @$_POST["token"]);
    $breeds_id = $con->real_escape_string($_POST["breeds_id"]);
    $newbreeds_name = $con->real_escape_string(@$_POST["newbreeds_name"]);
    $dog_name = $con->real_escape_string($_POST["dog_name"]);
    $dog_gender = $con->real_escape_string($_POST["dog_gender"]);
    $dog_birthyear = $con->real_escape_string($_POST["dog_birthyear"]);
    $disease_id = $con->real_escape_string($_POST["disease_id"]);
    $bloodtype_id = $con->real_escape_string($_POST["bloodtype_id"]);
    $dog_weight = $con->real_escape_string($_POST["dog_weight"]);
    $check = $con->query("SELECT * FROM user_dog WHERE dog_id = '$dog_id' AND user_id = '$user_id' AND dog_status = 1");
    if ($check->num_rows == 1) {
        if ($breeds_id == "0") {
            $res = $con->query("INSERT INTO dog_breeds values(null,'$newbreeds_name')");
            $breeds_id = $con->insert_id;
        }
        $res = $con->query("UPDATE `user_dog` SET `breeds_id`='$breeds_id',"
                . "`dog_name`='$dog_name',`dog_gender`='$dog_gender',`dog_birthyear`='$dog_birthyear',"
                . "`dog_bloodtype_id`='$bloodtype_id',`disease_id`='$disease_id',`dog_weight`='$dog_weight'"
                . " WHERE `dog_id`='$dog_id'");
        if (isset($_FILES["dog_image"])) {
            $target_dir = "dogimage/";
            $target_file = $target_dir . $dog_id . "_" . $_FILES["dog_image"]["name"];
            $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
            if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                //save image error
            } else {
                if (move_uploaded_file($_FILES["dog_image"]["tmp_name"], $target_file)) {
                    //save image success
                    $filename = $con->real_escape_string($dog_id . "_" . $_FILES["dog_image"]["name"]);
                    $con->query("UPDATE user_dog SET dog_image = '$filename' WHERE dog_id = '$dog_id'");
                }
            }
        }
        if ($res == 1) {
            $result = 1;
        }
    } else {
        $result = 0;
        $errortext = "Dog Not Found !";
    }

    if ($res == 1) {
        $result = 1;
    } else {
        $result = 0;
        $errortext = "Insert Error";
    }
}

$response = array(
    "result" => $result,
    "message" => $errortext
);
echo json_encode($response);
?>