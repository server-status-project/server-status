class Database{
  /**
  * Database Class
  * Created by Yigit Kerem Oktay
  */
  public static function getSetting($link,$setting){
   $sql = "SELECT SettingValue FROM tokentable WHERE SettingName=\"".$setting."\";";
       if($result = mysqli_query($link, $sql)){
        if(mysqli_num_rows($result) == 1){
         while($row = mysqli_fetch_array($result)){
                return $row['SettingValue'];
                mysqli_free_result($result);
              }
            }
          }
      else{
           die("ERROR: Could not able to execute $sql. " . mysqli_error($link));
      }
    mysqli_close($link);
  }
  public static function setSetting($link,$setting,$settingval){
   $sql = "INSERT INTO settings (SettingName, SettingValue) VALUES ('".$setting."','".$settingvalue."');";
       if($result = mysqli_query($link, $sql)){ return true; }
       else{
       die("ERROR: Could not able to execute $sql. " . mysqli_error($link));
       }
    mysqli_close($link);
  }
}
