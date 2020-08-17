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
  define("NAME", $db->getSetting($mysqli,"name"));
  define("TITLE", $db->getSetting($mysqli,"title"));
  define("WEB_URL", $db->getSetting($mysqli,"url"));
  define("MAILER_NAME", $db->getSetting($mysqli,"mailer"));
  define("MAILER_ADDRESS", $db->getSetting($mysqli,"mailer_email"));
  $set_post = false;
  if(isset($_POST)){
	 
	$set_post = true;	  
  }
  Template::render_header(_("Options"), true);
?>
<div class="text-center">
	<h2><?php if($set_post){ "Settings Saved"; } else { echo "Server Status Options"; } ?></h2>
</div>
<form method="post">
<?php Template::render_toggle("Toggle Title","togglename"); ?>
	<button class="btn btn-primary pull-right" type="submit">Save Settings</button>
</form>
