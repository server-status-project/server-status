<?php

if (!file_exists("../config.php"))
{
  header("Location: ../");
}
else{
  require_once("../config.php");
  require_once("../classes/constellation.php");
  require_once("../classes/mailer.php");
  require_once("../classes/notification.php");
  require_once("../template.php");
  require_once("../libs/parsedown/Parsedown.php");
  require_once("../classes/queue.php");

  // Process the subscriber notification queue
  // If CRON_SERVER_IP is not set, call notification once incident has been saved
  if ( empty(CRON_SERVER_IP) )
  {
    if ( isset($_GET['sent']) && $_GET['sent'] == true )
    {
      Queue::process_queue();
    }
  }
  else if ( isset($_GET['task']) && $_GET['task'] == 'cron' )
  {
    // Else, base it on call to /admin?task=cron being called from IP defined by CRON_SERVER_IP
    if (! empty(CRON_SERVER_IP) && $_SERVER['REMOTE_ADDR'] == CRON_SERVER_IP )
    {
        Queue::process_queue();
        syslog(1, "CRON server processed");
    }
    else {
        syslog(1, "CRON called from unauthorised server");
    }
  }


  if(isset($_COOKIE['user'])&&!isset($_SESSION['user']))
  {
    User::restore_session();
  }

  if (!isset($_SESSION['user']))
  {
    if (isset($_GET['do']) && $_GET['do']=="lost-password")
    {
      require_once("lost-password.php");
    }else if (isset($_GET['do']) && $_GET['do']=="change-email"){
      $user_pwd = new User($_GET['id']);
      $user_pwd->change_email();
      require_once("login-form.php");
    }
    else{
      User::login();
      require_once("login-form.php");
    }
  }
  else
  {
    $user = new User($_SESSION['user']);
    if (!$user->is_active())
    {
      User::logout();
    }

    if (!isset($_GET['do'])){
      $do = "";
    }else{
      $do = $_GET['do'];
    }

    switch ($do) {
      case 'change-email':
        $user = new User($_GET['id']);
        $user->change_email();
    	case 'user':
    		require_once("user.php");
    		break;

    	case 'settings':
    		require_once("settings.php");
    		break;

    	case 'new-user':
    		require_once("new-user.php");
    		break;

      case 'logout':
        User::logout();
        break;

    	default:
    		require_once("dashboard.php");
    		break;
    }

    Template::render_footer(true);
  }
}
