<?php
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