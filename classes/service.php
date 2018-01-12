<?php
/**
* Class for managing services
*/
class Service
{
  private $id;
  private $name;
  private $status;

  /**
   * Constructs service from its data.
   * @param int $id service ID
   * @param String $name service name
   * @param int $status current service status
   */
  function __construct($id, $name, $status=3)
  {
    //TODO: Maybe get data from ID?
    $this->id = $id;
    $this->name = $name;
    $this->status = $status;
  }

  /**
   * Returns status of this service
   * @return int status
   */
  public function get_status()
  {
    return $this->status;
  }

  /**
   * Returns id of this service
   * @return int id
   */
  public function get_id()
  {
    return $this->id;
  }

  /**
   * Returns name of this service
   * @return String name
   */
  public function get_name()
  {
    return $this->name;
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
    if (strlen($_POST['service'])>50)
    {
      $message = _("Service name is too long! Character limit is 50");
      return;
    }else if (strlen(trim($_POST['service']))==0){
      $message = _("Please enter name!");
      return;
    }

    if ($user->get_rank()<=1)
    {
      global $mysqli;
      $name = $_POST['service'];
      $stmt = $mysqli->prepare("INSERT INTO services VALUES(NULL,?)");
      $stmt->bind_param("s", $name);
      $stmt->execute();
      $query = $stmt->get_result();
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
    global $user;
    if ($user->get_rank()<=1)
    {
      global $mysqli;
      $id = $_GET['delete'];

      $stmt = $mysqli->prepare("SELECT service_id, status_id as status, (SELECT count(*) FROM services_status as s WHERE s.status_id=status) as count FROM services_status WHERE service_id = ? GROUP BY status_id");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $query = $stmt->get_result();

      while ($res = $query->fetch_assoc()) {
        if ($res['count']==1)
        {
          Incident::delete($res['status']);
        }
      }

      $stmt = $mysqli->prepare("DELETE FROM services WHERE id = ?");
      $stmt->bind_param("i", $id);
      $stmt->execute();
      $query = $stmt->get_result();

      $stmt = $mysqli->prepare("DELETE FROM services_status WHERE service_id = ?");
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
   * Renders current status for services from passed array of services.
   * @param Service[] $array array of services
   * @return void
   */
  public static function current_status($array){
    global $all, $some, $classes;
    $statuses = array(0,0,0,0);
    $worst = 5;

    foreach ($array as $service) {
      if ($service->status<$worst)
      {
        $worst = $service->get_status();
      }
      $statuses[$service->get_status()]++; 
    }

    echo '<div id="status-big" class="status '.$classes[$worst].'">';
      
    if ($statuses[$worst] == count($array))
    {
      echo $all[$worst];
    }else{
      echo $some[$worst];
    }
    echo '</div>';
  }

  /**
   * Renders this service.
   * @return void
   */
  public function render(){
    global $statuses;
    global $classes;
    ?>
      <div class="item clearfix">
        <div class="service"><?php echo $this->name; ?></div>
        <?php if ($this->status!=-1){?><div class="status <?php echo $classes[$this->status];?>"><?php echo $statuses[$this->status];?></div><?php }?>
      </div>
  <?php
  }
}          