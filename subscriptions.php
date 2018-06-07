<?php 
require_once("template.php");
require_once("config.php");
require_once("classes/constellation.php");
Template::render_header("Subscriptions");
$tg_user = getTelegramUserData();

if($tg_user !== false){

	if(isset($_GET['add'])){
		$service = $_GET['add'];
		$query = $mysqli->query("SELECT * FROM subscribers WHERE telegramID=" . $tg_user['id']);
		while($subscriber = $query->fetch_assoc()){
		  $subscriberID = $subscriber['subscriberID'];
		}
		$stmt = $mysqli->prepare("INSERT INTO services_subscriber VALUES (NULL,?, ?)"); 
        $stmt->bind_param("ii", $subscriberID, $service);
        $stmt->execute();
		$query = $stmt->get_result();
		header("Location: index.php?do=subscriptions");
	}

	if(isset($_GET['remove'])){
		$service = $_GET['remove'];
		$query = $mysqli->query("SELECT * FROM subscribers WHERE telegramID=" . $tg_user['id']);
		while($subscriber = $query->fetch_assoc()){
		  $subscriberID = $subscriber['subscriberID'];
		}
		$stmt = $mysqli->prepare("DELETE FROM services_subscriber WHERE subscriberIDFK = ? AND serviceIDFK = ?");
		$stmt->bind_param("ii", $subscriberID, $service);
		$stmt->execute();
		$query = $stmt->get_result();
		header("Location: index.php?do=subscriptions");
	}

	$query = $mysqli->query("SELECT services.id, services.name, subscribers.subscriberID, subscribers.telegramID
	FROM services
		LEFT JOIN services_subscriber ON services_subscriber.serviceIDFK = services.id
		LEFT JOIN subscribers ON services_subscriber.subscriberIDFK = subscribers.subscriberID
		WHERE subscribers.telegramID =" . $tg_user['id']);
if ($query->num_rows){
	$timestamp = time();
	echo '<h1>' . _("Your subscriptions") . "</h1>";
	echo '<div class="list-group">';
	$subs = array();
	while($result = $query->fetch_assoc())
	{
		echo '<a href="https://status.jhuesser.ch/subscriptions.php?remove=' . $result['id'] .'" class="list-group-item">' . $result['name'] . '</a>';
		$subs[] = $result['name'];
	}
	echo "</div>";
}

echo '<h1>' . _("Add new subscription") . '</h1>';

$query = $mysqli->query("SELECT services.id, services.name from services");
if ($query->num_rows){
	echo '<div class="list-group">';

	while($result = $query->fetch_assoc()){
		if(empty($subs)){
			echo '<a href="https://status.jhuesser.ch/subscriptions.php?add=' . $result['id'] . '" class="list-group-item list-group-item-action">' . $result['name'] . '</a>';

		} elseif(!in_array($result['name'], $subs)){
			echo '<a href="https://status.jhuesser.ch/subscriptions.php?add=' . $result['id'] . '" class="list-group-item list-group-item-action">' . $result['name'] . '</a>';
		}
	}
	echo '</div>';
}

} else{
	header('Location: index.php');
}

Template::render_footer();