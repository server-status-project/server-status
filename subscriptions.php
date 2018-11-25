<?php 
require_once("config.php");
require_once("template.php");
require_once("classes/constellation.php");
require_once("classes/subscriptions.php");
require_once("classes/telegram.php");

$subscription = new Subscriptions();
$telegram     = new Telegram();

Template::render_header("Subscriptions");

if ( SUBSCRIBE_TELEGRAM && $_SESSION['subscriber_typeid'] == 2 ) {
    $tg_user = $telegram->getTelegramUserData();    // TODO: Do we need this any longer?
}

if( $_SESSION['subscriber_valid'] ){
    
    $typeID       = $_SESSION['subscriber_typeid'];
    $subscriberID = $_SESSION['subscriber_id'];   
    $userID       = $_SESSION['subscriber_userid'];
    $token        = $_SESSION['subscriber_token'];
    
    if(isset($_GET['add'])){
        $subscription->add($subscriberID, $_GET['add']);
    }

    if(isset($_GET['remove'])){
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
