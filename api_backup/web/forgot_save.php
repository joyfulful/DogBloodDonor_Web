<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Change Password</title>
        <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
        <script src="jquery-2.1.3.min.js"></script>
        <script src="bootstrap/js/bootstrap.min.js"></script>
    </head>
    <body>
        <div class="container">
            <?php
            include "../../include/functions.php";
            include "../../include/dbcon.inc.php";
            $code = $con->real_escape_string(@$_POST["code"]);
            $res = $con->query("SELECT * FROM user WHERE forgot_code = '$code'");
            if ($res->num_rows == 1) {
                $data = $res->fetch_assoc();
                $user_id = $data["user_id"];
                $password = md5($_POST["password1"]);
                $con->query("UPDATE user SET forgot_code = '', password = '$password' WHERE user_id = '$user_id'")
                ?>
                <h1>Change Password For <?= $data["email"] ?></h1>
                <h1 class="success">Success !</h1>

                <?php
            } else {
                echo "<h1>Code Not Found !";
            }
            ?>
        </div>
    </body>
</html>
