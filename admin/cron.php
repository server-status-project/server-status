<?php
if (!file_exists("../config.php")) {
  header("Location: ../");
} else {
  require_once("../config.php");
  header('Cache-Control: no-cache');
  $status_user_id = "1";

  /**
   * Check the status
   * @param String $url url or ip to check
   * @param bool $repeat repeat of the test
   * @return Array online true/false, url/ip, httpcode
   */
  function check($url, $repeat)
  {
    $ishttp = preg_match("/^http(s)?:/", $url);
    if ($ishttp) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 10);
      $http_respond = curl_exec($ch);
      $http_respond = trim(strip_tags($http_respond));
      $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      if (($http_code >= "200") && ($http_code < "300")) {
        return array(true, "url", $http_code);
      } else {
        if ($repeat) {
          return false;
        } else {
          return array(false, "url", $http_code);
        }
      }
      curl_close($ch);
    } else {
      list($ip, $port) = preg_split("/:/", $url);
      if (fsockopen($ip, $port)) {
        return array(true, "ip", "");
      } else {
        if ($repeat) {
          return false;
        } else {
          return array(false, "ip", "");
        }
      }
    }
  }

  /**
   * Write the Status into DB
   * @param Objekt $mysqli mysql connection info from config.php
   * @param int $status_type Error type 0: Major outage 1: Minor outage 2: Planned maintenance 3: Operational
   * @param int $status_service_id id of the service currently being checked
   * @param int $status_user_id the User id
   * @return bool true/false
   */
  function lastOffline($mysqli, $status_service_id, $status_user_id)
  {
    $result = $mysqli->query("select * from status, services_status where services_status.service_id = '$status_service_id' AND status.id = services_status.status_id AND status.user_id = '$status_user_id' ORDER BY `status`.`id` DESC limit 1;");
    $status_id_types = $result->fetch_row();
    if (isset($status_id_types[0])) {
      if ($status_id_types[1] == "0") {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  /**
   * Write the Status into DB
   * @param Objekt $mysqli mysql connection info from config.php
   * @param int $status_service_id id of the service currently being checked
   * @param int $status_end_time time against which is checked (normally: current time)
   * @return bool true/false
   */
  function maintenance($mysqli, $status_service_id, $status_end_time)
  {
    $result = $mysqli->query("select * from status, services_status where services_status.service_id = '$status_service_id' AND status.id = services_status.status_id AND status.end_time >= '$status_end_time' AND `end_time` != '0' ORDER BY `status`.`id` DESC limit 1;");
    $status_rows = $result->fetch_row();
    if (isset($status_rows[0])) {
      if ($status_rows[1] == "2") {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
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

  $services = $mysqli->query("SELECT * FROM `services` WHERE 'url' IS NOT NULL");

  while ($service = $services->fetch_array()) {
    if ("$service[url]") {
      $status_id = "$service[id]";
      $status_name = "$service[name]";
      list($isonline, $adresstype, $http_code) = check("$service[url]", false);

      if ($isonline) {
        $status_text = "Running without Problems";
        echo "<p style='color:green'>" . $status_id . " " . $status_name;
        if (lastOffline($mysqli, $status_id, $status_user_id)) {
          echo " <span style='color:orange'> - new online</span>";
          writeStatus($mysqli, $status_id, "3", "Online check", $status_text, time(), "0", $status_user_id);
        } else {
          echo " <span style='color:orange'> - old online</span>";
        }
        echo "<br>RESPONDE: " . $status_text . "</p>";
      } else {
        if ($adresstype == "url") {
          $http_codes = parse_ini_file("http_codes.ini");
          $status_text = $http_code . " " . $http_codes[$http_code];
        } else {
          $status_text = "Can't reach the Server";
        }
        echo "<p style='color:red'>" . $status_id . " " . $status_name;
        if (!lastOffline($mysqli, $status_id, $status_user_id)) {
          echo "<span style='color:orange'> - new offline</span>";
          if (!maintenance($mysqli, $status_id, time())) {
            sleep(10);
            if (!check("$service[url]", true)) {
              echo "<span style='color:orange'>, still offline</span>";
              writeStatus($mysqli, $status_id, "0", "Online check", $status_text, time(), "0", $status_user_id);
            }
          } else {
            echo "<span style='color:orange'>, maintenance found</span>";
          }
        } else {
          echo "<span style='color:orange'> - old offline</span>";
        }
        echo "<br>RESPONDE: " . $status_text . "</p>";
      }
    }
  }
  $mysqli->close();
}
