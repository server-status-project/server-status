<?php


namespace SkyfallenCodeLibrary;


class UpdatesConsoleConnector
{
    public static function getLatestVersion($appid,$seed,$remoteauthority){
        $vajson = file_get_contents($remoteauthority."/updatecheck/?appid=".$appid."&seed=".$seed);
        $va_array = json_decode($vajson,true);
        return $va_array["version"];
    }
    public static function getLatestVersionData($appid,$seed,$remoteauthority){
        $vajson = file_get_contents($remoteauthority."/updatecheck/?appid=".$appid."&seed=".$seed);
        $va_array = json_decode($vajson,true);
        return $va_array;
    }
    public static function downloadLatestVersion($appid,$seed,$remoteauthority,$rootpath = "../"){
        $vajson = file_get_contents($remoteauthority."/updatecheck/?appid=".$appid."&seed=".$seed);
        $va_array = json_decode($vajson,true);
        $pkgloc = $va_array["downloadpath"];

        $file_name = basename($pkgloc);

        if(file_put_contents( $rootpath.$file_name,file_get_contents($pkgloc))) {
            $arr["path"] = $rootpath.$file_name;
            $arr["success"] = true;
           return $arr;
        }
        else {
            $arr["success"] = false;
            return $arr;
        }
    }

    public static function installUpdate($file,$rootpath = "../"){
        $zip = new \ZipArchive;
        $res = $zip->open($file);
        if ($res === TRUE) {
            $zip->extractTo($rootpath);
            $zip->close();
            return true;
        } else {
            return false;
        }
    }
    public static function getProviderData($providerurl){
        $vajson = file_get_contents($providerurl."/provider/");
        $prv_array = json_decode($vajson,true);
        return $prv_array;
    }
}
