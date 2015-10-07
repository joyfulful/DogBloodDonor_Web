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
        <script>
            $("document").ready(function (e) {
                $("#passwordform").on("submit", function (e) {
                    if ($("#p1").val() != $("#p2").val()) {
                        alert("Password Not Match");
                        e.preventDefault();
                        return false;
                    }
                });
            });
        </script>
    </head>
    <body>
        <div class="container">
            <?php
            include "../../include/functions.php";
            include "../../include/dbcon.inc.php";
            $code = $con->real_escape_string(@$_GET["code"]);
            $res = $con->query("SELECT * FROM user WHERE forgot_code = '$code'");
            if ($res->num_rows == 1) {
                $data = $res->fetch_assoc();
                ?>
                <h1>Change Password For <?= $data["email"] ?></h1>
                <form class="form-horizontal" id="passwordform" action ="forgot_save.php" method="post">
                    <div class="col-sm-3" style="text-align:right">
                        New Password : 
                    </div>
                    <div class="col-sm-9">
                        <input class="form-control" type="password" name="password1" id="p1" required="">
                    </div>
                    <div class="col-sm-3" style="text-align:right">
                        Retype Password : 
                    </div>
                    <input type="hidden" name="code" value="<?=$code?>">
                    <div class="col-sm-9">
                        <input class="form-control" type="password" name="password2" id="p2" required="">
                    </div>
                    <br><br>
                    <button type="submit" class="btn btn-success">Send !</button>
                </form>

                <?php
            } else {
                echo "<h1>Code Not Found !";
            }
            ?>
        </div>
    </body>
</html>
