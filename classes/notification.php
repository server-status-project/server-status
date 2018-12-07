<?php

/**
 * Class that encapsulates everything that can be done with notifications
 */

class Notification
{

    public $status_id = null;
    public $servicenames = "";
    public $serviceids = "";
    public $type = 0;
    public $time = 0;
    public $text = "";
    public $title = "";
    public $status = "";

    /**
     * Generate an array of servicenames and service IDs affected by a given incident
     * @param int $status_id The incident to query
     * @return boolean
     */
    public function populate_impacted_services($status_id)
    {
        global $mysqli;
        if (! empty($status_id)) {
            // Fetch services names for use in email
            $stmt = $mysqli->prepare("SELECT services.id, services.name FROM services INNER JOIN services_status on services.id = services_status.service_id WHERE services_status.status_id = ?");
            $stmt->bind_param("i", $status_id);
            $stmt->execute();
            $query = $stmt->get_result();
            $arrServicesNames = array();
            $arrServicesId = array();
            while ($result = $query->fetch_assoc()) {
                $arrServicesNames[] = $result['name'];
                $arrServicesId[] = (int) $result['id'];
            }
            $this->status_id = $status_id;
            $this->servicenames = implode(",", $arrServicesNames);
            $this->serviceids = implode(",", $arrServicesId);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Loop over the list of subscribers to notify depending on impacted service(s) and 
     * call the differnet notification handles.
     * @return void
     */    
    public function notify_subscribers()
    {
        global $mysqli;
        // Fetch list of unique subscribers for given service
        // Direct inclusion of variable withour using prepare justified by the fact that
        // this->serviceids are not user submitted
        $sql = "SELECT DISTINCT subscriberIDFK FROM services_subscriber WHERE serviceIDFK IN (" . $this->serviceids . ")";
        $query = $mysqli->query($sql);

        while ($subscriber = $query->fetch_assoc()) {
            // Fetch list of subscriber details for already found subscriber IDs
            $stmt = $mysqli->prepare("SELECT typeID, userID, firstname, token FROM subscribers WHERE subscriberID = ? AND active=1");
            $stmt->bind_param("i", $subscriber['subscriberIDFK']);
            $stmt->execute();
            $subscriberQuery = $stmt->get_result();

            while ($subscriberData = $subscriberQuery->fetch_assoc()) {
                $typeID = $subscriberData['typeID']; // Telegram = 1, email = 2
                $userID = $subscriberData['userID'];
                $firstname = $subscriberData['firstname'];
                $token = $subscriberData['token'];

                // Handle telegram
                if ($typeID == 1) {
                    $this->submit_telegram($userID, $firstname);
                }

                // Handle email
                if ($typeID == 2) {
                    $this->submit_email($userID, $token);
                }
            }
        }
    }

    /**
     * Sends Telegram notification message using their web api.
     * @param string $userID The Telegram userid to send to
     * @param string $firstname The users firstname
     * @return boolean
     */
    public function submit_telegram($userID, $firstname)
    {        
        // TODO Handle limitations (Max 30 different subscribers per second)
        // TODO Error handling
        $msg = _("Hi %s!\nThere is a status update for service(s): %s\nThe new status is: %s\nTitle: %s\n\n%s\n\n<a href='%s'>View online</a>");
        $msg = sprintf($msg, $firstname, $this->servicenames, $this->status, $this->title, $this->text, WEB_URL);
        
        $tg_message = urlencode($msg);
        $response = json_decode(file_get_contents("https://api.telegram.org/bot" . TG_BOT_API_TOKEN . "/sendMessage?chat_id=" . $userID . "&parse_mode=HTML&text=" . $tg_message), true);
        if (! array_key_exists("ok", $response) || $response['ok'] != 1 ) {
            return false;
        }
        return true;
    }

    /**
     * Sends email notifications to a subscriber.
     * Function depends on Parsedown and Mailer class being loaded.
     * @param String $userID The email address to send to
     * @param String $uthkey Users token for managing subscription
     * @return void
     */
    public function submit_email($userID, $token)
    {
        // TODO Error handling
        $Parsedown = new Parsedown();
        $mailer = new Mailer();
        
        $str_mail = file_get_contents("../libs/templates/email_status_update.html");
        $str_mail = str_replace("%name%", NAME, $str_mail);
        // $smtp_mail = str_replace("%email%", $userID, $smtp_mail);
        $str_mail = str_replace("%url%", WEB_URL, $str_mail);
        $str_mail = str_replace("%service%", $this->servicenames, $str_mail);
        $str_mail = str_replace("%status%", $this->status, $str_mail);
        $str_mail = str_replace("%time%", date("c", $this->time), $str_mail);
        $str_mail = str_replace("%comment%", $Parsedown->setBreaksEnabled(true)->text($this->text), $str_mail);
        $str_mail = str_replace("%token%", $token, $str_mail);
        
        $str_mail = str_replace("%service_status_update_from%", _("Service status update from"), $str_mail);
        $str_mail = str_replace("%services_impacted%", _("Service(s) Impacted"), $str_mail);
        $str_mail = str_replace("%status_label%", _("Status"), $str_mail);
        $str_mail = str_replace("%time_label%", _("Time"), $str_mail);
        $str_mail = str_replace("%manage_subscription%", _("Manage subscription"), $str_mail);
        $str_mail = str_replace("%unsubscribe%", _("Unsubscribe"), $str_mail);
        $str_mail = str_replace("%powered_by%", _("Powered by"), $str_mail);
        $subject = _('Status update from') . ' - ' . NAME . ' [ ' . $this->status . ' ]';
        $mailer->send_mail($userID, $subject, $str_mail);
    }
}