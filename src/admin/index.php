<?php

if (!file_exists('../config.php')) {
  header('Location: ../');
} else {
  include_once '../config.php';
  include_once '../classes/constellation.php';
  include_once '../classes/mailer.php';
  include_once '../classes/notification.php';
  include_once '../template.php';
  include_once '../classes/queue.php';
  include_once '../classes/db-class.php';
  $db = new SSDB();
  define('NAME', $db->getSetting($mysqli, 'name'));
  define('TITLE', $db->getSetting($mysqli, 'title'));
  define('WEB_URL', $db->getSetting($mysqli, 'url'));
  define('MAILER_NAME', $db->getSetting($mysqli, 'mailer'));
  define('MAILER_ADDRESS', $db->getSetting($mysqli, 'mailer_email'));

  define('GOOGLE_RECAPTCHA', $db->getBooleanSetting($mysqli, 'google_recaptcha'));
  define('GOOGLE_RECAPTCHA_SECRET', $db->getSetting($mysqli, 'google_recaptcha_secret'));
  define('GOOGLE_RECAPTCHA_SITEKEY', $db->getSetting($mysqli, 'google_recaptcha_sitekey'));
  define('SUBSCRIBE_EMAIL', $db->getBooleanSetting($mysqli, 'subscribe_email'));
  define('SUBSCRIBE_TELEGRAM', $db->getBooleanSetting($mysqli, 'subscribe_telegram'));
  define('TG_BOT_USERNAME', $db->getSetting($mysqli, 'tg_bot_username'));
  define('TG_BOT_API_TOKEN', $db->getSetting($mysqli, 'tg_bot_api_token'));
  define('PHP_MAILER', $db->getBooleanSetting($mysqli, 'php_mailer'));
  define('PHP_MAILER_SMTP', $db->getBooleanSetting($mysqli, 'php_mailer_smtp'));
  define('PHP_MAILER_PATH', $db->getSetting($mysqli, 'php_mailer_path'));
  define('PHP_MAILER_HOST', $db->getSetting($mysqli, 'php_mailer_host'));
  define('PHP_MAILER_PORT', $db->getSetting($mysqli, 'php_mailer_port'));
  define('PHP_MAILER_SECURE', $db->getBooleanSetting($mysqli, 'php_mailer_secure'));
  define('PHP_MAILER_USER', $db->getSetting($mysqli, 'php_mailer_user'));
  define('PHP_MAILER_PASS', $db->getSetting($mysqli, 'php_mailer_pass'));
  define('CRON_SERVER_IP', $db->getSetting($mysqli, 'cron_server_ip'));

    // Process the subscriber notification queue
    // If CRON_SERVER_IP is not set, call notification once incident has been saved
  if (empty(CRON_SERVER_IP)) {
    if (isset($_GET['sent']) && $_GET['sent'] == true) {
      Queue::process_queue();
    }
  } elseif (isset($_GET['task']) && $_GET['task'] == 'cron') {
      // Else, base it on call to /admin?task=cron being called from IP defined by CRON_SERVER_IP
    if (! empty(CRON_SERVER_IP) && $_SERVER['REMOTE_ADDR'] == CRON_SERVER_IP) {
      Queue::process_queue();
      syslog(1, 'CRON server processed');
    } else {
      syslog(1, 'CRON called from unauthorised server');
    }
  }


  if (isset($_COOKIE['user']) && !isset($_SESSION['user'])) {
    User::restore_session();
  }

  if (!isset($_SESSION['user'])) {
    if (isset($_GET['do']) && $_GET['do'] == 'lost-password') {
      include_once 'lost-password.php';
    } elseif (isset($_GET['do']) && $_GET['do'] == 'change-email') {
      $user_pwd = new User($_GET['id']);
      $user_pwd->change_email();
      include_once 'login-form.php';
    } else {
      User::login();
      include_once 'login-form.php';
    }
  } else {
    $user = new User($_SESSION['user']);
    if (!$user->is_active()) {
      User::logout();
    }

    if (!isset($_GET['do'])) {
      $do = '';
    } else {
      $do = $_GET['do'];
    }

    switch ($do) {
      case 'change-email':
        $user = new User($_GET['id']);
        $user->change_email();
      case 'user':
        include_once 'user.php';
          break;

      case 'settings':
        include_once 'settings.php';
          break;

      case 'new-user':
        include_once 'new-user.php';
          break;

      case 'new-service':
      case 'edit-service':
        include_once 'service.php';
          break;

      case 'new-service-group':
      case 'edit-service-group':
        include_once 'service-group.php';
          break;

      case 'options':
        include_once 'options.php';
          break;

      case 'logout':
        User::logout();
          break;

      default:
        include_once 'dashboard.php';
          break;
    }//end switch

    Template::render_footer(true);
  }//end if
}//end if
