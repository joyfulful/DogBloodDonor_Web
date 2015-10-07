<?php

//Function.php แปลง token เป็น user_id
function crypto_rand_secure($min, $max) {
    $range = $max - $min;
    if ($range < 0)
        return $min; // not so random...
    $log = log($range, 2);
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd >= $range);
    return $min + $rnd;
}

function getToken($length) {
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "0123456789";
    for ($i = 0; $i < $length; $i++) {
        $token .= $codeAlphabet[crypto_rand_secure(0, strlen($codeAlphabet))];
    }
    return $token;
}

function get_client_ip() {
    $ipaddress = '';
    if (@$_SERVER['HTTP_CLIENT_IP'])
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (@$_SERVER['HTTP_X_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (@$_SERVER['HTTP_X_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (@$_SERVER['HTTP_FORWARDED_FOR'])
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if (@$_SERVER['HTTP_FORWARDED'])
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if (@$_SERVER['REMOTE_ADDR'])
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

function getUserIdFromToken($con, $token) {
    $token = $con->real_escape_string($token);
    $res = $con->query("SELECT user_id FROM user_session WHERE token = '$token' AND isvalid = 1");
    if ($res->num_rows == 1) {
        $data = $res->fetch_assoc();
        $userid = $data["user_id"];
        return $userid;
    } else {
        return 0;
    }
}

function getUserById($user_id, $con) {
    $res = $con->query("SELECT * FROM user WHERE user_id = '$user_id'");
    if ($res->num_rows > 0) {
        return $res->fetch_assoc();
    } else {
        return false;
    }
}

function getUserProfileById($user_id, $con) {
    $res = $con->query("SELECT * FROM user_profile WHERE user_id = '$user_id'");
    if ($res->num_rows > 0) {
        return $res->fetch_assoc();
    } else {
        return false;
    }
}

function getUserDogByUserId($user_id, $con) {
    $dogs = array();
    $res = $con->query("SELECT * FROM user_dog WHERE user_id = '$user_id' AND dog_status = 1");
    while ($data = $res->fetch_assoc()) {
        array_push($dogs, $data);
    }
    return $dogs;
}

function getDogById($dog_id, $con) {
    $res = $con->query("SELECT * FROM user_dog WHERE dog_id = '$dog_id'");
    if ($res->num_rows > 0) {
        return $res->fetch_assoc();
    } else {
        return false;
    }
}

function getBloodTypeById($bloodtype_id, $con) {
    $res = $con->query("SELECT * FROM blood_type WHERE bloodtype_id = '$bloodtype_id'");
    if ($res->num_rows > 0) {
        return $res->fetch_assoc();
    } else {
        return false;
    }
}

function getDiseaseBloodById($dog_diseaseblood, $con) {
    $res = $con->query("SELECT * FROM dog_diseaseblood WHERE disease_id = '$dog_diseaseblood'");
    if ($res->num_rows > 0) {
        return $res->fetch_assoc();
    } else {
        return false;
    }
}

function getRequestById($request_id, $con) {
    $res = $con->query("SELECT * FROM request WHERE request_id = '$request_id'");
    if ($res->num_rows > 0) {
        return $res->fetch_assoc();
    } else {
        return false;
    }
}

function getPlaceById($place_id, $con) {
    $res = $con->query("SELECT * FROM place WHERE place_id = '$place_id'");
    if ($res->num_rows > 0) {
        return $res->fetch_assoc();
    } else {
        return false;
    }
}

function getBloodStoreByBloodTypeId($bloodtype_id, $con) {
    $bloodstores = array();
    $res = $con->query("SELECT * FROM hospital_bloodstore hb JOIN hospital_dog hd ON hb.hospital_dogid = hd.hospital_dogid "
            . "WHERE hd.bloodtype_id = '$bloodtype_id' and hb.status = 1 and hb.exp_date > now() ORDER BY hb.exp_date ASC ");
    while($data = $res->fetch_assoc()){
        array_push($bloodstores,$data);
    }
    return $bloodstores;
}

function getHospitalById($hospital_id,$con){
    $hospital = array();
    $res = $con->query("SELECT * FROM hospital WHERE hospital_id = '$hospital_id'");
    echo $con->error;
    if($res->num_rows > 0){
        $data = $res->fetch_assoc();
        $hospital = array(
            "hospital_id"=>$data["hospital_id"],
            "hospital_name"=>$data["hospital_name"],
            "hospital_address"=>$data["hospital_address"],
            "hospital_phone"=>$data["hospital_phone"],
            "hospital_contact"=>$data["hospital_contact"]
        );
    }
    return $hospital;
    
}





$thai_day_arr=array("อาทิตย์","จันทร์","อังคาร","พุธ","พฤหัสบดี","ศุกร์","เสาร์");  
$thai_month_arr=array(  
    "0"=>"",  
    "1"=>"มกราคม",  
    "2"=>"กุมภาพันธ์",  
    "3"=>"มีนาคม",  
    "4"=>"เมษายน",  
    "5"=>"พฤษภาคม",  
    "6"=>"มิถุนายน",   
    "7"=>"กรกฎาคม",  
    "8"=>"สิงหาคม",  
    "9"=>"กันยายน",  
    "10"=>"ตุลาคม",  
    "11"=>"พฤศจิกายน",  
    "12"=>"ธันวาคม"                    
);  
$thai_month_short_arr = Array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
?>