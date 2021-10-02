<?php

class SSDB
{


  function execute($conn, $sql)
  {
    if ($conn->query($sql) === true) {
      return true;
    } else {
      return $conn->error;
    }
  }//end execute()


  function getSetting($conn, $setting)
  {
    $sql    = "SELECT value FROM settings WHERE setting='".$setting."'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
      while ($row = $result->fetch_assoc()) {
        return $row['value'];
      }
    } else {
      return 'null';
    }
  }//end getSetting()


  function setSetting($conn, $settingname, $settingvalue)
  {
    $sql = "INSERT INTO settings (setting,value) VALUES ('".$settingname."','".$settingvalue."');";
    if ($conn->query($sql) === true) {
      return true;
    } else {
      return $conn->error;
    }
  }//end setSetting()


  function deleteSetting($conn, $settingname)
  {
    $sql = 'DELETE FROM settings WHERE setting="'.$settingname.'";';
    if ($conn->query($sql) === true) {
      return true;
    } else {
      return $conn->error;
    }
  }//end deleteSetting()


  function updateSetting($conn, $settingname, $settingvalue)
  {
    $this->deleteSetting($conn, $settingname);
    $this->setSetting($conn, $settingname, $settingvalue);
    return true;
  }//end updateSetting()


  function getBooleanSetting($conn, $setting)
  {
    if (trim($this->getSetting($conn, $setting)) == 'yes') {
      return true;
    }

    return false;
  }//end getBooleanSetting()
}//end class
