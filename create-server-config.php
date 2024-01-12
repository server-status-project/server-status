<?php
/********************************************************************/
//                       create-server-config.php
//                  Created by Yigit Kerem Oktay
// This file generates a .htaccess file that contains all necessary
// code for it.
// This is needed because some hosts do not either unzip hidden files
// or neither GitHub puts that file inside the zips.
/********************************************************************/
$apacheExampleName = "ApacheHtaccess";
$apacheProductionName = ".htaccess";
$iisExampleName = "IISWebConfig";
$iisProductionName = "web.config";
if(stripos($_SERVER['SERVER_SOFTWARE'],'apache')!== false){
  if(!file_exists($apacheProductionName)) {
    $f = fopen($apacheProductionName, "a+");
    $f2 = fopen($apacheExampleName,"r");
    fwrite($f, fread($f2, filesize($apacheExampleName)));
    fclose($f);
    fclose($f2);
  }
// skipping renaming file if it already exists
} else {
  if(!file_exists($iisProductionName)) {
    $f = fopen($iisProductionName, "a+");
    $f2 = fopen($iisExampleName,"r");
    fwrite($f, fread($f2, filesize($iisExampleName)));
    fclose($f);
    fclose($f2);
  }
}
?>
