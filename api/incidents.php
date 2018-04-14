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
		$result = $constellation->get_incidents(true);
	}else{
		$result = $constellation->get_incidents();
	}
	
	echo json_encode($result);
}