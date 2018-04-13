<?php

if (!file_exists("../config.php"))
{
  header("Location: ../");
}
else{
	require_once("../config.php");
	require_once("../classes/constellation.php");

	if (isset($_GET['future']) && $_GET['future'] == true)
	{
		$result = get_incidents(true);
	}else{
		$result = get_incidents();
	}
	
	echo json_encode($result);
}

function get_incidents($future = false){
	global $mysqli;
	$c = ($future)?">=":"<=";
  	$limit = (isset($_GET['limit'])?$_GET['limit']:5);
  	$offset = (isset($_GET['offset'])?$_GET['offset']:0);
    $timestamp = (isset($_GET['timestamp']))?$_GET['timestamp']:time();
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