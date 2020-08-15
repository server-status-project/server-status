<?php


class SSDB
{
    function execute($link,$sql){
        if ($result = mysqli_query($link, $sql)) {
            return true;
        } else {
            die("SQL Failure.Traceback:" . $sql . " Detailed info:" . mysqli_error($link));
        }
    }
    function getSetting($link,$setting){
        $sql = "SELECT value FROM settings WHERE setting=\"".$setting."\";";
        if($result = mysqli_query($link, $sql)){
            if(mysqli_num_rows($result) == 1){
                while($row = mysqli_fetch_array($result)){
                    return $row['value'];
                }
            }
            else{
                return "none";
            }
        }
    }
    function setSetting($link,$settingname,$settingvalue){
        $sql = "INSERT INTO settings (setting,value) VALUES ('".$settingname."','".$settingvalue."');";
        if ($result = mysqli_query($link, $sql)) {
            return true;
        } else {
            die("SQL Failure.Traceback:" . $sql . " Detailed info:" . mysqli_error($link));
        }
    }
    function deleteSetting($link,$settingname){
        $sql = "DELETE FROM settings WHERE setting=\"".$settingname."\";";
        if ($result = mysqli_query($link, $sql)) {
            return true;
        } else {
            die("SQL Failure.Traceback:" . $sql . " Detailed info:" . mysqli_error($link));
        }
    }
}
