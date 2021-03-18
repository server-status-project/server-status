<?php


class SSDB
{
    function execute($conn, $sql)
    {
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            return $conn->error;
        }
    }
    function getSetting($conn, $setting)
    {
        $sql = "SELECT value FROM settings WHERE setting='" . $setting . "'";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            while ($row = $result->fetch_assoc()) {
                return $row["value"];
            }
        } else {
            return "null";
        }
    }
    function setSetting($conn, $settingname, $settingvalue)
    {
        $sql = "INSERT INTO settings (setting,value) VALUES ('" . $settingname . "','" . $settingvalue . "');";
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            return $conn->error;
        }
    }
    function deleteSetting($conn, $settingname)
    {
        $sql = "DELETE FROM settings WHERE setting=\"" . $settingname . "\";";
        if ($conn->query($sql) === TRUE) {
            return true;
        } else {
            return $conn->error;
        }
    }
    function updateSetting($conn, $settingname, $settingvalue)
    {
        $this->deleteSetting($conn, $settingname);
        $this->setSetting($conn, $settingname, $settingvalue);
        return true;
    }

    function getBooleanSetting($conn, $setting)
    {
        if (trim($this->getSetting($conn, $setting)) == "yes") {
            return true;
        }
        return false;
    }
}
