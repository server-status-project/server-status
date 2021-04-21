<?php
require_once("libs/parsedown/Parsedown.php");

if (!file_exists("config.php")) {
  require_once("template.php");
  require_once("install.php");
} elseif (isset($_GET['do'])) { // we can add other actions with $_GET['do'] later.
  // Fix for translation via _(). We need config.php first...
  require_once("config.php");
  require_once("template.php");


  switch ($_GET['do']) {
    case 'subscriptions':
      require_once("subscriptions.php");
      break;

    case 'email_subscription':
    case 'manage':
    case 'unsubscribe';
      require_once("email_subscriptions.php");
      break;

    default:
      // TODO : How to handle url invalid/unknown [do] commands
      header('Location: index.php');
      break;
  }
} else {

  require_once("config.php");
  require_once("template.php");
  require_once("classes/constellation.php");
  require_once("classes/db-class.php");
  $db = new SSDB();
  define("NAME", $db->getSetting($mysqli, "name"));
  define("TITLE", $db->getSetting($mysqli, "title"));
  define("WEB_URL", $db->getSetting($mysqli, "url"));
  define("MAILER_NAME", $db->getSetting($mysqli, "mailer"));
  define("MAILER_ADDRESS", $db->getSetting($mysqli, "mailer_email"));

  define("SUBSCRIBE_EMAIL", $db->getBooleanSetting($mysqli, "subscribe_email"));
  define("SUBSCRIBE_TELEGRAM", $db->getBooleanSetting($mysqli, "subscribe_telegram"));
  define("TG_BOT_USERNAME", $db->getSetting($mysqli, "tg_bot_username"));
  define("TG_BOT_API_TOKEN", $db->getSetting($mysqli, "tg_bot_api_token"));
  define("GOOGLE_RECAPTCHA", $db->getBooleanSetting($mysqli, "google_recaptcha"));
  define("GOOGLE_RECAPTCHA_SITEKEY", $db->getSetting($mysqli, "google_recaptcha_sitekey"));
  define("GOOGLE_RECAPTCHA_SECRET", $db->getSetting($mysqli, "google_recaptcha_secret"));
  $offset = 0;

  if (isset($_GET['ajax'])) {
    $constellation->render_incidents(false, $_GET['offset'], 5);
    exit();
  } else if (isset($_GET['offset'])) {
    $offset = $_GET['offset'];
  }

  if (isset($_GET['subscriber_logout'])) {
    setcookie('tg_user', '');
    setcookie('referer', '', time() - 3600);
    $_SESSION['subscriber_valid'] = false;
    unset($_SESSION['subscriber_userid']);
    unset($_SESSION['subscriber_typeid']);
    unset($_SESSION['subscriber_id']);
    header('Location: index.php');
  }
  /*
$versionfile = fopen("versionfile", "r") or die("Unable to open version file!");
$appversion = fread($versionfile,filesize("versionfile"));
fclose($versionfile);
if($db->getSetting($mysqli,"dbConfigVersion") != trim($appversion)){
  die("Database needs to be updated. Please update the database and try again. App Version: '".$appversion."' DB Settings Version: '".$db->getSetting($mysqli,"dbConfigVersion")."'.");
}
$useedf = fopen("updateseed", "r") or die("Unable to open updateseed file!");
$useed = fread($useedf,filesize("updateseed"));
fclose($useedf);
if(trim($useed) == "stable"){
$remoteversion = file_get_contents("https://skyfallenhosted.ml/serverstatus/versionauthority/stable/version");
$remotedl = file_get_contents("https://skyfallenhosted.ml/serverstatus/versionauthority/stable/dl");
}
if(trim($useed) == "beta"){
$remoteversion = file_get_contents("https://skyfallenhosted.ml/serverstatus/versionauthority/beta/version");
$remotedl = file_get_contents("https://skyfallenhosted.ml/serverstatus/versionauthority/beta/dl");
}
if($db->getSetting($mysqli,"notifyUpdates") == "yes"){
  if(trim($remoteversion) != trim($appversion)){
    die("Your installation is not upp to date! Download the new update from: '".$remotedl."' Your version is:'".$appversion."' Remote Authority Version is:'".$remoteversion."' Your Update Seed is:'".$useed."' Remote Package Authority is Skyfallen. <br>If you cannot access Remote Authority, please check status.theskyfallen.com and skyfallenhosted.ml manually.");
  }
}
*/
  Template::render_header("Status", "status");
?>
  <div class="text-center">
    <h2><?php echo _("Current status"); ?></h2>
  </div>
  <div id="current">
    <?php $constellation->render_status(); ?>
  </div>

  <?php if ($mysqli->query("SELECT count(*) FROM status")->num_rows) {
  ?>
    <div id="timeline">
      <div class="item">
        <div class="timeline">
          <div class="line text-muted"></div>
          <?php
          $constellation->render_incidents(true, $offset);
          $constellation->render_incidents(false, $offset);
          ?>
        </div>
      </div>
    </div>
<?php }

  Template::render_footer();
}
