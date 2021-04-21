<?php
/********************************************************************/
//                       create-server-config.php
//                  Created by Yigit Kerem Oktay
// This file generates a .htaccess file that contains all necessary
// code for it.
// This is needed because some hosts do not either unzip hidden files
// or neither GitHub puts that file inside the zips.
/********************************************************************/
if(stripos($_SERVER['SERVER_SOFTWARE'],'apache')!== false){
$f = fopen(".htaccess", "a+");
$f2 = fopen("ApacheHtaccess","r");
fwrite($f, fread($f2, filesize("ApacheHtaccess")));
fclose($f);
fclose($f2);
} else {
$f = fopen("web.config", "a+");
$f2 = fopen("IISWebConfig","r");
fwrite($f, fread($f2, filesize("IISWebConfig")));
fclose($f);
fclose($f2);
}
