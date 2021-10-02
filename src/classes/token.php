<?php

/**
 * Class for creating and deleting tokens
 */
class Token
{


    /**
     * Generates a new token from user id and randomly generated salt.
     *
     * @param  integer   $id     user ID
     * @param  string    $data   associated with token that are important
     * @param  timestamp $expire expiration time
     * @return string token
     */
  public static function add($id, $data, $expire)
  {
    global $mysqli;
    $salt  = uniqid(mt_rand(), true);
    $token = hash('sha256', $id.$salt);
    $stmt  = $mysqli->prepare('INSERT INTO tokens VALUES(?, ?, ?, ?)');
    $stmt->bind_param('siis', $token, $id, $expire, $data);
    $stmt->execute();
    $stmt->get_result();
    return $token;
  }//end add()


    /**
     * Checks whether token exists in the database and has not expired.
     *
     * @param  string  $token
     * @param  integer $id    user ID
     * @param  string  $data
     * @return integer count of results in database
     */
  public static function validate($token, $id, $data)
  {
    global $mysqli;
    $time = time();
    $stmt = $mysqli->prepare('SELECT count(*) as count FROM tokens WHERE token = ? AND user = ? AND expire>=? AND data LIKE ?');
    $stmt->bind_param('siis', $token, $id, $time, $data);
    $stmt->execute();
    $query = $stmt->get_result();
    return $query->fetch_assoc()['count'];
  }//end validate()


    /**
     * Returns token data
     *
     * @param  string  $token
     * @param  integer $id    user ID
     * @return string data
     */
  public static function get_data($token, $id)
  {
    global $mysqli;
    $stmt = $mysqli->prepare('SELECT data as count FROM tokens WHERE token = ? AND user = ?');
    $stmt->bind_param('si', $token, $id);
    $stmt->execute();
    $query = $stmt->get_result();
    return $query->fetch_assoc()['data'];
  }//end get_data()


    /**
     * Deletes token.
     *
     * @param  string $token
     * @return void
     */
  public static function delete($token)
  {
    global $mysqli;
    $time = time();
    $stmt = $mysqli->prepare('DELETE FROM tokens WHERE token = ? OR expire<?');
    $stmt->bind_param('sd', $token, $time);
    $stmt->execute();
    $stmt->get_result();
  }//end delete()
}//end class
