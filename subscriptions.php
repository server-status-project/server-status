<?php
require_once("config.php");
require_once("template.php");
require_once("classes/constellation.php");
require_once("classes/subscriptions.php");
require_once("classes/telegram.php");
require_once("classes/db-class.php");
$db = new SSDB();
define("NAME", $db->getSetting($mysqli, "name"));
define("TITLE", $db->getSetting($mysqli, "title"));
define("WEB_URL", $db->getSetting($mysqli, "url"));
define("MAILER_NAME", $db->getSetting($mysqli, "mailer"));
define("MAILER_ADDRESS", $db->getSetting($mysqli, "mailer_email"));
define("SUBSCRIBE_EMAIL", $db->getBooleanSetting($mysqli, "subscribe_email"));
define("SUBSCRIBE_TELEGRAM", $db->getBooleanSetting($mysqli, "subscribe_telegram"));
define("GOOGLE_RECAPTCHA", $db->getSetting($mysqli, "google_recaptcha"));
define("GOOGLE_RECAPTCHA_SECRET", $db->getSetting($mysqli, "google_recaptcha_secret"));
define("GOOGLE_RECAPTCHA_SITEKEY", $db->getSetting($mysqli, "google_recaptcha_sitekey"));
define("TG_BOT_API_TOKEN", $db->getSetting($mysqli, "tg_bot_api_token"));
define("TG_BOT_USERNAME", $db->getSetting($mysqli, "tg_bot_username"));

$subscription = new Subscriptions();
$telegram     = new Telegram();

Template::render_header("Subscriptions", "subscripe");

if (SUBSCRIBE_TELEGRAM && $_SESSION['subscriber_typeid'] == 2) {
    $tg_user = $telegram->getTelegramUserData();    // TODO: Do we need this any longer?
}

if ($_SESSION['subscriber_valid']) {

    $typeID       = $_SESSION['subscriber_typeid'];
    $subscriberID = $_SESSION['subscriber_id'];
    $userID       = $_SESSION['subscriber_userid'];
    $token        = $_SESSION['subscriber_token'];

    if (isset($_GET['add'])) {
        $subscription->add($subscriberID, $_GET['add']);
    }

    if (isset($_GET['remove'])) {
        $subscription->remove($subscriberID, $_GET['remove']);
    }

    $subscription->render_subscribed_services($typeID, $subscriberID, $userID, $token);
} else {

    $header = _("Your session has expired or you tried something we don't suppprt");
    $message = _('If your session expired, retry your link or in case of Telegram use the login button in the top menu.');
    $constellation->render_warning($header, $message);

    header('Location: index.php');
}

Template::render_footer();
