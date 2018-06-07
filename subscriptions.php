<?php 
require_once("template.php");
require_once("config.php");
require_once("classes/constellation.php");
Template::render_header("Subscriptions");
$tg_user = getTelegramUserData();

if($tg_user !== false){

	$query = $mysqli->query("SELECT services.id, services.name, subscribers.subscriberID, subscribers.telegramID
	FROM services
		LEFT JOIN services_subscriber ON services_subscriber.serviceIDFK = services.id
		LEFT JOIN subscribers ON services_subscriber.subscriberIDFK = subscribers.subscriberID
		WHERE subscribers.telegramID =" . $tg_user['id']);
//$query = $mysqli->query("SELECT id, name  FROM services");
if ($query->num_rows){
	$timestamp = time();
	echo '<h1>' . _("Your subscriptions") . "</h1>";
	echo '<ul class="list-group">';
	while($result = $query->fetch_assoc())
	{
		echo '<li class="list-group-item">' . $result['name'] . '</li>';
	}
	echo "</ul>";
}
} else{
	header('Location: index.php');
}

Template::render_footer();