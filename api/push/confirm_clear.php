<?php
date_default_timezone_set('Asia/Bangkok');
echo "Running Request Clean up script : " . date('l jS \of F Y h:i:s A') . "\n";
include "../../include/dbcon.inc.php";
$findrequest = $con->query("SELECT * FROM request WHERE date(duedate) < date(now() + INTERVAL 10 DAY) "
        . "AND request_id NOT IN (SELECT request_id FROM donate WHERE donate_status = 1 OR donate_status = 2)");
if ($findrequest->num_rows > 0) {
    echo "Found " . $findrequest->num_rows . " Requests to clear\n";
    while ($data = $findrequest->fetch_assoc()) {
        $request_id = $data["request_id"];
        echo "Clearing Request_id=" . $request_id;
        $con->query("INSERT INTO `donate`(`donate_id`, `request_id`, `dog_id`, `donate_date`, `donate_status`, `donate_lastupdate`) "
                . "VALUES (null,'$request_id','0',now(),2,now())");
        $con->query("INSERT INTO `log_request`(`id`, `request_id`, `timelog`) VALUES (null,'$request_id',now())");
        if ($con->error == "") {
            echo " ... Success !\n";
        } else {
            echo " ... Failed !\n";
        }
    }
} else {
    echo "No Request Found\n";
}
echo "Program Finish at : " . date('l jS \of F Y h:i:s A') . "\n";
?>