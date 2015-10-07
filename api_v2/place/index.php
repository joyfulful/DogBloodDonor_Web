<?php

header('Content-Type: application/json');
include "../../include/dbcon.inc.php";
include "../../include/functions.php";

//recieve data from mobile app
$lat = $con->real_escape_string(@$_POST["lat"]);
$long = $con->real_escape_string(@$_POST["long"]);
$query = $con->real_escape_string(@$_POST["query"]);

//debug
//$lat = "13.6773297";
//$long = "100.5009175";
//foursquare api link
$link = "https://api.foursquare.com/v2/venues/search";

//foursqure app id and secret form developer page
$client_id = "0VUVYWX1EMQ3OGJACTCUVMAP4WXIT1CWM212BJ5EVG0LKHII";
$client_secret = "VENQ0LWPL13AUKORAZZUEENSYMQJC3RT0KY1KOWAN4NL2UG2";

//other query setting
$v = "20150824";
$categoryId = "4bf58dd8d48988d104941735"; //foursquare Medial Center
$categoryId .=",4bf58dd8d48988d1e5941735"; //Dog run
$ll = $lat . "," . $long;

//generate api link
$finallink = $link . "?" . "client_id=" . $client_id . "&" . "client_secret=" . $client_secret . "&v=" . $v . "&ll=" . $ll . "&categoryId=" . $categoryId;

if ($query == "") {
    $finallink.="&intent=browse&radius=1500";
} else {
    $finallink.="&query=" . urlencode($query);
}
$response = file_get_contents($finallink);
$places = json_decode($response, true);

$jsonresponse = array();

foreach ($places["response"]["venues"] as $key => $place) {
    $foursquare_id = $con->real_escape_string($place["id"]);
    $name = $con->real_escape_string($place["name"]);
    $phone = $con->real_escape_string(@$place["contact"]["phone"]);
    $location = $place["location"];
    $address = $con->real_escape_string(@$location["address"]);
    $p_lat = $con->real_escape_string(@$location["lat"]);
    $p_long = $con->real_escape_string(@$location["lng"]);
    $distance = $con->real_escape_string(@$location["distance"]);
    $postalCode = $con->real_escape_string(@$location["postalCode"]);
    $city = $con->real_escape_string(@$location["city"]);
    $state = $con->real_escape_string(@$location["state"]);
    $formattedAddress = $con->real_escape_string(implode(" ", @$location["formattedAddress"]));

    //check exists in place table
    $res = $con->query("SELECT * FROM place WHERE foursquare_id = '$foursquare_id'");
    if ($res->num_rows == 0) {
        //not found then insert new place
        $res2 = $con->query("INSERT INTO `place`(`place_id`, `foursquare_id`, `name`, `latitude`, `longtitude`, "
                . "`address`, `phone`, `postalCode`, `city`, `state`, `formattedAddress`)"
                . " VALUES (null,'$foursquare_id','$name','$p_lat','$p_long',"
                . "'$address','$phone','$postalCode','$city','$state','$formattedAddress')");
        $place_id = $con->insert_id;
    } else {
        $data = $res->fetch_assoc();
        $place_id = $data["place_id"];
    }

    $placeresponse = array(
        "place_id" => $place_id,
        "name" => $name,
        "distance" => $distance,
        "address" => $formattedAddress
    );
    array_push($jsonresponse, $placeresponse);
}
usort($jsonresponse, "distance_compare");
foreach ($jsonresponse as $key => $value) {
    $distance = $value["distance"];
    if ($distance >= 1000) {
        $distance = number_format($distance / 1000, 2) . " km";
    } else {
        $distance = $distance . " m";
    }
    $jsonresponse[$key]["distance"] = $distance;
}
echo json_encode($jsonresponse);

function distance_compare($a, $b) {
    $t1 = $a['distance'];
    $t2 = $b['distance'];

    return $t1 - $t2;
}

?>