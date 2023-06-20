<?php
/**
* Class for managing services
*/
class ServiceGroup
{
  private $id;
  private $name;
  private $description;
  private $visibility_id;
  private $status;

  /**
   * Constructs servicegroup from its data.
   * @param int $id service ID
   * @param String $name service name
   * @param String $description tooltip text
   * @param int $visibility_id how to display group items
   */
  function __construct($id, $name, $description, $visibility_id, $status)

  {
    //TODO: Maybe get data from ID?
    $this->id = $id;
    $this->name = $name;
    $this->description = $description;
    $this->visibility_id = $visibility_id;
    $this->status = $status;
  }

  /**
   * Returns id of this servicegroup
   * @return int id
   */
  public function get_id()
  {
    return $this->id;
  }

  /**
   * Returns name of this servicegroup
   * @return String name
   */
  public function get_name()
  {
    return $this->name;
  }

  /**
   * Returns description of this servicegroup
   * @return String description
   */
  public function get_description()
  {
    return $this->description;
  }

  /**
   * Processes submitted form and adds service unless problem is encountered,
   * calling this is possible only for admin or higher rank. Also checks requirements
   * for char limits.
   * @return void
   */
  public static function add()
  {
    global $user, $message;
    if (strlen($_POST['group'])>50)
    {
      $message = _("Service group name is too long! Character limit is 50");
      return;
    }else if (strlen(trim($_POST['group']))==0){
      $message = _("Please enter name!");
      return;
    }

    if ($user->get_rank()<=1)
    {
      global $mysqli;
      $name = $_POST["group"];
      $description = $_POST["description"];
      $visibility_id = $_POST["visibility_id"];
      $stmt = $mysqli->prepare("INSERT INTO services_groups VALUES(NULL,?,?,?)");
      $stmt->bind_param("ssi", $name, $description, $visibility_id);
      $stmt->execute();
      $stmt->get_result();
      header("Location: ".WEB_URL."/admin/?do=settings");
    }else
    {
      $message = _("You don't have the permission to do that!");
    }
  }

  public static function edit()
  {
    global $user, $message;
    if (strlen($_POST['group'])>50)
    {
      $message = _("Service group name is too long! Character limit is 50");
      return;
    }else if (strlen(trim($_POST['group']))==0){
      $message = _("Please enter name!");
      return;
    }

    if ($user->get_rank()<=1)
    {
      global $mysqli;
      $name          = $_POST["group"];
      $description   = $_POST["description"];
      $visibility_id = $_POST["visibility_id"];
      $group_id      = $_POST["id"];
      $stmt = $mysqli->prepare("UPDATE services_groups SET name=?, description=?,visibility=? WHERE id LIKE ?");
      $stmt->bind_param("ssii", $name, $description, $visibility_id, $group_id);
      $stmt->execute();
      $stmt->get_result();
      header("Location: ".WEB_URL."/admin/?do=settings");
    }else
    {
      $message = _("You don't have the permission to do that!");
    }
  }
  /**
   * Deletes this service - first checks if user has permission to do that.
   * @return void
   */
  public static function delete()
  {
    global $user, $message;
    if ($user->get_rank()<=1)
    {
      global $mysqli;
      $id = $_GET['delete'];

      $stmt = $mysqli->prepare("DELETE FROM services_groups WHERE id = ?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $query = $stmt->get_result();

      $stmt = $mysqli->prepare("UPDATE services SET group_id = NULL WHERE group_id = ?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $query = $stmt->get_result();

      header("Location: ".WEB_URL."/admin/?do=settings");
    }
    else
    {
      $message = _("You don't have the permission to do that!");
    }
  }


  /**
   * Get list of services groups.
   * @return array $groups
   */
  public static function get_groups() {
    global $mysqli;
    $stmt = $mysqli->query("SELECT id, name FROM services_groups ORDER by name ASC");

    $groups = array();
    $groups[0] = '';
    while ($res = $stmt->fetch_assoc()) {
      $groups[$res['id']] = $res['name'];
    }
    return $groups;
  }
}
