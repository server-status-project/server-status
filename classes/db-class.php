<?php


class SSDB
{
    function execute($conn,$sql){
        if ($conn->query($sql) === TRUE) {
        $conn->close();
        return true;
        } else {
        return $conn->error;
        }
    }
    function getSetting($conn,$setting){
        $sql = "SELECT value FROM settings WHERE setting='".$setting."'";
        $result = $conn->query($sql);
        $ret = "none";
        if ($result->num_rows == 1) {
            while($row = $result->fetch_assoc()) {
                $ret = $row["value"];
            }
        } else {
            $ret = "null";
        }
    $conn->close();
    return $ret;
    }
    function setSetting($conn,$settingname,$settingvalue){
        $sql = "INSERT INTO settings (setting,value) VALUES ('".$settingname."','".$settingvalue."');";
            if ($conn->query($sql) === TRUE) {
                $conn->close();
                return true;
            } else {
                return $conn->error;
            }

    }
    function deleteSetting($conn,$settingname){
        $sql = "DELETE FROM settings WHERE setting=\"".$settingname."\";";
        if ($conn->query($sql) === TRUE) {
                $conn->close();
                return true;
            } else {
                return $conn->error;
            }

    }
}
