<?php include "session.inc.php"; ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
        <link rel="stylesheet" href="../assets/css/font-awesome.min.css" />
        <link rel="stylesheet" href="../assets/css/summernote.css" />
        <link type="text/css" rel="stylesheet" href="../assets/css/materialize.min.css"  media="screen,projection"/>
        <link rel="stylesheet" href="../assets/css/admin.css" />
        <title>Add New Article</title>
    </head>
    <body>
        <?php include "navbar.inc.php"; ?>
        <main>
            <div class="section" id="index-banner">
                <div class="container">
                    Add New Article
                </div>
            </div>
                <iframe src="article_addframe.php" style="width:80%; height:1000px; margin-left:120px; border:none;" seamless></iframe>
        </main>

        <script type="text/javascript" src="../assets/js/jquery-2.1.4.min.js"></script>
        <script type="text/javascript" src="../assets/js/materialize.min.js"></script>
        <script type="text/javascript" src="../assets/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="../assets/js/summernote.min.js"></script>
        <script>
            $(document).ready(function () {
                $("#navarticle").addClass("active");
                $("#navarticle_add").addClass("active");
                $('.collapsible').collapsible();
                $("select").material_select();
                $('#summernote').summernote();
            });
        </script>
    </body>
</html>
