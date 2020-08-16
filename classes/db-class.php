<?php


class SSDB
{
    function execute($conn,$sql){
        if ($conn->query($sql) === TRUE) {
        return true;
        } else {
        return $conn->error;
    }

    $conn->close();
    }
    function getSetting($conn,$setting){
        $sql = "SELECT value FROM settings WHERE setting='".$setting."';
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                return $row["value"];
            }
        } else {
            return "null";
        }
        $conn->close();
    }
    function setSetting($conn,$settingname,$settingvalue){
        $sql = "INSERT INTO settings (setting,value) VALUES ('".$settingname."','".$settingvalue."');";
            if ($conn->query($sql) === TRUE) {
                return true;
            } else {
                return $conn->error;
            }

        $conn->close();
    }
    function deleteSetting($conn,$settingname){
        $sql = "DELETE FROM settings WHERE setting=\"".$settingname."\";";
        if ($conn->query($sql) === TRUE) {
                return true;
            } else {
                return $conn->error;
            }

        $conn->close();
    }
}
