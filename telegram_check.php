<?php
require_once ("config.php");
require_once ("classes/telegram.php");
require_once ("classes/subscriber.php");

$telegram   = new Telegram();
$subscriber = new Subscriber();

try {
    $auth_data = $telegram->checkTelegramAuthorization($_GET);
    $telegram->saveTelegramUserData($auth_data);
} catch (Exception $e) {
    die($e->getMessage());
}

// Check if user is registered in DB
$subscriber->firstname = $auth_data['first_name'];
$subscriber->lastname  = $auth_data['last_name'];
$subscriber->typeID    = 1;
$subscriber->userID    = $auth_data['id'];
$subscriber->active    = 1; // Telegram user should always be active if they can be validated

$subscriber_id  = $subscriber->get_subscriber_by_userid(true); // If user does not exists, create it
$subscriber->id = $subscriber_id;

// make sure we don't have a logged in email subscriber
$subscriber->set_logged_in();
//$_SESSION['subscriber_valid'] = true;
//$_SESSION['subscriber_typeid'] = 1;
//$_SESSION['subscriber_userid'] = $auth_data['id'];
//$_SESSION['subscriber_id'] = $subscriber_id;

header('Location: subscriptions.php');
