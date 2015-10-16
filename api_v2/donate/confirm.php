<?php

include "../../include/functions.php";
include "../../include/dbcon.inc.php";
include "../../include/push_functions.inc.php";
header('Content-Type: application/json');
$user_id = getUserIdFromToken($con, @$_POST["token"]);
$request_id = $con->real_escape_string(@$_POST["request_id"]);
$dog_id = $con->real_escape_string(@$_POST["dog_id"]);
$isDonate = $con->real_escape_string(@$_POST["isDonate"]);
if ($user_id != 0) {
    //get before change
    $request = getRequestById($request_id, $con);
    $donator = getCurrentActiveDonateByRequestId($request_id, $con);
    $donatecalc = calculateDonator($request["amount_volume"]);
    $donators = getDonatorByRequestId($request_id, $con);

    $statsbefore = array(
        "realneed" => $donatecalc["realdonator"],
        "realdonator" => sortRealDonator($donators, $donatecalc),
        "altneed" => $donatecalc["extradonator"],
        "altdonator" => sortAltDonator($donators, $donatecalc),
        "total" => $donatecalc["total"],
        "current" => sizeof($donator)
    );
    //print_r($statsbefore);

    $res = $con->query("UPDATE donate SET donate_status = '$isDonate' , donate_date = now() "
            . "WHERE request_id = '$request_id' AND dog_id ='$dog_id' ");
    echo $con->error;
    if ($con->error == "") {
        $response = array("result" => 1);
    } else {
        $response = array("result" => 0);
    }
}

if ($isDonate == 3) {
    //get after change
    $request = getRequestById($request_id, $con);
    $donator = getCurrentActiveDonateByRequestId($request_id, $con);
    $donatecalc = calculateDonator($request["amount_volume"]);
    $donators = getDonatorByRequestId($request_id, $con);

    $statsafter = array(
        "realneed" => $donatecalc["realdonator"],
        "realdonator" => sortRealDonator($donators, $donatecalc),
        "altneed" => $donatecalc["extradonator"],
        "altdonator" => sortAltDonator($donators, $donatecalc),
        "total" => $donatecalc["total"],
        "current" => sizeof($donator)
    );
    //print_r($statsafter);
    //echo "afterdogid:".$statsafter["realdonator"][$statsafter["realneed"]-1]["dog_id"];
    //echo "beforedogid:".$statsbefore["altdonator"][0]["dog_id"];
    if (isset($statsbefore["altdonator"][0]["dog_id"])) {
        if ($statsafter["realdonator"][$statsafter["realneed"] - 1]["dog_id"] == $statsbefore["altdonator"][0]["dog_id"]) {
            $user = getDogById($statsafter["realdonator"][$statsafter["realneed"] - 1]["dog_id"], $con);
            $user_id = $user["user_id"];
            pushToUser($user_id, "เปลื่ยนสถานะเป็นตัวจริง", "คุณถูกเปลื่ยนเป็นผู้บริจาคตัวจริง (จากเดิมตัวสำรอง)", "donator", $request_id, $con);
        }
    }
}
echo json_encode($response);
?>