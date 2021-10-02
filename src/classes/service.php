<?php

/**
 * Class for managing services
 */
class Service implements JsonSerializable
{

  private $id;

  private $name;

  private $description;

  private $group_name;

  private $status;


    /**
     * Constructs service from its data.
     *
     * @param integer $id          service ID
     * @param string  $name        service name
     * @param string  $descriotion service description for tooltip
     * @param integer $status      current service status
     */
  function __construct($id, $name, $description = null, $group_name = '', $status = 3)
  {
      // TODO: Maybe get data from ID?
    $this->id          = $id;
    $this->name        = $name;
    $this->description = $description;
    $this->group_name  = $group_name;
    $this->status      = $status;
  }//end __construct()


    /**
     * Returns status of this service
     *
     * @return integer status
     */
  public function get_status()
  {
    return $this->status;
  }//end get_status()


    /**
     * Returns id of this service
     *
     * @return integer id
     */
  public function get_id()
  {
    return $this->id;
  }//end get_id()


    /**
     * Returns name of this service
     *
     * @return string name
     */
  public function get_name()
  {
    return $this->name;
  }//end get_name()


    /**
     * Returns description of this service
     *
     * @return string description
     */
  public function get_description()
  {
    return $this->description;
  }//end get_description()


    /**
     * Processes submitted form and adds service unless problem is encountered,
     * calling this is possible only for admin or higher rank. Also checks requirements
     * for char limits.
     *
     * @return void
     */
  public static function add()
  {
    global $user, $message;
    if (strlen($_POST['service']) > 50) {
      $message = _('Service name is too long! Character limit is 50');
      return;
    } elseif (strlen(trim($_POST['service'])) == 0) {
      $message = _('Please enter name!');
      return;
    }

    if ($user->get_rank() <= 1) {
      global $mysqli;
      $name        = htmlspecialchars($_POST['service']);
      $description = htmlspecialchars($_POST['description']);
      $group_id    = $_POST['group_id'];
      $stmt        = $mysqli->prepare('INSERT INTO services ( name, description, group_id ) VALUES ( ?, ?, ? )');
      $stmt->bind_param('ssi', $name, $description, $group_id);
      $stmt->execute();
      $stmt->get_result();
      header('Location: '.WEB_URL.'/admin/?do=settings');
    } else {
      $message = _("You don't have the permission to do that!");
    }
  }//end add()


    /**
     * Processes submitted form and adds service unless problem is encountered,
     * calling this is possible only for admin or higher rank. Also checks requirements
     * for char limits.
     *
     * @return void
     */
  public static function edit()
  {
    global $user, $message;
    if (strlen($_POST['service']) > 50) {
      $message = _('Service name is too long! Character limit is 50');
      return;
    } elseif (strlen(trim($_POST['service'])) == 0) {
      $message = _('Please enter name!');
      return;
    }

    if ($user->get_rank() <= 1) {
      global $mysqli;
      $service_id  = $_POST['id'];
      $name        = htmlspecialchars($_POST['service']);
      $description = htmlspecialchars($_POST['description']);
      $group_id    = $_POST['group_id'];
      $stmt        = $mysqli->prepare('UPDATE services SET name=?, description=?, group_id=? WHERE id = ?');
      $stmt->bind_param('ssii', $name, $description, $group_id, $service_id);
      $stmt->execute();
      $stmt->get_result();
      header('Location: '.WEB_URL.'/admin/?do=settings');
    } else {
      $message = _("You don't have the permission to do that!");
    }
  }//end edit()


    /**
     * Deletes this service - first checks if user has permission to do that.
     *
     * @return void
     */
  public static function delete()
  {
    global $user, $message;
    if ($user->get_rank() <= 1) {
      global $mysqli;
      $id = $_GET['delete'];

      $stmt = $mysqli->prepare('SELECT status_id as status, (SELECT count(*) FROM services_status as s WHERE s.status_id=status) as count FROM services_status WHERE service_id = ? GROUP BY status');
      $stmt->bind_param('i', $id);
      $stmt->execute();
      $query = $stmt->get_result();

      while ($res = $query->fetch_assoc()) {
        if ($res['count'] == 1) {
          Incident::delete($res['status']);
        }
      }

      $stmt = $mysqli->prepare('DELETE FROM services WHERE id = ?');
      $stmt->bind_param('i', $id);
      $stmt->execute();
      $query = $stmt->get_result();

      $stmt = $mysqli->prepare('DELETE FROM services_status WHERE service_id = ?');
      $stmt->bind_param('i', $id);
      $stmt->execute();
      $query = $stmt->get_result();

      header('Location: '.WEB_URL.'/admin/?do=settings');
    } else {
      $message = _("You don't have the permission to do that!");
    }//end if
  }//end delete()


    /**
     * Renders current status for services from passed array of services.
     *
     * @param  Service[] $array array of services
     * @return void
     */
  public static function current_status($array)
  {
    global $all, $some, $classes;
    $statuses = [
          0,
          0,
          0,
          0,
      ];
    $worst    = 5;

    foreach ($array as $service) {
      if ($service->status < $worst) {
        $worst = $service->get_status();
      }

      $statuses[$service->get_status()]++;
    }

    $result = '<div id="status-big" class="status '.$classes[$worst].'">';

    if ($statuses[$worst] == count($array)) {
      $result .= $all[$worst];
    } else {
      $result .= $some[$worst];
    }

    $result .= '</div>';
  }//end current_status()

    /**
     * Evaluates the group status
     *
     * @param  Service[] $array array of services
     * @return void
     */
  public static function group_status($array)
  {
    $worst    = 5;

    foreach ($array as $service) {
      if ($service->status < $worst) {
        $worst = $service->get_status();
      }
    }

    return $worst;
  }//end current_status()

    /**
     * Renders this service.
     *
     * @param  $boolGroup set to true if the groups name is to be rendered
     * @return void
     */
  public function render()
  {
    global $classes, $statuses;

      // Render the service status
    echo '<li class="list-group-item sub-component"><strong>'.$this->name.'</strong>';
      // echo '<li class="list-group-item d-flex flex-columns justify-content-between><span>+</span><h3 class="py-2 my-0 flex-fill expanded">' . $this->name . '</h3>';
    if (!empty($this->description)) {
      echo '<a class="desc-tool-tip" data-toggle="tooltip" data-placement="top" title="'.$this->description.'"> <span><i class="bi bi-question-circle-fill"></i></span></a>';
    }

    if ($this->status != -1) {
      ?><div class="status float-end <?php echo $classes[$this->status]; ?>"><?php echo _($statuses[$this->status]); ?></div>
      <?php
    }

    echo '</li>';
  }//end render()


  public function jsonSerialize()
  {
    global $statuses;
    return [
          'id'            => $this->id,
          'name'          => $this->name,
          'description'   => $this->description,
          'status'        => $this->status,
          'status_string' => $statuses[$this->status],
      ];
  }//end jsonSerialize()
}//end class

