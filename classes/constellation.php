<?php
//DIR Because of include problems
require(__DIR__ . "/incident.php");
require(__DIR__ . "/service.php");
require(__DIR__ . "/user.php");
require(__DIR__ . "/token.php");
/**
* Facade class
*/
class Constellation
{

  public function render_incidents($future=false, $offset=0, $limit = 5, $admin = 0){
    global $mysqli;
    if ($offset<0)
    {
      $offset = 0; 
    }
    if (isset($_GET['ajax']))
    {
       $ajax = true;
    }
    $limit++;
    $c = ($future)?">=":"<=";
    $timestamp = (isset($_GET['timestamp'])&& !$future)?$_GET['timestamp']:time();
    $sql = $mysqli->prepare("SELECT *, status.id as status_id FROM status INNER JOIN users ON user_id=users.id WHERE `time` $c ? AND `end_time` $c ?  OR (`time`<=? AND `end_time` $c ? ) ORDER BY `time` DESC LIMIT ? OFFSET ?");
    $sql->bind_param("iiiiii",$timestamp, $timestamp, $timestamp, $timestamp, $limit, $offset);
    $sql->execute();
    $query = $sql->get_result();
    if ($future && $query->num_rows && !$ajax)
    {
      echo "<h3>"._("Planned maintenance")."</h3>";
    }
    else if ($query->num_rows &&!$ajax)
    {
      if ($offset) 
      {
        echo '<noscript><div class="centered"><a href="?offset='.($offset-$limit+1).'&timestamp='.$timestamp.'" class="btn btn-default">'._("Back").'</a></div></noscript>';
      }
      echo "<h3>"._("Past incidents")."</h3>";
    }
    else if (!$future &&!$ajax)
    {
      echo "<h3>"._("No incidents")."</h3>";
    }
    $show = !$future && $query->num_rows==$limit;
    $limit--;
    $offset += $limit;

    if ($query->num_rows){
      while(($result = $query->fetch_assoc()) && $limit-->0)
      {
        $incident = new Incident($result);
        $incident->render($admin);
      }
      if ($show)
      {
        echo '<div class="centered"><a href="?offset='.($offset).'&timestamp='.$timestamp.'" id="loadmore" class="btn btn-default">'._("Load more").'</a></div>';
      }
    }
  }

  public function render_status($admin = 0){
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

      echo Service::current_status($array);
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
}          

$constellation = new Constellation();