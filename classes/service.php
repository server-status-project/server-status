<?php
/**
* Class for creating and rendering an incident
*/
class Service
{
  private $id;
  private $name;
  private $status;

  function __construct($id, $name, $status=3)
  {
    $this->id = $id;
    $this->name = $name;
    $this->status = $status;
  }

  public function get_status()
  {
    return $this->status;
  }

  public function get_id()
  {
    return $this->id;
  }

  public function get_name()
  {
    return $this->name;
  }

  public static function add()
  {
    global $user, $message;
    if (strlen($_POST['service'])>50)
    {
      $message = "Service name is too long! Character limit is 50";
      return;
    }else if (strlen(trim($_POST['service']))==0){
      $message = "Please enter name!";
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
      header("Location: /admin/?do=settings");
    }else
    {
      $message = "Insufficient permissions";
    }
  }

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

      header("Location: /admin/?do=settings");
    }
    else
    {
      $message = "Insufficient permissions";
    }
  }

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