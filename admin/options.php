<?php

if (!file_exists("../config.php"))
{
  header("Location: ../");
}
else{
  require_once("../config.php");
  require_once("../classes/constellation.php");
  require_once("../classes/mailer.php");
  require_once("../classes/notification.php");
  require_once("../template.php");
  require_once("../libs/parsedown/Parsedown.php");
  require_once("../classes/queue.php");
  require_once("../classes/db-class.php");
}
  $db = new SSDB();
  if(trim($db->getSetting($mysqli,"notifyUpdates")) == "yes"){
      $notifyUpdates_status = true;
  } else {
      $notifyUpdates_status = false;
  }
  $set_post = false;
  if(!empty($_POST)){
    if($_POST["nu_toggle"] == "on"){ $nu_toggle = "yes"; } else { $nu_toggle = "no"; }
    $db->deleteSetting($mysqli,"notifyUpdates");
    $db->setSetting($mysqli,"notifyUpdates",$nu_toggle);
    $db->deleteSetting($mysqli,"name");
    $db->setSetting($mysqli,"name",$_POST["sitename"]);
    $set_post = true;
    if($nu_toggle == "yes"){
      $notifyUpdates_status = true;
    } else {
      $notifyUpdates_status = false;
    }
    define("NAME", $db->getSetting($mysqli,"name"));
    define("TITLE", $db->getSetting($mysqli,"title"));
    define("WEB_URL", $db->getSetting($mysqli,"url"));
    define("MAILER_NAME", $db->getSetting($mysqli,"mailer"));
    define("MAILER_ADDRESS", $db->getSetting($mysqli,"mailer_email"));
  }
  Template::render_header(_("Options"), true);
?>
<div class="text-center">
    <h2><?php if($set_post){ echo "Settings Saved"; } else { echo "Server Status Options"; } ?></h2>
</div>
<form method="post">
<?php Template::render_toggle("Notify Updates","nu_toggle",$notifyUpdates_status); ?>
    <div class="input-group mb-3">
        <div class="input-group-prepend">
        <span class="input-group-text" id="basic-addon1">Site Name</span>
        </div>
        <input type="text" class="form-control" placeholder="" aria-label="Username" aria-describedby="basic-addon1" name="sitename" value="<?php echo NAME; ?>">
    </div>
    <button class="btn btn-primary pull-right" type="submit">Save Settings</button>
</form>
