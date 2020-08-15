<?php

/**
 * Subscriptions class
 *
 */
Class Subscriptions
{
    public function add($userID, $service)
    {
        global $mysqli;
       
        $stmt = $mysqli->prepare("INSERT INTO services_subscriber (subscriberIDFK, serviceIDFK) VALUES (?, ?)");
        $stmt->bind_param("ii", $userID, $service);
        $stmt->execute();
        //$query = $stmt->get_result();
        return true;
    }
    
    public function remove($userID, $service)
    {
        global $mysqli;
        
        $stmt = $mysqli->prepare("DELETE FROM services_subscriber WHERE subscriberIDFK = ? AND serviceIDFK = ?");
        $stmt->bind_param("ii", $userID, $service);
        $stmt->execute();
        //$query = $stmt->get_result();
        return true;
    }
    
    function render_subscribed_services($typeID, $subscriberID, $userID, $token)
    {
        global $mysqli;
        $stmt = $mysqli->prepare("SELECT services.id, services.name, subscribers.subscriberID, subscribers.userID, subscribers.token
                                  FROM services
                                  LEFT JOIN services_subscriber ON services_subscriber.serviceIDFK = services.id
                                  LEFT JOIN subscribers ON services_subscriber.subscriberIDFK = subscribers.subscriberID
                                  WHERE subscribers.typeID = ? AND subscribers.subscriberID = ?");
        $stmt->bind_param("ii", $typeID, $subscriberID);
        $stmt->execute();
        $query = $stmt->get_result();

        $strNotifyType = _('E-mail Notification subscription');
        if ( $typeID == 1 ) { $strNotifyType = _('Telegram Notification subscription'); }

        ?>
        <div class="row">
          <div class="col-xs-12 col-lg-offset-2 col-lg-8">
            <div class="text-center">
              <h3><?php echo $strNotifyType; ?></h3>
              <p><?php echo _("Manage notification subscription for"); echo "&nbsp". $userID; ?></p>              
              <a onclick="if (confirm('<?php echo _("Are you sure you want to cancel you subscription?");?>')){return true;}else{event.stopPropagation(); event.preventDefault();};" class="confirmation" href="index.php?do=unsubscribe&amp;type=<?php echo $typeID;?>&amp;token=<?php echo $token;?>"><button class="btn btn-danger"><?php echo _("Cancel Subscription");?></button></a>          
            </div>
          </div>
        </div>
        <?php
        
        echo '<h1>' . _("Your subscriptions") . "</h1>";
        echo '<div class="list-group">';
        $subs = array();    // Will be used to hold IDs of services already selected
 
        if ($query->num_rows){
            while($result = $query->fetch_assoc())
            {
                echo '<a href="'.WEB_URL.'/subscriptions.php?remove=' . $result['id'] .'" class="list-group-item"><span class="glyphicon glyphicon-remove  text-danger"></span>&nbsp;' . $result['name'] . '</a>';
                $subs[] = $result['id'];
            }
            
        } else {
            echo '<div class="container"><summary>'._("You do not currently subscribe to any services. Please add services from the list below.").'</summary></div>';
        }
        echo "</div>";
        
        echo '<h1>' . _("Add new subscription") . '</h1>';

        // Prepare to query for unselect services. If none are selected, query for all
        $subsExp = null;
        if (count($subs) >  0 ) {
            $subsExp = 'NOT IN ('. implode(",", $subs) .')';
        }

        $query = $mysqli->query("SELECT services.id, services.name from services WHERE services.id $subsExp");
        echo '<div class="list-group">';
        if ($query->num_rows){           
            while($result = $query->fetch_assoc()){
                echo '<a href="'.WEB_URL.'/subscriptions.php?add=' . $result['id'] . '" class="list-group-item list-group-item-action"><span class="glyphicon glyphicon-plus  text-success"></span>&nbsp;' . $result['name'] . '</a>';
            }
        } else {
            echo '<div class="container"><summary>'._("No further services available for subscriptions.").'</summary></div>';
        }
        echo '</div>';
    }
    
}