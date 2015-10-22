<?php

header('Content-Type: application/json');
include "../../include/dbcon.inc.php";
include "../../include/functions.php";
$user_id = getUserIdFromToken($con, @$_POST["token"]);
$response = array();
//get user dog list
$dogs = getUserDogByUserId($user_id, $con);
foreach ($dogs as $key => $dog) {
    $isAbleToDonate = true;
    if ($dog["dog_bloodtype_id"] == 1 | $dog["dog_bloodtype_id"] == 2) {
        $isAbleToDonate = false;
    }
    if ($dog["disease_id"] > 2) {
        $isAbleToDonate = false;
    }
    $breeds = getBreedsById($dog["breeds_id"], $con);
    $disease = getDiseaseBloodById($dog["disease_id"], $con);
    $blood = getBloodTypeById($dog["dog_bloodtype_id"], $con);
    //get current donate for dog
    $isDonating = isDogDonatingByDogId($dog["dog_id"], $con);
    $donatedata = array();
    if ($isDonating) {
        $donate = getCurrentDonateByDogId($dog["dog_id"], $con);
        $request = getRequestById($donate["request_id"], $con);
        $requestdog = getDogById($request["for_dog_id"], $con);
        $donators = getDonatorByRequestId($request["request_id"], $con);
        $real = sortRealDonator($donators, calculateDonator($request["amount_volume"]));
        $alt = sortAltDonator($donators, calculateDonator($request["amount_volume"]));
        //find out if user can cancle and calculate the time left in Days
        $duedate = $request["duedate"];
        $createddate = $request["created_time"];
        $reason = "";
        if (sizeof($alt) > 0) {
            //have alt donator
            $now = time(); // or your date as well
            $your_date = strtotime($duedate);
            $datediff = $your_date - $now;
            $daydiff = floor($datediff / (60 * 60 * 24));
            $canCancle = true;
            $reason = "มีผู้บริจาคตัวสำรอง";
            if ($daydiff <= 0) {
                $daydiff = 0;
                $canCancle = false;
                $reason = "ไม่สามารถยกเลิกได้หลังจากวันที่กำหนด";
            }
        } else {
            //no alt donator
            $daydiff = 0;
            $canCancle = false;
            $reason = "ไม่สามารถยกเลิกได้ เพราะไม่มีผู้บริจาคตัวสำรอง";
        }

        $donatedata = array(
            "request_id" => $donate["request_id"],
            "dog_name" => $requestdog["dog_name"],
            "status" => getDonatorStatus($request["request_id"], $dog["dog_id"], $con),
            "due_date"=>  changeFormatDate($duedate),
            "isCancelable" => $canCancle,
            "reason" => $reason,
            "dayLeftToCancel" => $daydiff
        );
    }


    $userres = array(
        "dog_id" => $dog["dog_id"],
        //"breeds_id" => $breeds["breeds_id"],
        //"breeds_name" => $breeds["breeds_name"],
        "dog_name" => $dog["dog_name"],
        //"disease_id" => $disease["disease_id"],
        //"disease_name" => $disease["disease_name"],
        //"bloodtype_id" => $blood["bloodtype_id"],
        //"bloodtype_name" => $blood["bloodtype_name"],
        //"dog_weight" => $dog["dog_weight"],
        //"dog_image" => $dog["dog_image"],
        "isDonating" => $isDonating,
        "donatedata" => $donatedata
    );

    if ($isAbleToDonate) {
        array_push($response, $userres);
    }
}
echo json_encode($response);
/*

  //find current donate data
  $finddonateres = $con->query("SELECT donate_id, dog_id, request_id FROM donate WHERE donate_status = 0 AND dog_id IN"
  . " (SELECT dog_id FROM user_dog WHERE user_id = '$user_id') AND request_id IN"
  . " (SELECT request_id FROM request WHERE request_type = 2)");
  $donate = array();
  while ($finddonatedata = $finddonateres->fetch_array()) {
  $donate_id = $finddonatedata[0];
  $dog_id = $finddonatedata[1];
  $request_id = $finddonatedata[2];

  //find current request data
  //Dog Data Query
  $res = $con->query("SELECT * FROM `user_dog` ud
  JOIN dog_breeds db ON ud.breeds_id = db.breeds_id
  JOIN blood_type bt on ud.dog_bloodtype_id=bt.bloodtype_id
  JOIN dog_diseaseblood ddb on ud.disease_id = ddb.disease_id
  JOIN request rq ON rq.for_dog_id = ud.dog_id
  WHERE rq.request_id ='$request_id'");

  $data = $res->fetch_assoc();
  $userdogreq = array(
  "dog_id" => $data["dog_id"],
  "breeds_id" => $data["breeds_id"],
  "breeds_name" => $data["breeds_name"],
  "dog_name" => $data["dog_name"],
  "dog_gender" => $data["dog_gender"],
  "dog_birthyear" => $data["dog_birthyear"],
  "disease_id" => $data["disease_id"],
  "disease_name" => $data["disease_name"],
  "dog_bloodtype_id" => $data["dog_bloodtype_id"],
  "dog_bloodtype_name" => $data["bloodtype_name"],
  "dog_weight" => $data["dog_weight"],
  "dog_image" => $data["dog_image"],
  );

  //Request Data Query
  $res = $con->query("SELECT * FROM `request` rq
  JOIN user u ON rq.from_user_id = u.user_id
  JOIN place p ON rq.place_id = p.place_id
  WHERE request_id ='$request_id'");
  $data = $res->fetch_assoc();
  $requestdata = array(
  "request_id" => $data["request_id"],
  "requester_dog" => $userdogreq,
  "symptoms" => $data["symptoms"],
  "place" => array(
  "place_id" => $data["place_id"],
  "place_name" => $data["name"],
  "phone" => $data["phone"],
  "formattedAddress" => $data["formattedAddress"],
  ),
  "duedate" => $data["duedate"],
  "amount_volume" => $data["amount_volume"],
  "created_time" => $data["created_time"]
  );

  //Current Donate Dog Data Query
  $res = $con->query("SELECT * FROM `user_dog` ud
  JOIN dog_breeds db ON ud.breeds_id = db.breeds_id
  JOIN blood_type bt on ud.dog_bloodtype_id=bt.bloodtype_id
  JOIN dog_diseaseblood ddb on ud.disease_id = ddb.disease_id
  WHERE ud.dog_id = '$dog_id'");

  $data = $res->fetch_assoc();
  $userdogdonate = array(
  "dog_id" => $data["dog_id"],
  "breeds_id" => $data["breeds_id"],
  "breeds_name" => $data["breeds_name"],
  "dog_name" => $data["dog_name"],
  "dog_gender" => $data["dog_gender"],
  "dog_birthyear" => $data["dog_birthyear"],
  "disease_id" => $data["disease_id"],
  "disease_name" => $data["disease_name"],
  "dog_bloodtype_id" => $data["dog_bloodtype_id"],
  "dog_bloodtype_name" => $data["bloodtype_name"],
  "dog_weight" => $data["dog_weight"],
  "dog_image" => $data["dog_image"],
  );

  //count how many people donate to this request
  $countres = $con->query("SELECT * FROM donate WHERE request_id = '$request_id' AND donate_status = 0");
  $currentdonatecount = $countres->num_rows;
  $amount = $requestdata["amount_volume"];
  $needdonator = ceil($amount / 300);
  $needprepare = ceil($needdonator / 2);
  $totalneed = $needdonator + $needprepare;

  //find current donation data
  $donatorrealarr = array();
  $donatoraltarr = array();
  $donatorfind = $con->query("SELECT
  up.user_id, up.firstname, up.lastname, ud.dog_id, ud.dog_name FROM donate d
  JOIN user_dog ud ON d.dog_id = ud.dog_id
  JOIN user_profile up ON ud.user_id = up.user_id
  WHERE d.request_id = '$request_id' AND d.donate_status != 3");
  $count = 0;
  while ($donatordata = $donatorfind->fetch_assoc()) {
  if (++$count <= $needdonator) {
  array_push($donatorrealarr, $donatordata);
  } else {
  array_push($donatoraltarr, $donatordata);
  }
  }
  $donator = array(
  "realneed" => $needdonator,
  "realdonator" => $donatorrealarr,
  "altneed" => $needprepare,
  "altdonator" => $donatoraltarr,
  "total" => $totalneed,
  "current" => $currentdonatecount
  );

  //find out if user can cancle and calculate the time left in Days
  $duedate = $requestdata["duedate"];
  $createddate = $requestdata["created_time"];
  $reason = "";
  if (sizeof($donatoraltarr) > 0) {
  //have alt donator
  $now = time(); // or your date as well
  $your_date = strtotime($duedate);
  $datediff = $your_date - $now;
  $daydiff = floor($datediff / (60 * 60 * 24));
  $canCancle = true;
  $reason = "มีผู้บริจาคตัวสำรอง";
  if ($daydiff > 0) {
  $canCancle = false;
  $reason = "ไม่สามารถยกเลิกได้หลังจากวันที่กำหนด";
  }
  } else {
  //no alt donator
  $daydiff = 0;
  $canCancle = false;
  $reason = "ไม่สามารถยกเลิกได้ เพราะไม่มีผู้บริจาคตัวสำรอง";
  }

  $innerdonate = array(
  "donate_id" => $donate_id,
  "donator_dog" => $userdogdonate,
  "request" => $requestdata,
  "isCancelable" => $canCancle,
  "reason" => $reason,
  "dayLeftToCancel" => $daydiff
  );
  array_push($donate, $innerdonate);
  }
  echo json_encode($donate);
 * /
 */
?>