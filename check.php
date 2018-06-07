<?php
require_once("config.php");
require_once("telegram.php");

try {
	$auth_data = checkTelegramAuthorization($_GET);
	saveTelegramUserData($auth_data);
  } catch (Exception $e) {
	die ($e->getMessage());
  }
  header('Location: index.php');
  ?>