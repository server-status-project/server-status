<?php
session_start();
require("../config.php");
require("../classes/constellation.php");
require("../template.php");

if(isset($_COOKIE['user'])&&!isset($_SESSION['user']))
{
  User::restore_session();
}

if (!isset($_SESSION['user']))
{
  if (isset($_GET['do']) && $_GET['do']=="lost-password")
  {
    require("lost-password.php");
  }else if (isset($_GET['do']) && $_GET['do']=="change-email"){
    $user_pwd = new User($_GET['id']);
    $user_pwd->change_email();
    require("login-form.php");
  }
  else{
    User::login();
    require("login-form.php");
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
  		require("user.php");
  		break;
  	
  	case 'settings':
  		require("settings.php");
  		break;

  	case 'new-user':
  		require("new-user.php");
  		break;

    case 'logout':
      User::logout();
      break;

  	default:
  		require("dashboard.php");
  		break;
  }

  Template::render_footer(true);
}