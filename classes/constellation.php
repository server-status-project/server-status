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
    $timenow = new DateTime('NOW');
    $dPrev = null;
    $dateIPrev = null;

    // Check to see if call is coming from the admin page. If loaded from the admin page
    // the date headers will not be rendered.
    // TODO: Should be an eaiser way to determine this...
    $isonadmin = false;
    // Handles default page load
    $arr_url = explode("/", $_SERVER['PHP_SELF']);
    if ( 'admin' == strtolower($arr_url[count($arr_url)-2]) ) {
      $isonadmin = true;
    }
    // Handles calls that comes from the "Load More" button.
    if (isset( $_SERVER['HTTP_REFERER']) ) {
      $arr_url = explode("/", $_SERVER['HTTP_REFERER']);
      if ( 'admin'== strtolower($arr_url[count($arr_url)-2]) ) {  // Get last array
        $isonadmin = true;  // We are on the admin dashboard, so prevent rendering of incident dates headers
      }
    }
    // Loop over dataset and create missing dates
    if (count($incidents["incidents"])){

       // Get previous date from last loaded incident via GET to indicate where the rendering starts from
       if (isset($_GET['lastviewed']) && is_numeric($_GET['lastviewed']) ) {
          $timenow->setTimestamp($_GET['lastviewed']);
          $dateIPrev =  $timenow->format("Y-m-d");
       }

      foreach ($incidents['incidents'] as $incident) {

        // Check if first incident is this date or earlier
        // If earlier -> calc number of days and add no-incident display for timeperiod leading up to
	
        $dateIncident = new DateTime();
        $dateIncident->setTimestamp($incident->timestamp);
        $days = $timenow->diff($dateIncident)->format('%a');
        if ( $days > 1 ) {
          for ($i = 0; $i <= $days; $i++) {
            $subs = "-" . $i . " days";
            $timenowM = clone $timenow;	// Clone object to avoid master object to change during processing
            $timenowM->modify($subs);	// Store -x days on cloned object
            $datestr = $timenowM->format("Y-m-d");
            $ordinal = $timenowM->format("S");

            $datept1 = ucwords(strftime("%A ", $timenowM->getTimestamp()));
            $datept2 = date("j", $timenowM->getTimestamp());
            $datept3 = _($ordinal) . ucwords(strftime(" %B %Y", $timenowM->getTimestamp()));
            $fulldatestr = $datept1 . $datept2 . $datept3;

            $datestrI = substr($incident->date, 0, 10);

            if  ( ($datestr != $datestrI) && ($datestr != $dateIPrev)) {
              $incident->render_no_incident($fulldatestr, $isonadmin);
            }

            $dateIPrev = $datestrI;
          }
        }
        $dateIPrev = $dateIncident->format("Y-m-d");

        // Check if we need to add date header for incident being rendered. 
        // It will only be rendered once per date
        if ( $dPrev != $dateIPrev ) {
          $dPrev = $dateIPrev;
          $ordinal = $dateIncident->format("S");
          $datept1 = ucwords(strftime("%A ", $dateIncident->getTimestamp()));
          $datept2 = date("j", $dateIncident->getTimestamp());
          $datept3 = _($ordinal) . ucwords(strftime(" %B %Y", $dateIncident->getTimestamp()));
          $strIncDate = $datept1 . $datept2 . $datept3;
        }

        $timenow->setTimestamp($incident->timestamp);
        $incident->render($admin, $strIncDate, $isonadmin);
        $strIncDate = null;	// Reset field so it isn't repeated  for the multiple incidents
      }

      if ($show)
      {
        echo '<div class="centered"><a href="'.WEB_URL.'/?offset='.($offset).'&timestamp='.$timestamp.'&lastviewed='.$incident->timestamp.'" id="loadmore" class="btn btn-default">'._("Load more").'</a></div>';
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
}          

$constellation = new Constellation();