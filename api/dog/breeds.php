<?php
header('Content-Type: application/json');
include "../../include/dbcon.inc.php";
include "../../include/functions.php";

$res = $con->query("SELECT * FROM `dog_breeds`");
$dog = array();
while ($data = $res->fetch_array()) {
    $newdog = array(
        "breeds_id" => $data[0],
        "breeds_name" => $data[1]
    );
    array_push($dog, $newdog);
}
echo json_encode($dog);
?>