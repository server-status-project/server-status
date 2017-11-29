<?php
/**
* Class for creating and rendering an incident
*/
class Incident
{
  private $id;
  private $date;
  private $end_date;
  private $text;
  private $type;
  private $title;
  private $username;

  function __construct($data)
  {
    $this->id = $data['status_id'];
    $this->date = new DateTime("@".$data['time']);
    $this->date = $this->date->format('Y-m-d H:i:sP');
    if ($data['end_time']>0){
      $this->end_date = new DateTime("@".$data['end_time']);
      $this->end_date = $this->end_date->format('Y-m-d H:i:sP');
    }
    $this->type = $data['type'];
    $this->title = $data['title'];
    $this->text = $data['text'];
    $this->username = $data['username'];
  }

  public static function delete($id){
    global $mysqli, $message;

    $stmt = $mysqli->prepare("DELETE FROM services_status WHERE status_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $query = $stmt->get_result();

    $stmt = $mysqli->prepare("DELETE FROM status WHERE id= ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $query = $stmt->get_result();
    header("Location: /admin");
  }

  public static function add()
  {
    global $mysqli, $message;
    $user_id = $_SESSION['user'];
    $type = $_POST['type'];
    $title = $_POST['title'];
    $text = $_POST['text'];

    if (strlen($title)==0)
    {
      $message = "Please enter title";
      return;
    }else if(strlen($title)>50){
      $message = "Title too long! Character limit is 50";
      return;
    }

    if (strlen($title)==0)
    {
      $message = "Please enter text";
      return;
    }

    if ($type == 2 && (!strlen(trim($_POST['time'])) || !strlen(trim($_POST['end_time']))))
    {
      $message = "Please set start and end time! Use ISO 8601 format.";
      return;
    }

    if (empty($_POST['services'])){
      $message = "Please select at least one service";
    }
    else
    {
      if (!is_array($_POST['services']))
      {
        $services = array($_POST['services']);
      }
      else
      {
        $services = $_POST['services'];
      }

      if (!empty($_POST['time'])){
        $time = strtotime($_POST['time']);  
        $end_time = strtotime($_POST['end_time']);
        if (!$time)
        {
          $message = "Start date format is not recognized. Please use ISO 8601 format.";
          return;
        }

        if (!$end_time)
        {
          $message = "End date format is not recognized. Please use ISO 8601 format.";
          return;
        }
      }else{
        $time = time();
        $end_time = '';
      }
      
      $stmt = $mysqli->prepare("INSERT INTO status VALUES (NULL,?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("issiii", $type, $title, $text, $time ,$end_time ,$user_id);
      $stmt->execute();
      $query = $stmt->get_result();
      $status_id = $mysqli->insert_id;

      foreach ($services as $service) {
        $stmt = $mysqli->prepare("INSERT INTO services_status VALUES (NULL,?, ?)"); 
        $stmt->bind_param("ii", $service, $status_id);
        $stmt->execute();
        $query = $stmt->get_result();
      }
      header("Location: /admin");
    }
  }

  public function render($admin=0){
    global $icons;
    global $classes, $user;
    $admin = $admin && (($user->get_rank()<=1) || ($user->get_username() == $this->username));
    ?>
     <article class="panel panel-<?php echo $classes[$this->type];?>">
        <div class="panel-heading icon">
          <i class="<?php echo $icons[$this->type];?>"></i>
        </div>
        <div class="panel-heading clearfix">
          <h2 class="panel-title"><?php echo $this->title; ?></h2>
          <?php if ($admin){
            echo '<a href="?delete='.$this->id.'" class="pull-right delete"><i class="fa fa-trash"></i></a>';
          }?>
          <time class="pull-right timeago" datetime="<?php echo $this->date; ?>"><?php echo $this->date; ?></time>
        </div>
        <div class="panel-body">
          <?php echo $this->text; ?>
        </div>
        <div class="panel-footer">
          <small>Posted by: <?php echo $this->username; 
          if (isset($this->end_date)){?> 
            <span class="pull-right"><?php echo strtotime($this->end_date)>time()?"Ending:":"Ended:";?>&nbsp;<time class="pull-right timeago" datetime="<?php echo $this->end_date; ?>"><?php echo $this->end_date; ?></time></span>
            <?}?>
          </small>
        </div>
      </article>
      <?php
  }
}          