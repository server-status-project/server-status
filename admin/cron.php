<?php
if (!file_exists("../config.php")) {
  header("Location: ../");
} else {
  require_once("../config.php");
  header('Cache-Control: no-cache');

  /**
   * Check the status
   * @param String $url url or ip to check
   * @param int $id id to the fetched url
   * @param String $name name to the fetched url
   * @param Objekt $mysqli mysql connection info from config.php
   */
  function check($url, $id, $name, $mysqli) {
    $status_time = time();
    $status_user_id = "3";
    $ishttp = preg_match("/^http(s)?:/", $url);
    if ($ishttp) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);
      $http_respond = curl_exec($ch);
      $http_respond = trim( strip_tags( $http_respond ) );
      $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
      if ( ( $http_code >= "200" ) && ( $http_code < "300" ) ) {
        $status_text = "Running without Problems";
        #if (lastOffline($mysqli, $id)) {
        #  writeStatus($mysqli, $id, "3", "Online check", $status_text, time(), "0", $status_user_id);
        #}
        echo "<p style='color:green'>" . $id . " " . $name . "<br>RESPONDE: " . $status_text . "</p>";
      } else {
        $http_codes = parse_ini_file("http_codes.ini");
        $status_text = $http_code . " " . $http_codes[$http_code];
        # writeStatus($mysqli, $id, "1", "Online check", $status_text, time(), "0", $status_user_id);
        echo "<p style='color:red'>" . $id . " " . $name . "<br>RESPONDE: " . $status_text . "</p>";
      }
      curl_close( $ch );
    } else {
      list($ip, $port) = preg_split("/:/", $url);
      if (fsockopen($ip, $port)) {
        $status_text = "Running without Problems";
        #if (lastOffline($mysqli, $id)) {
        #  writeStatus($mysqli, $id, "3", "Online check", $status_text, time(), "0", $status_user_id);
        #}
        echo "<p style='color:green'>" . $id . " " . $name . "<br>RESPONDE: " . $status_text . "</p>";
      } else {
        $status_text = "Can't reach the Server";
        # writeStatus($mysqli, $id, "1", "Online check", $status_text, time(), "0", $status_user_id);
        echo "<p style='color:red'>" . $id . " " . $name . "<br>RESPONDE: " . $status_text . "</p>";
      }
    }
  }

  /**
   * Write the Status into DB
   * @param Objekt $mysqli mysql connection info from config.php
   * @param int $status_service_id id of the service currently being checked
   * @param int $status_type Error type 0: Major outage 1: Minor outage 2: Planned maintenance 3: Operational
   * @param String $status_title 
   * @param String $status_text 
   * @param int $status_time 
   * @param int $status_end_time only used for planned maintenance otherwise 0
   * @param int $status_user_id id of the user
   */
  function writeStatus($mysqli, $status_service_id, $status_type, $status_title, $status_text, $status_time, $status_end_time, $status_user_id)
  {
    $status_query = $mysqli->prepare("INSERT INTO status VALUES (NULL,?, ?, ?, ?, ?, ?)");
    $status_query->bind_param("issiii", $status_type, $status_title, $status_text, $status_time, $status_end_time, $status_user_id);
    $status_query->execute();
    $status_status_id = $mysqli->insert_id;

    $services_status = $mysqli->prepare("INSERT INTO services_status VALUES (NULL,?, ?)");
    $services_status->bind_param("ii", $status_service_id, $status_status_id);
    $services_status->execute();

    $status_query->close();
    $services_status->close();
  }

  /**
   * Write the Status into DB
   * @param Objekt $mysqli mysql connection info from config.php
   * @param int $status_service_id id of the service currently being checked
   */
  function lastOffline($mysqli, $status_service_id)
  {
    $status_id_type = $mysqli->query("SELECT 'status_id'.'type' FROM 'services_status'.'status' WHERE 'service_id' = $status_service_id AND 'status'.'id' = 'services_status'.'status_id' ORDER BY 'id' DESC limit 1");
    if ($status_id_type == "1") {
      return true;
    } else {
      return false;
    }
  }

  $services = $mysqli->query("SELECT * FROM `services` WHERE 'url' IS NOT NULL");

  while ($service = $services->fetch_array()) {
    if ("$service[url]") {
      check("$service[url]", "$service[id]", "$service[name]", $mysqli);
    }
  }
  $mysqli->close();
}
?>