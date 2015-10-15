<?php

header('Content-Type: application/json');
include "../../include/dbcon.inc.php";
include "../../include/functions.php";
$ses_user_id = getUserIdFromToken($con, @$_POST["token"]);
$request_id = $con->real_escape_string(@$_POST["request_id"]);
if ($request_id != 0 & $ses_user_id != 0) {

    //check if request is not closed
    $checkclose = $con->query("SELECT * FROM donate WHERE request_id = '$request_id' AND donate_status IN('1','2')");
    if ($checkclose->num_rows > 0) {
        $status = 2;
    } else {
        $status = 1;
    }

    //finduser_id from request
    $finduid = $con->query("SELECT from_user_id FROM request WHERE request_id = '$request_id'");
    $data = $finduid->fetch_array();
    $user_id = $data["from_user_id"];

    // User Data Query
    $res = $con->query("SELECT user_profile.firstname as fname,
        user_profile.lastname as lname, user_profile.telno, user_profile.house_no,user_profile.subdistrict
        ,user_profile.district,user_profile.province,user_profile.postcode ,user_profile.user_image
        FROM user
        JOIN user_profile ON user.user_id = user_profile.user_id
        WHERE user.user_id = '$user_id'");
    echo $con->error;
    $data = $res->fetch_assoc();
    $userdata = array(
        "user_id" => $user_id,
        "firstname" => $data["fname"],
        "lastname" => $data["lname"],
        "user_image" => $data["user_image"]
    );
    //Dog Data Query (Later)
    $res = $con->query("SELECT * FROM `user_dog` ud
           JOIN dog_breeds db ON ud.breeds_id = db.breeds_id
           JOIN blood_type bt on ud.dog_bloodtype_id=bt.bloodtype_id
           JOIN dog_diseaseblood ddb on ud.disease_id = ddb.disease_id
           JOIN request rq ON rq.for_dog_id = ud.dog_id
           WHERE ud.user_id = '$user_id' and ud.dog_status = 1 and request_id ='$request_id'");

    $data = $res->fetch_assoc();
    $userdog = array(
        "dog_id" => $data["dog_id"],
        "dog_name" => $data["dog_name"],
        "dog_image" => $data ["dog_image"],
        "disease_id" => $data["disease_id"],
        "disease_name" => $data['disease_name'],
        "dog_bloodtype_id" => $data["dog_bloodtype_id"],
        "dog_bloodtype_name" => $data["bloodtype_name"],
        "dog_weight" => $data["dog_weight"],
    );
    //Request Data Query
    $res = $con->query("SELECT * FROM `request`  rq 
            JOIN user u ON rq.from_user_id = u.user_id
            JOIN place p ON rq.place_id = p.place_id
            WHERE u.user_id = '$user_id' and request_id ='$request_id'");
    $data = $res->fetch_assoc();

    $userdoglist = array();
    //find user's dog list
    $finddogres = $con->query("SELECT * FROM user_dog ud "
            . "JOIN blood_type bt ON bt.bloodtype_id = ud.dog_bloodtype_id "
            . "JOIN dog_breeds db ON db.breeds_id = ud.breeds_id "
            . "JOIN dog_diseaseblood dd ON dd.disease_id = ud.disease_id "
            . "WHERE ud.user_id = '$ses_user_id' and ud.dog_status = 1 ");

    while ($dogdata = $finddogres->fetch_assoc()) {
        $isOk = true;
        $dog_id = $dogdata["dog_id"];
        $responseText = array();
        $currentbloodtypeid = $dogdata["dog_bloodtype_id"];
        $requestbloodtypeid = $userdog["dog_bloodtype_id"];

        //check bloodtype
        if (in_array($currentbloodtypeid, array("1", "2"))) {
            array_push($responseText, "สุนัขกรุ๊ปเลือด DEA1.1 และ DEA1.2 ไม่สามารถบริจาคเลือดได้");
            $isOk = false;
        } else {
            if (in_array($requestbloodtypeid, array("1", "2"))) {
                //ok
            } else {
                if ($requestbloodtypeid == $currentbloodtypeid) {
                    //ok
                } else {
                    $isOk = false;
                    array_push($responseText, "กรุ๊ปเลือดไม่ตรงกับผู้ขอรับการบริจาค");
                }
            }
        }

        //check donation is active
        $findactive = $con->query("SELECT * FROM donate WHERE dog_id = '$dog_id' AND donate_status = 0");
        if ($findactive->num_rows == 0) {
            //ok
        } else {
            $isOk = false;
            array_push($responseText, "อยู่ในระหว่างการบริจาคเลือดให้กับสุนัขตัวอื่น");
        }

        //check no success donation in past 3 month
        $findthreem = $con->query("SELECT * FROM donate WHERE request_id = '$request_id' "
                . "AND dog_id = '$dog_id' AND donate_status = 1 AND donate_date > DATE_SUB(now(), INTERVAL 3 MONTH)");
        if ($findthreem->num_rows == 0) {
            //ok
        } else {
            $isOk = false;
            array_push($responseText, "ยังไม่ครบกำหนดระยะเวลาเว้นช่วงในการบริจาคเลือด");
        }

        //check dog blood diease
        $finddiease = $con->query("SELECT * FROM user_dog WHERE dog_id = '$dog_id' AND disease_id > 2");
        if ($finddiease->num_rows == 0) {
            //ok
        } else {
            $isOk = false;
            array_push($responseText, "มีโรคเลือดที่ไม่สามารถบริจาคเลือดได้");
        }

        $currentyear = date("Y") + 543;
        if ($currentyear - $dogdata["dog_birthyear"] >= 1 & $currentyear - $dogdata["dog_birthyear"] <= 8) {
            //ok
        } else {
            $isOk = false;
            array_push($responseText, "อายุไม่อยู่ในเกณฑ์การบริจาคเลือด");
        }

        if ($dogdata["dog_weight"] >= 17.00) {
            //ok
        } else {
            $isOk = false;
            array_push($responseText, "น้ำหนักไม่อยู่ในเกณฑ์การบริจาคเลือด");
        }

        //edit by aj.pichet request, don't display dog which have different bloodtype than requester_dog
        if (in_array($requestbloodtypeid, array(1,2)) | $currentbloodtypeid == $requestbloodtypeid) {
            array_push($userdoglist, array(
                "dog_id" => $dog_id,
                "bloodtype_name" => $dogdata["bloodtype_name"],
                "dog_name" => $dogdata["dog_name"],
                "dog_gender" => $dogdata["dog_gender"],
                "dog_birthyear" => $dogdata["dog_birthyear"],
                "dog_weight" => $dogdata["dog_weight"],
                "dog_image" => $dogdata["dog_image"],
                "breeds_name" => $dogdata["breeds_name"],
                "disease_name" => $dogdata["disease_name"],
                "isOkToDonate" => $isOk,
                "reasons" => $responseText
            ));
        }
    }

    //check if this is donateable
    $donateablestatus = true;
    $donateablereasons = array();
    if ($user_id == $ses_user_id) {
        $donateablestatus = false;
        array_push($donateablereasons, "คุณไม่สามารถบริจาคเลือดให้กับสุนัขของตัวเองได้");
    }
    //count how many people donate to this request
    $countres = $con->query("SELECT * FROM donate WHERE request_id = '$request_id' AND donate_status IN(0,1,2)");
    $currentdonatecount = $countres->num_rows;
    $amount = $data["amount_volume"];
    $needdonator = ceil($amount / 300);
    $needprepare = ceil($needdonator / 4);
    $totalneed = $needdonator + $needprepare;
    if ($currentdonatecount >= $totalneed) {
        $donateablestatus = false;
        array_push($donateablereasons, "มีผู้บริจาคเลือดครบตามจำนวนที่ต้องการแล้ว");
    }
    if ($status == 2) {
        $donateablestatus = false;
        array_push($donateablereasons, "การขอเลือดนี้ได้สิ้นสุดกระบวนการแล้ว");
    }

    //find current donation data
    $donatorrealarr = array();
    $donatoraltarr = array();
    $donatorfind = $con->query("SELECT 
        up.user_id, up.firstname, up.lastname, ud.dog_name FROM donate d
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


    $donateable = array(
        "status" => $donateablestatus,
        "reasons" => $donateablereasons,
        "stats" => array(
            "realneed" => $needdonator,
            "realdonator" => $donatorrealarr,
            "altneed" => $needprepare,
            "altdonator" => $donatoraltarr,
            "total" => $totalneed,
            "current" => $currentdonatecount
        )
    );
    $response = array(
        "status" => $status,
        "requester_userprofile" => $userdata,
        "requester_dog" => $userdog,
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
        "dog_list" => $userdoglist,
        "donateable" => $donateable
    );
} else {
    $response = array(
        "status" => 0
    );
}
echo json_encode($response);
