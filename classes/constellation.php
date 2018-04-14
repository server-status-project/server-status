<?php
//DIR Because of include problems
require_once(__DIR__ . "/incident.php");
require_once(__DIR__ . "/service.php");
require_once(__DIR__ . "/user.php");
require_once(__DIR__ . "/token.php");
/**
* Facade class
*/
class Constellation
{

  /**
   * Renders incidents matching specified constraints.
   * @param Boolean $future - specifies whether to render old or upcoming incidents
   * @param int $offset - specifies offset - used for pagination
   * @param int $limit - limits the number of incidents rendered
   * @param Boolean $admin - specifies whether to render admin controls
   */
  public function render_incidents($future=false, $offset=0, $limit = 5, $admin = 0){
    global $mysqli;
    if ($offset<0)
    {
      $offset = 0; 
    }

    $limit = (isset($_GET['limit'])?$_GET['limit']:5);
    $offset = (isset($_GET['offset'])?$_GET['offset']:0);
    $timestamp = (isset($_GET['timestamp']))?$_GET['timestamp']:time();

    $incidents = $this->get_incidents($future, $offset, $limit, $timestamp);

    $ajax = isset($_GET['ajax']);

    if ($future && count($incidents["incidents"]) && !$ajax)
    {
      echo "<h3>"._("Planned maintenance")."</h3>";
    }
    else if (count($incidents["incidents"]) &&!$ajax)
    {
      if ($offset) 
      {
        echo '<noscript><div class="centered"><a href="'.WEB_URL.'/?offset='.($offset-$limit).'&timestamp='.$timestamp.'" class="btn btn-default">'._("Back").'</a></div></noscript>';
      }
      echo "<h3>"._("Past incidents")."</h3>";
    }
    else if (!$future &&!$ajax)
    {
      echo "<h3>"._("No incidents")."</h3>";
    }
    $show = !$future && $incidents["more"];

    $offset += $limit;

    if (count($incidents["incidents"])){
      foreach ($incidents['incidents'] as $incident) {
        $incident->render($admin);
      }

      if ($show)
      {
        echo '<div class="centered"><a href="'.WEB_URL.'/?offset='.($offset).'&timestamp='.$timestamp.'" id="loadmore" class="btn btn-default">'._("Load more").'</a></div>';
      }
    }
  }

  /**
   * Renders service status - in admin page it returns array so it can be processed further.
   * @param boolean $admin
   * @return array of services 
   */
  public function render_status($admin = false, $heading = true){
    global $mysqli;
    
    $query = $mysqli->query("SELECT id, name  FROM services");
    $array = array();
    if ($query->num_rows){
      $timestamp = time();

      while($result = $query->fetch_assoc())
      {
        $id = $result['id'];
        $sql = $mysqli->prepare("SELECT type FROM services_status INNER JOIN status ON services_status.status_id = status.id WHERE service_id = ? AND `time` <= ? AND (`end_time` >= ? OR `end_time`=0) ORDER BY `time` DESC LIMIT 1");

        $sql->bind_param("iii", $id, $timestamp, $timestamp);
        $sql->execute();
        $tmp = $sql->get_result();
        if ($tmp->num_rows)
        {
          $array[] = new Service($result['id'], $result['name'], $tmp->fetch_assoc()['type']);
        }
        else{
          $array[] = new Service($result['id'], $result['name']);
        }
      }      
      if ($heading)
      {
        echo Service::current_status($array);
      }
    }
    else{
      $array[] = new Service(0, _("No services"), -1);
    }
    if (!$admin)
    {
      echo '<div id="status-container" class="clearfix">';
      foreach($array as $service){
        $service->render();
      }
      echo '</div>';
    }
    else{
      return $array;
    }
  }


  function get_incidents($future = false, $offset = 0, $limit = 5, $timestamp = 0){
    global $mysqli;
    if ($timestamp == 0)
    {
      $timestamp = time();
    }

    $c = ($future)?">=":"<=";
    $limit++;
    $sql = $mysqli->prepare("SELECT *, status.id as status_id FROM status INNER JOIN users ON user_id=users.id WHERE `time` $c ? AND `end_time` $c ?  OR (`time`<=? AND `end_time` $c ? ) ORDER BY `time` DESC LIMIT ? OFFSET ?");
    $sql->bind_param("iiiiii",$timestamp, $timestamp, $timestamp, $timestamp, $limit, $offset);
    $sql->execute();
    $query = $sql->get_result();
    $array = [];
    $limit--;
    $more = false;
    if ($query->num_rows>$limit){
      $more = true; 
    }
    if ($query->num_rows){
      while(($result = $query->fetch_assoc()) && $limit-- > 0)
      {
        $array[] = new Incident($result);
      }
    }
    return [
      "more" => $more,
      "incidents" => $array
    ];
  }
}          

$constellation = new Constellation();