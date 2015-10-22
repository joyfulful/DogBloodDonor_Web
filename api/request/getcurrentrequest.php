<?php

header('Content-Type: application/json');
include "../../include/dbcon.inc.php";
include "../../include/functions.php";
$user_id = getUserIdFromToken($con, @$_POST["token"]);

$response = array();

$requests = getCurrentActiveRequestByUserId($user_id, $con);
if (sizeof($requests) > 0) {
    foreach ($requests as $key => $request) {
        $dog = getDogById($request["for_dog_id"], $con);
        $bloodtype = getBloodTypeById($dog["dog_bloodtype_id"], $con);
        $breeds = getBreedsById($dog["breeds_id"], $con);
        $place = getPlaceById($request["place_id"], $con);

        $donator = getCurrentActiveDonateByRequestId($request["request_id"], $con);
        $donatecalc = calculateDonator($request["amount_volume"]);
        $donators = getDonatorByRequestId($request["request_id"], $con);

        $stats = array(
            "realneed" => $donatecalc["realdonator"],
            "realdonator" => sortRealDonator($donators, $donatecalc),
            "altneed" => $donatecalc["extradonator"],
            "altdonator" => sortAltDonator($donators, $donatecalc),
            "total" => $donatecalc["total"],
            "current" => sizeof($donator)
        );

        $resrequest = array(
            "request_id" => $request["request_id"],
            "requested_dog" => array(
                "dog_id" => $dog["dog_id"],
                "dog_name" => $dog["dog_name"],
                "dog_image" => $dog["dog_image"],
                "bloodtype_name" => $bloodtype["bloodtype_name"],
                "breeds_name" => $breeds["breeds_name"]
            ),
            "symptoms" => $request["symptoms"],
            "place" => array(
                "place_id" => $place["place_id"],
                "place_name" => $place["name"],
                "phone" => $place["phone"],
                "formattedAddress" => $place["formattedAddress"]
            ),
            "duedate" => changeFormatDate($request["duedate"]),
            "amount_volume" => $request["amount_volume"],
            "stats" => $stats
        );
        array_push($response, $resrequest);
    }
}

echo json_encode($response);