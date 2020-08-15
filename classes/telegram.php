<?php
Class Telegram
{

    /**
     * Get telegram user data
     * 
     * Gets telegram user data from cookie and save it to array
     * 
     * @return void
     *
     * @author Telegram
     *
     *
     * @since 0.1
     */
    function getTelegramUserData() {
    	if (isset($_COOKIE['tg_user'])) {
            $auth_data_json = urldecode($_COOKIE['tg_user']);
            $auth_data = json_decode($auth_data_json, true);
            return $auth_data;
        }
        return false;
    }
    /** 
     * Check if data is from telegram
     * 
     * This checks if the data provides is from telegram. It includes a Fix for firefox
     * 
     * @param mixed $auth_data The Authentication Data
     * 
     * @return $auth_data
     * 
    */
    function checkTelegramAuthorization($auth_data) {
    	$check_hash = $auth_data['hash'];
    	unset($auth_data['hash']);
    	$data_check_arr = [];
    	foreach ($auth_data as $key => $value) {
    	 // $data_check_arr[] = $key . '=' . $value;
    	  $data_check_arr[] = $key . '=' . str_replace('https:/t', 'https://t', $value);
    	}
    	sort($data_check_arr);
    	$data_check_string = implode("\n", $data_check_arr);
    	$secret_key = hash('sha256', TG_BOT_API_TOKEN, true);
    	$hash = hash_hmac('sha256', $data_check_string, $secret_key);
    	if (strcmp($hash, $check_hash) !== 0) {
    	  throw new Exception('Data is NOT from Telegram');
    	}
    	if ((time() - $auth_data['auth_date']) > 86400) {
    	  throw new Exception('Data is outdated');
    	}
    	return $auth_data;
    }
    
    
    /**
     * Save telegram userdata
     * 
     * Save the telegram user data in a cookie
     *  @return void
     */  
    function saveTelegramUserData($auth_data) {
    	$auth_data_json = json_encode($auth_data);
    	setcookie('tg_user', $auth_data_json);
    }
  
    function get_telegram_subscriberid($user)
    {
        global $mysqli;
        $stmt = $mysqli->prepare("SELECT subscriberID FROM subscribers WHERE typeID=1 AND userID LIKE ? LIMIT 1");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();
        if ( $result->num_rows) {
            $row = $result->fetch_assoc();
            $subscriberID = $row['subscriberID'];          
            return $subscriberID;
        }
        return null;  // Return null on false          
    }
}