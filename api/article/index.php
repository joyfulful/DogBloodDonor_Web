<?php
include "../../include/functions.php";
include "../../include/dbcon.inc.php";
header('Content-Type: application/json');
$group_id = $con->real_escape_string(@$_POST["group_id"]);
$article_id = $con->real_escape_string(@$_POST["article_id"]);

$result = array();
if ($group_id == "" & $article_id == "") {
    $res = $con->query("SELECT * FROM `article_group`");
    while ($data = $res->fetch_assoc()) {
        array_push($result, $data);
    }
} else if ($group_id != "" & $article_id == "") {
    $res = $con->query("SELECT `article_id`, `group_id`, `article_name`, `article_date`, `article_image`, `article_viewcount`, `article_ref`, `article_ref_link` "
            . "FROM article_data where group_id = '$group_id' ORDER BY article_date DESC ");
    while ($data = $res->fetch_assoc()) {
        array_push($result, $data);
    }
} else if ( $article_id != "") {
    $con->query("UPDATE article_data SET article_viewcount = article_viewcount+1 WHERE article_id = '$article_id'");
    $res = $con->query("SELECT * FROM article_data where article_id = '$article_id'");
    while ($data = $res->fetch_assoc()) {
        array_push($result, $data);
    }
}
echo json_encode($result);
?>