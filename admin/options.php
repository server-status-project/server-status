<?php
function getToggle($variable)
{
  $res = ((isset($variable) && ($variable == "on")) ? "yes" : "no");
  return $res;
}


if (!file_exists("../config.php")) {
  header("Location: ../");
} else {
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
$notifyUpdates_status = $db->getBooleanSetting($mysqli, "notifyUpdates");
$emailSubscription_status = $db->getBooleanSetting($mysqli, "subscribe_email");
$telegramSubscription_status = $db->getBooleanSetting($mysqli, "subscribe_telegram");
$tg_bot_api_token = $db->getSetting($mysqli, "tg_bot_api_token");
$tg_bot_username = $db->getSetting($mysqli, "tg_bot_username");
$php_mailer_status = $db->getBooleanSetting($mysqli, "php_mailer");
$php_mailer_smtp_status = $db->getBooleanSetting($mysqli, "php_mailer_smtp");
$php_mailer_secure_status = $db->getBooleanSetting($mysqli, "php_mailer_secure");
$php_mailer_path = $db->getSetting($mysqli, "php_mailer_path");
$php_mailer_host = $db->getSetting($mysqli, "php_mailer_host");
$php_mailer_port = $db->getSetting($mysqli, "php_mailer_port");
$php_mailer_user = $db->getSetting($mysqli, "php_mailer_user");
$php_mailer_pass = $db->getSetting($mysqli, "php_mailer_pass");
$cron_server_ip = $db->getSetting($mysqli, "cron_server_ip");
$google_rechaptcha_status = $db->getBooleanSetting($mysqli, "google_recaptcha");
$google_recaptcha_sitekey = $db->getSetting($mysqli, "google_recaptcha_sitekey");
$google_recaptcha_secret = $db->getSetting($mysqli, "google_recaptcha_secret");

$db->getSetting($mysqli, "");
$set_post = false;
if (!empty($_POST)) {
  $db->updateSetting($mysqli, "notifyUpdates", getToggle($_POST["nu_toggle"]));
  $db->updateSetting($mysqli, "name", htmlspecialchars($_POST["sitename"], ENT_QUOTES));
  $db->updateSetting($mysqli, "subscribe_email", getToggle($_POST["email_subscription_toggle"]));
  $db->updateSetting($mysqli, "subscribe_telegram", getToggle($_POST["telegram_subscription_toggle"]));
  $db->updateSetting($mysqli, "tg_bot_api_token", htmlspecialchars($_POST["tg_bot_api_token"], ENT_QUOTES));
  $db->updateSetting($mysqli, "tg_bot_username", htmlspecialchars($_POST["tg_bot_username"], ENT_QUOTES));
  $db->updateSetting($mysqli, "php_mailer", getToggle($_POST["php_mailer_toggle"]));
  $db->updateSetting($mysqli, "php_mailer_smtp", getToggle($_POST["php_mailer_smtp_toggle"]));
  $db->updateSetting($mysqli, "php_mailer_secure", getToggle($_POST["php_mailer_secure_toggle"]));
  $db->updateSetting($mysqli, "php_mailer_path", htmlspecialchars($_POST["php_mailer_path"], ENT_QUOTES));
  $db->updateSetting($mysqli, "php_mailer_host", htmlspecialchars($_POST["php_mailer_host"], ENT_QUOTES));
  $db->updateSetting($mysqli, "php_mailer_port", htmlspecialchars($_POST["php_mailer_port"], ENT_QUOTES));
  $db->updateSetting($mysqli, "php_mailer_user", htmlspecialchars($_POST["php_mailer_user"], ENT_QUOTES));
  $db->updateSetting($mysqli, "php_mailer_pass", htmlspecialchars($_POST["php_mailer_pass"], ENT_QUOTES));
  $db->updateSetting($mysqli, "cron_server_ip", htmlspecialchars($_POST["cron_server_ip"], ENT_QUOTES));
  $db->updateSetting($mysqli, "google_recaptcha", getToggle($_POST["google_rechaptcha_toggle"]));
  $db->updateSetting($mysqli, "google_recaptcha_sitekey", htmlspecialchars($_POST["google_recaptcha_sitekey"], ENT_QUOTES));
  $db->updateSetting($mysqli, "google_recaptcha_secret", htmlspecialchars($_POST["google_recaptcha_secret"], ENT_QUOTES));

  $set_post = true;
  /*if($nu_toggle == "yes"){
      $notifyUpdates_status = true;
    } else {
      $notifyUpdates_status = false;
    }*/
  // TODO - Reload page to prevent showing old values! or update variables being displayed
  header("Location: " . $uri = $_SERVER['REQUEST_URI']);
  // TODO - The code below will not happen ...

  /*define("NAME", $db->getSetting($mysqli,"name"));
    define("TITLE", $db->getSetting($mysqli,"title"));
    define("WEB_URL", $db->getSetting($mysqli,"url"));
    define("MAILER_NAME", $db->getSetting($mysqli,"mailer"));
    define("MAILER_ADDRESS", $db->getSetting($mysqli,"mailer_email"));
    define("SUBSCRIBER_EMAIL", $db->getSetting($mysqli,"subscriber_email"));
    define("SUBSCRIBER_TELEGRAM", $db->getSetting($mysqli,"subscriber_telegram"));
    define("TG_BOT_API_TOKEN", $db->getSetting($mysqli,"tg_bot_api_token"));
    define("TG_BOT_USERNAME", $db->getSetting($mysqli,"tg_bot_username"));
    define("GOOGLE_RECAPTCHA", $db->getSetting($mysqli,"google_recaptcha"));
    define("GOOGLE_RECAPTCHA_SITEKEY", $db->getSetting($mysqli,"google_recaptcha_sitekey"));
    define("GOOGLE_RECAPTCHA_SECRET", $db->getSetting($mysqli,"google_recaptcha_secret"));
    define("PHP_MAILER", $db->getSetting($mysqli,"php_mailer"));
    define("PHP_MAILER_PATH", $db->getSetting($mysqli,"php_mailer_path"));
    define("PHP_MAILER_SMTP", $db->getSetting($mysqli,"php_mailer_smtp"));
    define("PHP_MAILER_HOST", $db->getSetting($mysqli,"php_mailer_host"));
    define("PHP_MAILER_PORT", $db->getSetting($mysqli,"php_mailer_port"));
    define("PHP_MAILER_SECURE", $db->getSetting($mysqli,"php_mailer_secure"));
    define("PHP_MAILER_USER", $db->getSetting($mysqli,"php_mailer_user"));
    define("PHP_MAILER_PASS", $db->getSetting($mysqli,"php_mailer_pass"));
    define("CRON_SERVER_IP", $db->getSetting($mysqli,"cron_server_ip"));
    */
}
Template::render_header(_("Options"), "options", true);
?>
<div class="text-center">
  <h2><?php if ($set_post) {
        echo "Settings Saved";
      } else {
        echo "Server Status Options";
      } ?></h2>
</div>
<form id="options" method="post">
  <div class="card">
    <div class="card-header">
      <?php Template::render_toggle("Notify Updates", "nu_toggle", $notifyUpdates_status); ?>
    </div>
    <div class="card-body">
      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="basic-addon1">Site Name</span>
        </div>
        <input type="text" class="form-control" placeholder="" aria-label="Username" aria-describedby="basic-addon1" name="sitename" value="<?php echo NAME; ?>">
      </div>
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-header">
      <?php Template::render_toggle("Enable Email Subscription", "email_subscription_toggle", $emailSubscription_status); ?>
    </div>
    <div class="card-body">
      <?php Template::render_toggle("Use PHPMailer for notifications", "php_mailer_toggle", $php_mailer_status); ?>
      <?php Template::render_toggle("Use SMTP with PHPMailer", "php_mailer_smtp_toggle", $php_mailer_smtp_status); ?>
      <?php Template::render_toggle("Use Secure SMTP with PHPMailer", "php_mailer_secure_toggle", $php_mailer_secure_status); ?>
      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="basic-addon1">PHPMailer Path</span>
        </div>
        <input type="text" class="form-control" placeholder="" aria-label="phpmailer_path" aria-describedby="basic-addon1" name="php_mailer_path" value="<?php echo $php_mailer_path; ?>">
      </div>
      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="basic-addon1">PHPMailer SMTP Host</span>
        </div>
        <input type="text" class="form-control" placeholder="" aria-label="php_mailer_host" aria-describedby="basic-addon1" name="php_mailer_host" value="<?php echo $php_mailer_host; ?>">
      </div>
      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="basic-addon1">PHPMailer SMTP Port</span>
        </div>
        <input type="text" class="form-control" placeholder="" aria-label="php_mailer_port" aria-describedby="basic-addon1" name="php_mailer_port" value="<?php echo $php_mailer_port; ?>">
      </div>
      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="basic-addon1">PHPMailer Username</span>
        </div>
        <input type="text" class="form-control" placeholder="" aria-label="php_mailer_username" aria-describedby="basic-addon1" name="php_mailer_user" value="<?php echo $php_mailer_user; ?>">
      </div>
      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="basic-addon1">PHPMailer Password</span>
        </div>
        <input type="password" class="form-control" placeholder="" aria-label="php_mailer_password" aria-describedby="basic-addon1" name="php_mailer_pass" value="<?php echo $php_mailer_pass; ?>">
      </div>
      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="basic-addon1">Cron Server IP</span>
        </div>
        <input type="text" class="form-control" placeholder="" aria-label="cron_server_ip" aria-describedby="basic-addon1" name="cron_server_ip" value="<?php echo $cron_server_ip; ?>">
      </div>
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-header">
      <?php Template::render_toggle("Enable Telegram Subscription", "telegram_subscription_toggle", $telegramSubscription_status); ?>
    </div>
    <div class="card-body">
      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="basic-addon1">Telegram BOT API Token</span>
        </div>
        <input type="text" class="form-control" placeholder="" aria-label="telegram_bot_api_token" aria-describedby="basic-addon1" name="tg_bot_api_token" value="<?php echo $tg_bot_api_token; ?>">
      </div>
      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="basic-addon1">Telegram BOT Username</span>
        </div>
        <input type="text" class="form-control" placeholder="" aria-label="telegram_bot_username" aria-describedby="basic-addon1" name="tg_bot_username" value="<?php echo $tg_bot_username; ?>">
      </div>
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-header">
      <?php Template::render_toggle("Use Google reChaptcha for subscriber signup", "google_rechaptcha_toggle", $google_rechaptcha_status); ?>
    </div>
    <div class="card-body">
      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="basic-addon1">Google reChaptcha Sitekey</span>
        </div>
        <input type="text" class="form-control" placeholder="" aria-label="google_sitekey" aria-describedby="basic-addon1" name="google_recaptcha_sitekey" value="<?php echo $google_recaptcha_sitekey; ?>">
      </div>
      <div class="input-group mb-3">
        <div class="input-group-prepend">
          <span class="input-group-text" id="basic-addon1">Google reChaptcha Secret</span>
        </div>
        <input type="text" class="form-control" placeholder="" aria-label="google_secret" aria-describedby="basic-addon1" name="google_recaptcha_secret" value="<?php echo $google_recaptcha_secret; ?>">
      </div>
    </div>
  </div>

  <div class="card mt-3 mb-3" style="border: none;">
    <button class="btn btn-primary float-end" type="submit">Save Settings</button>
  </div>
</form>