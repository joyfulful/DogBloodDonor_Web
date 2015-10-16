<?php

include "../../include/dbcon.inc.php";
header('Content-Type: application/json');

$province_id = $con->real_escape_string(@$_POST["province_id"]);
$subdistrict_id = $con->real_escape_string(@$_POST["subdistrict_id"]);
$district_id = $con->real_escape_string(@$_POST["district_id"]);

$result = array();
if ($province_id == "" & $district_id == "" & $subdistrict_id == "") {
    //search for province by sending nothing!
    $res = $con->query("SELECT province_id,province_name FROM data_province ORDER BY province_name");
    while ($data = $res->fetch_assoc()) {
        array_push($result, $data);
    }
} else if ($province_id != "" & $district_id == "" & $subdistrict_id == "") {
    //search for district by sending province_id
    $res = $con->query("SELECT district_id, district_name FROM data_district WHERE province_id = '$province_id' ORDER BY district_name");
    while ($data = $res->fetch_assoc()) {
        array_push($result, $data);
    }
} else if ($province_id != "" & $district_id != "" & $subdistrict_id == "") {
    //search for subdistrict by sending province_id and district_id
    $res = $con->query("SELECT subdistrict_id, subdistrict_name FROM data_subdistrict WHERE province_id = '$province_id' AND district_id = '$district_id' ORDER BY subdistrict_name");
    while ($data = $res->fetch_assoc()) {
        array_push($result, $data);
    }
} else if ($province_id != "" & $district_id != "" & $subdistrict_id != "") {
    //search for postcode by sending province_id, district_id and subdistrict_id
    $res = $con->query("SELECT postcode FROM data_postcode WHERE province_id = '$province_id' AND district_id = '$district_id' AND subdistrict_id = '$subdistrict_id' ORDER BY postcode");
    if ($res->num_rows == 0) {
        //if that subdistrict's postcode does not found, query for all distinct postcode for that district instead !
        $res2 = $con->query("SELECT DISTINCT postcode FROM data_postcode WHERE province_id = '$province_id' AND district_id = '$district_id' ORDER BY postcode");
        while ($data2 = $res2->fetch_assoc()) {
            array_push($result, $data2);
        }
    } else {
        while ($data = $res->fetch_assoc()) {
            array_push($result, $data);
        }
    }
}
echo json_encode($result);
