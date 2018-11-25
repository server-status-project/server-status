<?php
require_once("template.php");
require_once("config.php");
require_once("classes/constellation.php");
require_once("classes/subscriber.php");
require_once("classes/subscriptions.php");
require_once("classes/mailer.php");
//require_once("libs/php_idn/idna.php");

$mailer       = new Mailer();
$subscriber   = new Subscriber();
$subscription =  new Subscriptions();


$boolRegistered = false;

if ( isset($_GET['new']) ) {

    // Form validation for subscribers signing up
    $message = "";
    Template :: render_header(_("Email Subscription"));
    
    if (isset($_POST['emailaddress'])) {
        
        if (0 == strlen(trim($_POST['emailaddress']))){
            $messages[] = _("Email address");
        }
        
        // Perform DNS domain validation on
        if ( ! $mailer->verify_domain($_POST['emailaddress']) ) {
            $messages[] = _("Domain does not apper to be a valid email domain. (Check MX record)");
        }
        
        if (GOOGLE_RECAPTCHA) {
            // Validate recaptcha
            $response = $_POST["g-recaptcha-response"];
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = array(
                'secret' => GOOGLE_RECAPTCHA_SECRET,
                'response' => $_POST["g-recaptcha-response"]
            );
            $options = array(
                'http' => array (
                    'header' => 'Content-Type: application/x-www-form-urlencoded\r\n',
                    'method' => 'POST',
                    'content' => http_build_query($data)
                )
            );
            $context  = stream_context_create($options);
            $verify   = file_get_contents($url, false, $context);
            $captcha_success = json_decode($verify);

            if ( $captcha_success->success==false ) {
                $messages[] = _("reChaptcha validation failed");
            }
        }
        if ( isset($messages) ) {
            $message = _("Please check<br>");
            $message .= implode("<br> ", $messages);
        }

    }

    if(isset($_POST['emailaddress']) && empty($message))
    {

        // Check if email is already registered        
        $boolUserExist = false;
        $subscriber->userID = $_POST['emailaddress'];
        $subscriber->typeID = 2; // Email
        $boolUserExist = $subscriber->check_userid_exist();

        $url = WEB_URL."/index.php?do=manage&token=".$subscriber->token;

        if ( ! $boolUserExist ) {
            // Create a new subscriber as it does not exist
            $subscriber->add($subscriber->typeID, $_POST['emailaddress']);
            $url = WEB_URL."/index.php?do=manage&token=".$subscriber->token;    // Needed again after adding subscriber since token did not exist before add
            $msg = sprintf(_("Thank you for registering to receive status updates via email.</br></br> Click on the following link to confirm and manage your subcription: <a href=\"%s\">%s</a>. New subscriptions must be confirmed within 2 hours"), $url, NAME .' - ' . _("Validate subscription"));            
  
        } else {
            if ( ! $subscriber->active ) {
                // Subscriber is registered, but has not been activated yet...
                $msg = sprintf(_("Thank you for registering to receive status updates via email.</br></br> Click on the following link to confirm and manage your subcription: <a href=\"%s\">%s</a>. New subscriptions must be confirmed within 2 hours"), $url, NAME .' - ' . _("Validate subscription"));
                $subscriber->activate($subscriber->id);
                
            } else {
                // subscriber is registered and active
                $msg = sprintf(_("Click on the following link to update your existing subscription:  <a href=\"%s\">%s</a>"), $url, NAME .' - ' . _("Manage subscription"));                
                $subscriber->update($subscriber->id);
            }
        }
        // Show success message
        $header  = _("Thank you for subscribing");
        $message = _("You will receive an email shortly with an activation link. Please click on the link to activate and/or manage your subscription.");
        $constellation->render_success($header, $message, true, WEB_URL, _('Go back'));

        // Send email about new registration   
        $subject = _('Email subscription registered').' - '.NAME;
        $mailer->send_mail($_POST['emailaddress'], $subject, $msg);
        
        $boolRegistered = true;
    }

    // Add a new email subscriber - display form
    if ( isset($_GET['new']) && (! $boolRegistered) ) {

        if (!empty($message)) {
            echo '<p class="alert alert-danger">'.$message.'</p>';
        }
        $strPostedEmail = (isset($_POST['emailaddress']))  ?  $_POST['emailaddress'] : "";
    ?>
    
    
    <form method="post" action="index.php?do=email_subscription&new=1" class="clearfix" enctype="multipart/form-data" >
        <h3><?php echo _('Subscribe to get email notifications on status updates');?></h3>
        <div class="form-group clearfix">
        <label for="labelEmailAddress"><?php echo _('Email address');?></label>
        <input type="email" class="form-control" name="emailaddress" id="emailaddress" aria-describedby="emailHelp" placeholder="<?php echo _('Enter email address');?>" value="<?php echo $strPostedEmail;?>" required>
        </div>
        <?php if (GOOGLE_RECAPTCHA) {?>
        <div class="col-md-12">
            <div class="form-group">
            <div class="captcha_wrapper">
                    <div class="g-recaptcha" data-sitekey="<?php echo GOOGLE_RECAPTCHA_SITEKEY;?>"></div>
                </div>
            </div>
        </div>
        <?php } ?>
        <summary>
        <?php
            $msg = sprintf(_('By subscribing to recieve notifications you are agreeing to our <a href="%s">Privacy Policy</a>'), POLICY_URL);
            echo $msg;
        ?>
        </summary>
      <div class="form-group form-check">
      </div>
      <a href="<?php echo WEB_URL;?>" id="cancel" name="cancel" class="btn btn-default"><?php echo _('Close');?></a>
      <button type="submit" class="btn btn-primary"><?php echo _('Subscribe');?></button>
    </form>
    <?php
    }


/* Handle management and activation of email subscriptions */
} else if (isset($_GET['do']) && $_GET['do'] == 'manage') {

    // check if userid/token combo is valid, active or expired
    $subscriber->typeID = 2; //EMAIL
    if ( $subscriber->is_active_subscriber($_GET['token']) ) {
        // forward user to subscriber list....       
        $subscriber->set_logged_in();
        header('Location: subscriptions.php');
        exit;
    } else {
        Template :: render_header(_("Email Subscription"));

        $header  = _("We cannot find a valid subscriber account matching those details");
        $message = _("If you have recently subscribed, please make sure you activate the account within two hours of doing so. You are welcome to try and re-subscribe.");
        $constellation->render_warning($header, $message, true, WEB_URL, _('Go back'));
    }
        
    
} else if (isset($_GET['do']) && $_GET['do'] == 'unsubscribe') {
    // Handle unsubscriptions
    // TODO This function is universal and should probably live elsewhere??
    if (isset($_GET['token'])) {
        $subscriber->typeID = (int) $_GET['type']; 
                
        if ( $subscriber->get_subscriber_by_token($_GET['token'])) {
            $subscriber->delete($subscriber->id);
            $subscriber->set_logged_off();
            Template :: render_header(_("Email Subscription"));
            
            $header  = _("You have been unsubscribed from our system");
            $message = _("We are sorry to see you go. If you want to subscribe again at a later date please feel free to re-subscribe.");
			$constellation->render_success($header, $message, true, WEB_URL, _('Go back'));          
                       
        } else {
            // TODO Log token for troubleshooting ?
            // Cannot find subscriber - show alert
            Template :: render_header(_("Email Subscription"));
            $header = _("We are unable to find any valid subscriber detail matching your submitted data!");
            $message = _("If you believe this to be an error, please contact the system admininistrator.");            
            $constellation->render_warning($header, $message, true, WEB_URL, _('Go back'));
            
            //            
        }
    } else {
        // TODO Log $_GET[] for troubleshooting ?
        $header = _("We are unable to find any valid subscriber detail matching your submitted data!");
        $message = _("If you believe this to be an error, please contact the system admininistrator.");        
        $constellation->render_warning($header, $message, true, WEB_URL, _('Go back'));        
    }    
}
Template :: render_footer();
