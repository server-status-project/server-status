<?php

if (!file_exists("../config.php"))
{
  header("Location: ../");
}
else{
  require_once("../config.php");
  require_once("../classes/constellation.php");
  header('Cache-Control: no-cache');
  header('Content-type: application/json');

  if (!isset($_GET['id']))
  {
	  $array = $constellation->render_status(true, false);
	  echo json_encode($array);
  }else{
    $queryId = $mysqli->prepare("SELECT id as 'id' from services where id = ?;");
    $queryId->bind_param("i", $_GET['id']);
    $queryId->execute();
    $result = $queryId->get_result()->fetch_assoc();
    if (!count($result))
    {
    	die(json_encode(["error" => _("Service does not exist!")]));
    }

  	$query = $mysqli->prepare("select services.id, name, description, status.type from services inner join status on status.id = services.id where services.id = ?;");
  	$query->bind_param("i", $_GET['id']);
  	$query->execute();
    $result = $query->get_result()->fetch_assoc();

    if (is_numeric($result["type"])) {
      $service = new Service($_GET["id"], $result["name"], $result["description"], '', $result["type"]);
    } else {
      $service = new Service($_GET["id"], $result["name"], $result["description"]);
    }

    echo json_encode($service);
  }
}