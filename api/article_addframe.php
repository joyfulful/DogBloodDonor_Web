<?php
include "session.inc.php";
include "../dbcon.inc.php";
if (isset($_GET["editid"])) {
    $id = $con->real_escape_string($_GET["editid"]);
    $resedit = $con->query("SELECT * FROM article_data WHERE article_id = '$id'");
    $dataedit = $resedit->fetch_assoc();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="../assets/css/bootstrap.min.css" />
        <link rel="stylesheet" href="../assets/css/font-awesome.min.css" />
        <link rel="stylesheet" href="../assets/css/summernote.css" />
        <title>Add New Article</title>
    </head>
    <body>
        <form class="articleform" action="article_add_save.php" method="post" enctype="multipart/form-data" style="padding:15px; padding-top:25px;  ">
            <table style="width:100%;">
                <tr>
                    <td valign="top" style="width:50%;">
                        <div>
                            <div class="col-xs-3" style="line-height: 30px; font-weight: bold;">
                                Article Group : 
                            </div>
                            <div class="col-xs-9">
                                <select class="form-control" name="group" required>
                                    <option value="" disabled selected>Select Article Group</option>
                                    <?php
                                    $res = $con->query("SELECT * FROM `article_group`");
                                    while ($data = $res->fetch_assoc()) {
                                        if (@$dataedit["group_id"] == $data["group_id"]) {
                                            ?>
                                            <option selected="" value="<?= $data["group_id"] ?>"><?= $data["group_name"] ?></option>
                                        <?php } else { ?>
                                            <option  value="<?= $data["group_id"] ?>"><?= $data["group_name"] ?></option>
                                        <?php } ?>

                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <br><br>
                        <div>
                            <div class="col-xs-3" style="line-height: 30px; font-weight: bold;">
                                Name : 
                            </div>
                            <div class="col-xs-9">
                                <textarea name="name" class="form-control" required><?= @$dataedit["article_name"] ?></textarea>
                            </div>
                        </div>
                        <br><br><br>
                        <div>
                            <div class="col-xs-3" style="line-height: 30px; font-weight: bold;">
                                Date : 
                            </div>
                            <div class="col-xs-9">
                                <input type="date" name="date" value="<?= @$dataedit["article_date"] ?>"  class="form-control" required>
                            </div>
                        </div>
                        <br><br>
                        <div>
                            <div class="col-xs-3" style="line-height: 30px; font-weight: bold;">
                                Image : 
                            </div>
                            <div class="col-xs-9">
                                <?php if (isset($_GET["editid"])) { ?>
                                    <img src="articleimg/<?= @$dataedit["article_image"] ?>" class = "img-thumbnail"><br>
                                <?php }
                                ?>
                                <input type="file" name="image" class="form-control" >
                            </div>
                        </div>
                        <br><br>
                        <div>
                            <div class="col-xs-3" style="line-height: 30px; font-weight: bold;">
                                Reference : 
                            </div>
                            <div class="col-xs-9">
                                <textarea name="reftext"  class="form-control" required><?= @$dataedit["article_ref"] ?></textarea>
                            </div>
                        </div>
                        <br><br><br>
                        <div>
                            <div class="col-xs-3" style="line-height: 30px; font-weight: bold;">
                                URL : 
                            </div>
                            <div class="col-xs-9">
                                <textarea name="refurl" class="form-control" required><?= @$dataedit["article_ref_link"] ?></textarea>
                            </div>
                        </div>
                        <br><br>
                        <!--  <button type="submit">Save</button -->
                    </td>
                    <td  style="width:50%;">
                        <div style="width:100%; padding-left:30px;">
                            <div id="summernote"><?= @$dataedit["article_text"] ?></div>
                        </div>
                        <input type="hidden" name="data" id="summernotedata">
                        <?php if (isset($_GET["editid"])) { ?>
                            <input type="hidden" name="editid" value="<?= $_GET["editid"] ?>">
                        <?php }
                        ?>
                        <button class="btn waves-effect waves-light btn-lg" style="margin-left:80%;width:20%;background-color:#A52A2A;color: white " 
                                type="submit" name="action">Save   </button>

                    </td>
                </tr>
            </table>

        </form>
        <div id="loader" style="display:none; width:100%; height:100%; position: fixed; top:0; left:0; background-color: rgba(255,255,255,0.6); z-index:9999; text-align: center;">
            <img src="../assets/img/loader2.gif" style="width:60px; margin-top:150px;"><br>
            <h3>Uploading...</h3>
        </div>
        <script type="text/javascript" src="../assets/js/jquery-2.1.4.min.js"></script>
        <script type="text/javascript" src="../assets/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="../assets/js/summernote.min.js"></script>
        <script>
            $(document).ready(function () {
                $('#summernote').summernote({
                    height: 700, // set editor height
                    minHeight: null, // set minimum height of editor
                    maxHeight: 1000, // set maximum height of editor
                });
                $(".articleform").on("submit", function (e) {
                    $("#loader").fadeIn(100);
                    $("#summernotedata").val($('#summernote').code());
                });
            });
        </script>
    </body>
</html>
