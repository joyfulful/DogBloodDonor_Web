<?php

include "../include/functions.php";
include "../include/dbcon.inc.php";
header('Content-Type: application/json');
$urgent = array();
$all = array();
$byBloodType = array();

$findUrgent = $con->query("SELECT r.request_id, r.from_user_id as user_id, r.created_time, DATE(r.duedate) as duedate, ud.dog_name, db.breeds_name, bt.bloodtype_name,
	IF(r.duedate <= DATE_SUB(now(),INTERVAL 1 DAY) ,'false','true')
    as isValid
FROM request r 
JOIN user_dog ud ON ud.dog_id = r.for_dog_id
JOIN dog_breeds db ON db.breeds_id = ud.breeds_id
JOIN blood_type bt ON bt.bloodtype_id = ud.dog_bloodtype_id
WHERE r.request_id NOT IN 
	(SELECT DISTINCT request_id FROM donate d WHERE d.donate_status IN('1','2') AND d.request_id = r.request_id) 
AND now() >= DATE_SUB(r.duedate, INTERVAL 3 DAY) ORDER BY r.created_time DESC");

while ($urgentdata = $findUrgent->fetch_assoc()) {
    array_push($urgent, $urgentdata);
}

$findall = $con->query("SELECT r.request_id, r.from_user_id as user_id, r.created_time, DATE(r.duedate) as duedate, ud.dog_name, db.breeds_name, bt.bloodtype_name,
	IF(r.duedate <= DATE_SUB(now(),INTERVAL 1 DAY) ,'false','true')
    as isValid
FROM request r 
JOIN user_dog ud ON ud.dog_id = r.for_dog_id
JOIN dog_breeds db ON db.breeds_id = ud.breeds_id
JOIN blood_type bt ON bt.bloodtype_id = ud.dog_bloodtype_id
WHERE r.request_id NOT IN 
	(SELECT DISTINCT request_id FROM donate d WHERE d.donate_status IN('1','2') AND d.request_id = r.request_id)
    AND r.request_id NOT IN (
    
SELECT r.request_id
FROM request r 
JOIN user_dog ud ON ud.dog_id = r.for_dog_id
JOIN dog_breeds db ON db.breeds_id = ud.breeds_id
JOIN blood_type bt ON bt.bloodtype_id = ud.dog_bloodtype_id
WHERE r.request_id NOT IN 
	(SELECT DISTINCT request_id FROM donate d WHERE d.donate_status IN('1','2') AND d.request_id = r.request_id) 
AND now() >= DATE_SUB(r.duedate, INTERVAL 3 DAY) ORDER BY r.created_time DESC


)
ORDER BY r.created_time DESC");
echo $con->error;
while ($alldata = $findall->fetch_assoc()) {
    array_push($all, $alldata);
}

$findBloodType = $con->query("SELECT * FROM blood_type ORDER BY bloodtype_id ASC");
while ($bloodtype = $findBloodType->fetch_assoc()) {
    $requestObj = array();
    $findByBloodType = $con->query("SELECT r.request_id, r.from_user_id as user_id, r.created_time, DATE(r.duedate) as duedate, ud.dog_name, db.breeds_name, bt.bloodtype_name,
	IF(r.duedate <= DATE_SUB(now(),INTERVAL 1 DAY) ,'false','true')
    as isValid
FROM request r 
JOIN user_dog ud ON ud.dog_id = r.for_dog_id
JOIN dog_breeds db ON db.breeds_id = ud.breeds_id
JOIN blood_type bt ON bt.bloodtype_id = ud.dog_bloodtype_id
WHERE r.request_id NOT IN 
	(SELECT DISTINCT request_id FROM donate d WHERE d.donate_status IN('1','2') AND d.request_id = r.request_id) 
AND bt.bloodtype_id = '" . $bloodtype["bloodtype_id"] . "'  
ORDER BY r.created_time DESC");
    echo $con->error;
    while ($bybloodtypedata = $findByBloodType->fetch_assoc()) {
        array_push($requestObj, $bybloodtypedata);
    }
    $bloodtypeobj = array(
        "bloodtype_id" => $bloodtype["bloodtype_id"],
        "bloodtype_name" => $bloodtype["bloodtype_name"],
        "requests" => $requestObj
    );
    array_push($byBloodType, $bloodtypeobj);
}
echo json_encode(array(
    "urgent" => $urgent,
    "all" => $all,
    "byBloodType" => $byBloodType
));
