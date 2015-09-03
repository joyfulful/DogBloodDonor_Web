<?php
include "session.inc.php";
include "../dbcon.inc.php";
$admin_id = $_SESSION["userdata"]["admin_id"];
$group_id = $con->real_escape_string($_POST["group"]);
$name = $con->real_escape_string($_POST["name"]);
$date = $con->real_escape_string($_POST["date"]);
$reftext = $con->real_escape_string($_POST["reftext"]);
$refurl = $con->real_escape_string($_POST["refurl"]);
$data = $_POST["data"];

if (isset($_POST["editid"])) {
    //edit
    $article_id = $con->real_escape_string($_POST["editid"]);
    $target_dir = "articleimg/";
    $target_file = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
// Check if image file is a actual image or fake image
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }

// Check file size
    if ($_FILES["image"]["size"] > 50000000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
// Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
// Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            //echo "The file ". basename( $_FILES["image"]["name"]). " has been uploaded.";
        } else {
            //echo "Sorry, there was an error uploading your file.";
        }
    }

    $image = time() . "_" . basename($_FILES["image"]["name"]);

    if ($uploadOk) {
        $con->query("UPDATE `article_data` SET `group_id`='$group_id',`article_name`='$name',"
                . "`article_date`='$date',`article_text`='$data',`article_image`='$image',"
                . "`article_ref`='$reftext',`article_ref_link`='$refurl', last_updated = now() WHERE article_id = '$article_id'");
        if ($con->error == "") {
            echo "Insert OK ";
            ?>
            <script>window.top.location.href = "article_manage.php#group<?=$group_id?>";</script> 
            <?php

        } else {
            echo "Insert ERROR : " . $con->error;
        }
    } else {
        $con->query("UPDATE `article_data` SET `group_id`='$group_id',`article_name`='$name',"
                . "`article_date`='$date',`article_text`='$data',"
                . "`article_ref`='$reftext',`article_ref_link`='$refurl', last_updated = now() WHERE article_id = '$article_id'");
        if ($con->error == "") {
            echo "Insert OK ";
            ?>
            <script>window.top.location.href = "article_manage.php#group<?=$group_id?>";</script> 
            <?php

        } else {
            echo "Insert ERROR : " . $con->error;
        }
    }
} else {
//new insert
    $target_dir = "articleimg/";
    $target_file = $target_dir . time() . "_" . basename($_FILES["image"]["name"]);
    $uploadOk = 1;
    $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);
// Check if image file is a actual image or fake image
    if (isset($_POST["submit"])) {
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        if ($check !== false) {
            echo "File is an image - " . $check["mime"] . ".";
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }
    }

// Check file size
    if ($_FILES["image"]["size"] > 500000) {
        echo "Sorry, your file is too large.";
        $uploadOk = 0;
    }
// Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        $uploadOk = 0;
    }
// Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            //echo "The file ". basename( $_FILES["image"]["name"]). " has been uploaded.";
        } else {
            //echo "Sorry, there was an error uploading your file.";
        }
    }

    $image = time() . "_" . basename($_FILES["image"]["name"]);
    $con->query("INSERT INTO `article_data`(`article_id`, `group_id`, `article_name`, `article_date`, "
            . "`article_text`, `article_image`, `article_viewcount`, `article_ref`, "
            . "`article_ref_link`, add_by_admin_id, last_updated) VALUES "
            . "('null','$group_id','$name','$date','$data','$image','0','$reftext','$refurl','$admin_id', '0000-00-00 00:00:00')");
    if ($con->error == "") {
        echo "Insert OK ";
        ?>
        <script>window.top.location.href = "article_manage.php#group<?=$group_id?>";</script> 
        <?php

    } else {
        echo "Insert ERROR : " . $con->error;
    }
}
?>