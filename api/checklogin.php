<?php

header('Content-Type: application/json');
include "../include/functions.php";
include "../include/dbcon.inc.php";
$user_id = getUserIdFromToken($con, @$_POST["token"]);
if ($user_id != 0) {
    // User Data Query
    $res = $con->query("SELECT user.email as email, user_profile.firstname as fname, user_profile.lastname as lname, user_profile.telno, user_profile.house_no,data_subdistrict.subdistrict_NAME,data_district.district_name,data_province.PROVINCE_NAME,user_profile.postcode,user_profile.user_image, user.activate_status, user.user_type ,data_province.PROVINCE_NAME FROM user 
                        LEFT JOIN user_profile ON user.user_id = user_profile.user_id 
                        LEFT JOIN data_province ON data_province.province_id = user_profile.province
                        LEFT JOIN data_district ON data_district.district_id = user_profile.district
                        LEFT JOIN data_subdistrict ON data_subdistrict.subdistrict_id = user_profile.subdistrict
                         WHERE user.user_id = '$user_id'");
    echo $con->error;
    $data = $res->fetch_assoc();
    $userdata = array(
        "user_id" => $user_id,
        "email" => $data["email"],
        "firstname" => $data["fname"],
        "lastname" => $data["lname"],
        "telno" => $data["telno"],
        "address" => array(
            "houseno" => $data["house_no"],
            "subdistrict" => $data["subdistrict_NAME"],
            "district" => $data["district_name"],
            "province" => $data["PROVINCE_NAME"],
            "postcode" => $data["postcode"],
        ),
        "user_image" => $data["user_image"],
        "activate_status" => $data["activate_status"],
        "user_type" => $data["user_type"]
    );
    //Dog Data Query (Later)
    $dogdata = array();
    $res = $con->query("SELECT * FROM `user_dog` ud
           JOIN dog_breeds db ON ud.breeds_id = db.breeds_id
           JOIN blood_type bt on ud.dog_bloodtype_id=bt.bloodtype_id
           JOIN dog_diseaseblood dd ON dd.disease_id = ud.disease_id
           WHERE ud.user_id = '$user_id' and ud.dog_status = 1");
    while ($data = $res->fetch_assoc()) {
        $userdog = array(
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
        array_push($dogdata, $userdog);
    }

    //Current Request Query
    //find current request_id
    $findreq = $con->query("SELECT request_id FROM request WHERE request.from_user_id = '$user_id'"
            . " AND request.request_type = 2 AND request.request_id NOT IN "
            . "(SELECT request_id FROM donate WHERE donate_status = 1 OR donate_status = 2)");
    echo $con->error;
    $request = array();
    while ($findreqdata = $findreq->fetch_array()) {
        $request_id = $findreqdata[0];

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


        //count how many people donate to this request
        $countres = $con->query("SELECT * FROM donate WHERE request_id = '$request_id' AND donate_status = 0");
        $currentdonatecount = $countres->num_rows;
        $amount = $data["amount_volume"];
        $needdonator = ceil($amount / 300);
        $needprepare = ceil($needdonator / 4);
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
        $requestinner = array(
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
            "created_time" => $data["created_time"],
            "donator" => $donator
        );
        array_push($request, $requestinner);
    }

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
        //
        //
        ////Dog Data Query
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
        $needprepare = ceil($needdonator / 4);
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
            if ($daydiff <= 0) {
                $canCancle = false;
                $reason = "ไม่สามารถยกเลิกก่อนถึงเวลาที่กำหนด 1 วัน";
            }
        } else {
            //no alt donator
            $createdtime = strtotime($createddate);
            $duetime = strtotime($duedate);
            $datediff = $duetime - $createdtime;
            $okdaydiff = floor((floor($datediff / (60 * 60 * 24))) / 2);
            $currentdaydiff = floor(($duetime - time()) / (60 * 60 * 24));
            $daydiff = $currentdaydiff-$okdaydiff;
            if ($daydiff<=0) {
                $canCancle = false;
                $reason = "ไม่สามารถยกเลิกก่อนถึงเวลาที่กำหนด 1/2 ของเวลาทั้งหมด";
            } else {
                $canCancle = true;
                $reason = "ไม่มีผู้ผู้บริจาคตัวสำรอง";
            }
        }

        $innerdonate = array(
            "donate_id" => $donate_id,
            "donator_dog" => $userdogdonate,
            "request" => $requestdata,
            "isCancelable" => $canCancle,
            "reason"=>$reason,
            "dayLeftToCancel"=>$daydiff
        );
        array_push($donate, $innerdonate);
    }


    $response = array(
        "status" => 1,
        "userdata" => $userdata,
        "dogdata" => $dogdata,
        "requestdata" => $request,
        "donatedata" => $donate
    );
} else {
    $response = array(
        "status" => 0
    );
}
echo json_encode($response);
?>