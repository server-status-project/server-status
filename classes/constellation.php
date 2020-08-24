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

    $operator = ($future)?">=":"<=";
    $limit++;
    $sql = $mysqli->prepare("SELECT users.id, status.type, status.title, status.text, status.time, status.end_time, users.username, status.id as status_id FROM status INNER JOIN users ON user_id=users.id WHERE `time` $operator ? AND `end_time` $operator ?  OR (`time`<=? AND `end_time` $operator ? ) ORDER BY `time` DESC LIMIT ? OFFSET ?");
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
        // Add service id and service names to an array in the Incident class
        $stmt_service = $mysqli->prepare("SELECT services.id,services.name FROM services 
                                                 INNER JOIN services_status ON services.id = services_status.service_id 
                                                 WHERE services_status.status_id = ?");
        $stmt_service->bind_param("i", $result['status_id']);
        $stmt_service->execute();
        $query_service = $stmt_service->get_result();
        while($result_service = $query_service->fetch_assoc()) {
          $result['service_id'][] = $result_service['id'];
          $result['service_name'][] = $result_service['name'];
        }

        $array[] = new Incident($result);
      }
    }
    return [
      "more" => $more,
      "incidents" => $array
    ];
  }
  
  
  function render_warning($header, $message, $show_link = false, $url = null, $link_text = null)
  {
    $this->render_alert('alert-warning', $header, $message, $show_link, $url, $link_text);  
  }
  function render_success($header, $message, $show_link = false, $url = null, $link_text = null)
  {
    $this->render_alert('alert-success', $header, $message, $show_link, $url, $link_text);
  }
  
  /**
   * Renders an alert on screen with an optional button to return to a given URL
   * @param string alert_type - Type of warning to render alert-danger, alert-warning, alert-success etc
   * @param string header - Title of warning
   * @param string message - Message to display
   * @param boolean show_link - True if button is to be displayed
   * @param string url - URL for button
   * @param string link_txt - Text for button
   * @return void
   */
  function render_alert($alert_type, $header, $message, $show_link = false, $url = null, $link_text = null)
  {
    echo '<div><h1></h1>
         <div class="alert '.$alert_type.'" role="alert">
         <h4 class="alert-heading">'.$header.'</h4>
         <hr>
         <p class="mb-0">'.$message.'</p>
         </div></div>';
    if ( $show_link ) {
      echo '<div class="clearfix"><a href="'.$url.'" class="btn btn-success" role="button">'.$link_text.'</a></div>';
    }
      
  }
}

$constellation = new Constellation();