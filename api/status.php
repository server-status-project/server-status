<?php

if (!file_exists("../config.php")) {
  header("Location: ../");
} else {
  require_once("../config.php");
  require_once("../classes/constellation.php");
  header('Cache-Control: no-cache');
  header('Content-type: application/json');

  if (!isset($_GET['id'])) {
    $array = $constellation->render_status(true, false);
    echo json_encode($array);
  } else {
    $query = $mysqli->prepare("SELECT name FROM services WHERE id=?");
    $query->bind_param("i", $_GET['id']);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    if (!count($result)) {
      die(json_encode(["error" => _("Service does not exist!")]));
    }

    $sql = $mysqli->prepare("SELECT type FROM services_status INNER JOIN status ON services_status.status_id = status.id WHERE service_id = ? AND `time` <= ? AND (`end_time` >= ? OR `end_time`=0) ORDER BY `time` DESC LIMIT 1");

    $sql->bind_param("iii", $id, $timestamp, $timestamp);
    $sql->execute();
    $tmp = $sql->get_result();
    if ($tmp->num_rows) {
      $service = new Service($_GET['id'], $result['name'], $tmp->fetch_assoc()['type']);
    } else {
      $service = new Service($_GET['id'], $result['name']);
    }

    echo json_encode($service);
  }
}
