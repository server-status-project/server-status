<?php
require 'vendor/autoload.php';

if (!file_exists('config.php')) {
  include_once 'template.php';
  include_once 'install.php';
} elseif (isset($_GET['do'])) {
    // we can add other actions with $_GET['do'] later.
    // Fix for translation via _(). We need config.php first...
  include_once 'config.php';
  include_once 'template.php';


  switch ($_GET['do']) {
    case 'subscriptions':
      include_once 'subscriptions.php';
        break;

    case 'email_subscription':
    case 'manage':
    case 'unsubscribe';
      include_once 'email_subscriptions.php';
        break;

    default:
        // TODO : How to handle url invalid/unknown [do] commands
      header('Location: index.php');
        break;
  }
} else {
  include_once 'config.php';
  include_once 'template.php';
  include_once 'classes/constellation.php';
  include_once 'classes/db-class.php';
  $db = new SSDB();
  define('NAME', $db->getSetting($mysqli, 'name'));
  define('TITLE', $db->getSetting($mysqli, 'title'));
  define('WEB_URL', $db->getSetting($mysqli, 'url'));
  define('MAILER_NAME', $db->getSetting($mysqli, 'mailer'));
  define('MAILER_ADDRESS', $db->getSetting($mysqli, 'mailer_email'));

  define('SUBSCRIBE_EMAIL', $db->getBooleanSetting($mysqli, 'subscribe_email'));
  define('SUBSCRIBE_TELEGRAM', $db->getBooleanSetting($mysqli, 'subscribe_telegram'));
  define('TG_BOT_USERNAME', $db->getSetting($mysqli, 'tg_bot_username'));
  define('TG_BOT_API_TOKEN', $db->getSetting($mysqli, 'tg_bot_api_token'));
  define('GOOGLE_RECAPTCHA', $db->getBooleanSetting($mysqli, 'google_recaptcha'));
  define('GOOGLE_RECAPTCHA_SITEKEY', $db->getSetting($mysqli, 'google_recaptcha_sitekey'));
  define('GOOGLE_RECAPTCHA_SECRET', $db->getSetting($mysqli, 'google_recaptcha_secret'));
  $offset = 0;

  if (isset($_GET['ajax'])) {
    $constellation->render_incidents(false, $_GET['offset'], 5);
    exit();
  } elseif (isset($_GET['offset'])) {
    $offset = $_GET['offset'];
  }

  if (isset($_GET['subscriber_logout'])) {
    setcookie('tg_user', '');
    setcookie('referer', '', (time() - 3600));
    $_SESSION['subscriber_valid'] = false;
    unset($_SESSION['subscriber_userid']);
    unset($_SESSION['subscriber_typeid']);
    unset($_SESSION['subscriber_id']);
    header('Location: index.php');
  }

  Template::render_header('Status');
  ?>
    <div class="text-center">
      <h2><?php echo _('Current status'); ?></h2>
    </div>
    <div id="current">
  <?php $constellation->render_status(); ?>
    </div>

  <?php
  if ($mysqli->query('SELECT count(*) FROM status')->num_rows) {
    ?>
      <div id="timeline" class="timeline">
        <div class="line text-muted"></div>
    <?php
    $constellation->render_incidents(true, $offset);
    $constellation->render_incidents(false, $offset);
    ?>
      </div>
    <?php
  }

  Template::render_footer();
}//end if
