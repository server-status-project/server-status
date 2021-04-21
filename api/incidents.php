<?php

if (!file_exists("../config.php")) {
	header("Location: ../");
} else {
	require_once("../config.php");
	require_once("../classes/constellation.php");

	$limit = (isset($_GET['limit']) ? $_GET['limit'] : 5);
	$offset = (isset($_GET['offset']) ? $_GET['offset'] : 0);
	$timestamp = (isset($_GET['timestamp'])) ? $_GET['timestamp'] : time();

	$result = $constellation->get_incidents((isset($_GET['future']) ? $_GET['future'] : false), $offset, $limit, $timestamp);
	header('Cache-Control: no-cache');
	header('Content-type: application/json');
	echo json_encode($result);
}
