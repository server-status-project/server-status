<?php
/**
* Class for creating and deleting tokens
*/
class Token
{
  /**
   * Generates a new token from user id and randomly generated salt.
   * @param int $id user ID
   * @param String $data associated with token that are important
   * @param timestamp $expire expiration time
   * @return String token
   */
  public static function add($id, $data, $expire)
  {
    global $mysqli;
    $salt = uniqid(mt_rand(), true);
    $token = hash('sha256', $id.$salt);
    $stmt = $mysqli->prepare("INSERT INTO tokens VALUES(?, ?, ?, ?)");
    $stmt->bind_param("siis", $token, $id, $expire, $data);
    $stmt->execute();
    $query = $stmt->get_result();
    return $token;
  }

  /**
   * Checks whether token exists in the database and has not expired.
   * @param String $token
   * @param int $id user ID
   * @param String $data
   * @return int count of results in database
   */
  public static function validate_token($token, $id, $data)
  {
    global $mysqli;
    $time = time();
    $stmt = $mysqli->prepare("SELECT count(*) as count FROM tokens WHERE token = ? AND user = ? AND expire>=? AND data LIKE ?");
    $stmt->bind_param("siis", $token, $id, $time, $data);
    $stmt->execute();
    $query = $stmt->get_result();
    return $query->fetch_assoc()['count'];
  }

  /**
   * Deletes token.
   * @param String $token
   * @return void
   */
  public static function delete($token)
  {
    global $mysqli;
    $time = time();
    $stmt = $mysqli->prepare("DELETE FROM tokens WHERE token = ? OR expire<?");
    $stmt->bind_param("sd", $token,$time);
    $stmt->execute();
    $query = $stmt->get_result();
  }
}          