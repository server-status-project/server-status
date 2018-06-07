<?php
/**
* Class for creating and rendering an incident
*/
class Incident implements JsonSerializable
{
  private $id;
  private $date;
  private $end_date;
  private $timestamp;
  private $end_timestamp;
  private $text;
  private $type;
  private $title;
  private $username;

  /**
   * Constructs service from its data.
   * @param array $data incident data
   */
  function __construct($data)
  {
  	//TODO: Maybe get data from id?
    $this->id = $data['status_id'];
    $this->timestamp = $data['time'];
    $this->end_timestamp = $data['end_time'];
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

  /**
   * Deletes incident by ID.
   * @param int ID
   */
  public static function delete($id){
    global $mysqli, $message, $user;

    if ($user->get_rank() > 1)
    {
      $stmt = $mysqli->prepare("SELECT count(*) as count FROM status WHERE id= ? AND user_id = ?");
      $stmt->bind_param("ii", $id, $_SESSION['user']);
      $stmt->execute();
      $query = $stmt->get_result();
      if (!$query->fetch_assoc()['count'])
      {
        $message = _("You don't have permission to do that!");
        return;
      }
    }

    $stmt = $mysqli->prepare("DELETE FROM services_status WHERE status_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $query = $stmt->get_result();

    $stmt = $mysqli->prepare("DELETE FROM status WHERE id= ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $query = $stmt->get_result();
    header("Location: ".WEB_URL."/admin");
  }

  /**
   * Processes submitted form and adds incident unless problem is encountered, 
   * calling this is possible only for admin or higher rank. Also checks requirements
   * for char limits.
   * @return void
   */
  public static function add()
  {
    global $mysqli, $message;
    $user_id = $_SESSION['user'];
    $type = $_POST['type'];
    $title = $_POST['title'];
    $text = $_POST['text'];

    if (strlen($title)==0)
    {
      $message = _("Please enter title");
      return;
    }else if(strlen($title)>50){
      $message = _("Title too long! Character limit is 50");
      return;
    }

    if (strlen($title)==0)
    {
      $message = _("Please enter text");
      return;
    }

    if ($type == 2 && (!strlen(trim($_POST['time'])) || !strlen(trim($_POST['end_time']))))
    {
      $message = _("Please set start and end time! Use ISO 8601 format.");
      return;
    }

    if (empty($_POST['services'])){
      $message = _("Please select at least one service");
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
        $input_time = (isset($_POST['time_js'])?$_POST['time_js']: $_POST['time']);
        $input_end_time = (isset($_POST['end_time_js'])?$_POST['end_time_js']: $_POST['end_time']);
        $time = strtotime($input_time);  
        $end_time = strtotime($input_end_time);
        if (!$time)
        {
          $message = _("Start date format is not recognized. Please use ISO 8601 format.");
          return;
        }

        if (!$end_time)
        {
          $message = _("End date format is not recognized. Please use ISO 8601 format.");
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

        $query = $mysqli->query("SELECT * FROM services_subscriber WHERE serviceIDFK=" . $service);
				while($subscriber = $query->fetch_assoc()){
          $subscriberQuery = $mysqli->query("SELECT * FROM subscribers WHERE subscriberID=" . $subscriber['subscriberIDFK']);
          while($subscriberData = $subscriberQuery->fetch_assoc()){
            $telegramID = $subscriberData['telegramID'];
            $firstname = $subscriberData['firstname'];
            $lastname = $subscriberData['lastname'];
            
            $tg_message = urlencode('Hi ' . $firstname . chr(10) . 'There is a status update on a service that you have subscribed. <a href="' . WEB_URL . '">View online</a>');
            $response = json_decode(file_get_contents("https://api.telegram.org/bot" . TG_BOT_API_TOKEN . "/sendMessage?chat_id=" . $telegramID . "&parse_mode=HTML&text=" . $tg_message));
        }
       
      }
      header("Location: ".WEB_URL."/admin");
    }
  }
  }

  /**
   * Renders incident
   * @param Boolean $admin - decides whether admin controls should be rendered
   * @return void
   */
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
            echo '<a href="'.WEB_URL.'/admin/?delete='.$this->id.'" class="pull-right delete"><i class="fa fa-trash"></i></a>';
          }?>
          <time class="pull-right timeago" datetime="<?php echo $this->date; ?>"><?php echo $this->date; ?></time>
        </div>
        <div class="panel-body">
          <?php echo $this->text; ?>
        </div>
        <div class="panel-footer clearfix">
          <small><?php echo _("Posted by");?>: <?php echo $this->username; 
          if (isset($this->end_date)){?> 
            <span class="pull-right"><?php echo strtotime($this->end_date)>time()?_("Ending"):_("Ended");?>:&nbsp;<time class="pull-right timeago" datetime="<?php echo $this->end_date; ?>"><?php echo $this->end_date; ?></time></span>
            <?php } ?>
          </small>
        </div>
      </article>
      <?php
  }

  public function jsonSerialize() {
    return [
      "id" => $this->id,
      "date" => $this->timestamp,
      "end_date" => $this->end_timestamp,
      "text" => $this->text,
      "type" => $this->type,
      "title" => $this->title,
      "username" => $this->username
    ];
  }
}          