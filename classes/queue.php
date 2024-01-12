<?php

/**
 * Class for creating and managing the queue system
 */
class Queue
{
    public $task_id;
    public $type_id;
    public $status;
    public $template_data1;    // i.e. Subject for email
    public $template_data2;    // i.e. HTML email body
    public $create_time;
    public $completed_time;
    public $num_errors;
    public $user_id;
    public $all_type_id = array('notify_telegram' => 1,
                                'notify_email'    => 2);

    public $all_status = array('populating' => 1,
                               'ready'      => 2,
                               'processing' => 3,
                               'completed'  => 4,
                               'failed'     => 5);

    public function add_task() {
        global $mysqli;
        $stmt = $mysqli->prepare("INSERT INTO queue_task (type_id, status, template_data1, template_data2, created_time, user_id) VALUES (?,?,?,?,?,?)");
        if ( false===$stmt ) {
            //die('prepare() failed: ' . htmlspecialchars($mysqli->error));
            echo $mysqli->errno();

        }
        #if ( false === $stmt ) { syslog(1, "Error :". $mysqli->error); }
        $now = time();
        $res = $stmt->bind_param("iissii", $this->type_id, $this->status, $this->template_data1, $this->template_data2, $now, $this->user_id);
        if ( false === $res ) {
        echo "error";
        die();
        }
        $stmt->execute();
        $query = $stmt->get_result();
        print $query;
        $this->task_id = $mysqli->insert_id;
        return $this->task_id;
    }

    /**
     * Remove task from the queue
     * @return void
     */
    public static function delete_task($task_id){
        global $mysqli;
        $stmt = $mysqli->prepare("DELETE FROM queue_task WHERE id = ?");
        $stmt->bind_param("i", $task_id);
        $stmt->execute();
    }

    /**
     * Update status for given task
     * @param int $new_status The new current status of the task. Must be selected from the $all_status array.
     * @return void
     */
    public function set_task_status($new_status) {
        global $mysqli;
        $stmt = $mysqli->prepare("UPDATE queue_task SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_status, $this->task_id);
        $stmt->execute();
        $this->status = $new_status;
    }

    /**
     * Add notification queue data for given task
     * @param array $arr_data Array filled with subscriber_id
     * @return void
     */
    public function add_notification($arr_data) {
        global $mysqli;

        //Default status = 1, retres = 0, task_id = $this->task_id

        // Build query manually since mysqli doesn't cater well for multi insert..
        $count     = count($arr_data);  // Let's find number of elements
        $counter   = 0;
        $query     = '';
        $seperator = ',';
        $sub_query = '(%d, %d, %d ,%d)%s';

        foreach ($arr_data as $value) {
            $counter++;
            if ($counter == $count)  { $seperator = ''; }  // Make sure last character for SQL query is correct
            $query .= sprintf($sub_query, $this->task_id, 1, $value, 0, $seperator);
        }
        $sql = "INSERT INTO queue_notify (task_id, status, subscriber_id, retries) VALUES ". $query;

        $mysqli->query($sql);

        $this->set_task_status($this->all_status['ready']); // Make task available for release
    }

    public static function update_notification_retries($task_id, $subscriber_id) {
        global $mysqli;
        $stmt = $mysqli->prepare("UPDATE queue_notify SET retries = retries+1 WHERE task_id = ? AND subscriber_id = ?");
        $stmt->bind_param("ii", $task_id, $subscriber_id);
        $stmt->execute();
    }

    public static function delete_notification($task_id, $subscriber_id) {
        global $mysqli;
        $stmt = $mysqli->prepare("DELETE FROM queue_notify WHERE task_id = ? AND subscriber_id = ?");
        $stmt->bind_param("ii", $task_id, $subscriber_id);
        $stmt->execute();

    }

    // TODO: Fix max attempts for notifications
    public static function process_queue(){
      global $mysqli;
      $stmt = $mysqli->query("SELECT qn.id, qn.task_id, qn.status, qn.subscriber_id, qn.retries, sub.firstname, sub.userID, sub.token FROM queue_notify AS qn INNER JOIN subscribers AS sub ON qn.subscriber_id = sub.subscriberID WHERE qn.status NOT LIKE 2 AND sub.active=1");
      while ( $result = $stmt->fetch_assoc() ) {

          $i = 2;
          $stmt2 = $mysqli->prepare("SELECT * FROM queue_task WHERE id = ? AND status = ?");
          $stmt2->bind_param("ii", $result['task_id'], $i);
          $stmt2->execute();
          $tmp = $stmt2->get_result();
          $result2 = $tmp->fetch_assoc();
          $typeID = $result2['type_id'];

          // Handle telegram
          if ($typeID == 1) {
              $msg = str_replace("#s", $result['firstname'], $result2['template_data2']);
              if ( ! Notification::submit_queue_telegram($result['userID'], $result['firstname'], $msg) ) {
                Queue::update_notification_retries($result['task_id'], $result['subscriber_id']); // Sent
              } else {
                Queue::delete_notification($result['task_id'], $result['subscriber_id']); // Failed
              }
          }

          // Handle email
          if ($typeID == 2) {
              $msg = str_replace("%token%", $result['token'], $result2['template_data2']);
              if ( ! Notification::submit_queue_email($result['userID'], $result2['template_data1'], $msg) ) {
                Queue::update_notification_retries($result['task_id'], $result['subscriber_id']); // Sent

              } else {
                Queue::delete_notification($result['task_id'], $result['subscriber_id']); // Failed
              }
          }
      }

      // Check if queue log is empty and if so delete the queue_task
      $stmt = $mysqli->query("SELECT id, (SELECT COUNT(*) FROM queue_notify AS qn WHERE qn.task_id = queue_task.id) AS count FROM queue_task");
      while ( $result = $stmt->fetch_assoc() ) {
        if ( $result['count'] == 0 ) {
          Queue::delete_task($result['id']);
        }
      }

    }
}
