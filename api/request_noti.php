<?php

//เมื่อกด request จะnoti ไปยังคนใช้แอพคนอื่นๆ สมมุตขอเลือด 7 ทุกคนในระบบที่มีเลือด 7 ก้จะได้รับ noti นั้น  กดเปิด noti ดูจะเป็นรายละเอียดของ
//request นั้น user จะกด donate หรือไม่ก้ได้ ถ้ากดก้โยนไปหน้า donate 
include "../include/functions.php";
include "../include/dbcon.inc.php";
header('Content-Type: application/json');
$user_id = getUserIdFromToken($con, @$_POST["token"]);
$request_id = $con->real_escape_string(@$_POST["request_id"]);

if ($user_id != 0) {
    $res = $con->query("SELECT ud.dog_name , ud.dog_bloodtype_id FROM `request` r
JOIN user_dog ud ON ud.dog_id = r.for_dog_id
WHERE r.request_id = '$request_id'");
}
?>
