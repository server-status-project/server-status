<?php

if (!file_exists("../config.php"))
{
  header("Location: ../");
}
else{
  require_once("../config.php");
  require_once("../classes/constellation.php");

}