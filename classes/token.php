<?php
/**
* Class for creating and deleting tokens
*/
class Token
{
  public static function new($id, $data, $expire)
  {
    global $mysqli;
    $salt = uniqid(mt_rand(), true);
    $token = hash('sha256', $seed.$salt);
    $stmt = $mysqli->prepare("INSERT INTO tokens VALUES(?, ?, ?, ?)");
    $stmt->bind_param("siis", $token, $id, $expire, $data);
    $stmt->execute();
    $query = $stmt->get_result();
    return $token;
  }

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