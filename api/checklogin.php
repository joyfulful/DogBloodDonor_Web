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