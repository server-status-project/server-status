<?php
$statuses = array(_("Major outage"), _("Minor outage"), _("Planned maintenance"), _("Operational") );
$classes = array("danger", "warning", "primary", "success" );
$icons = array("fa fa-times", "fa fa-exclamation", "fa fa-info", "fa fa-check" );
$some = array(_("Some systems are experiencing major outages"), _("Some systems are experiencing minor outages"), _("Some systems are under maintenance"));
$all = array(_("Our systems are experiencing major outages."), _("Our systems are experiencing minor outages"), _("Our systems are under maintenance"), _("All systems operational"));
$permissions = array(_("Super admin"), _("Admin"), _("Editor"));
$visibility = array(_("Collapsed"), _("Expanded"), _("Expand on events"));

/**
* Class that encapsulates methods to render header and footer
*/
class Template{
  /**
  * Renders header
  * @param String $page_name name of the page to be displayed as title
  * @param Boolean $admin decides whether to show admin menu
  */
  public static function render_header($page_name, $admin = false){
    if (!$admin)
    {
       // Create subscriber menu sections for later inclusion
       // Check if we are on admin menu, if so do not display
       $arr_url = explode("/", $_SERVER['PHP_SELF']);
       $str_url = strtolower($arr_url[count($arr_url)-2]);
       if ( 'admin' == $str_url ) {
           $strSubsMenu = '';
       } else {
        $strSubsMenu = '';
           if (defined('SUBSCRIBE_EMAIL') || defined('SUBSCRIBE_TELEGRAM') ) {
               // Subscriber menu is to be shown...
               $strSubsMenu = '<ul class="nav navbar-nav mr-auto">';
               // If subscriber is not logged on, display subscriber menus
               if ( (!isset($_SESSION['subscriber_valid'])) || false == $_SESSION['subscriber_valid'] ) {
                   $strSubsMenu .= '<li class="dropdown">
                                    <a class="dropdown-toggle" data-toggle="dropdown" role="button" href="#"><span class="glyphicon glyphicon-th"></span>&nbsp;'. _('Subscribe').'</a>
                                    <ul class="dropdown-menu ">';

                   if ( SUBSCRIBE_EMAIL ) {
                       $strSubsMenu .= '<li><a href="?do=email_subscription&amp;new=1"><span class="glyphicon glyphicon-envelope"></span>&nbsp;'._('Subscribe via email').'</a></li>';
                   }
                   if ( SUBSCRIBE_TELEGRAM ) {
                       $strSubsMenu .= '<li><a href="#"><script async src="https://telegram.org/js/telegram-widget.js?4" data-telegram-login="'.TG_BOT_USERNAME.'" data-size="small" data-userpic="false" data-auth-url="'.WEB_URL.'/telegram_check.php" data-request-access="write"></script></a></li>';
                   }
                   $strSubsMenu .=  '</ul>';
                }
           }
           // If subscriber is logged on, display unsub and logoff menu points
           if ( (isset($_SESSION['subscriber_valid'])) &&  $_SESSION['subscriber_valid'] ) {
               $strSubsMenu .= '<li><a href="?do=subscriptions">'._('Subscriptions').'</a></li>';
               $strSubsMenu .= '<li><a href="'.WEB_URL.'/index.php?subscriber_logout=1">'._('Logout').'</a></li>';
           }
           $strSubsMenu .=  '</ul>';
       }
      ?>
      <!doctype html>
      <html lang="en">
      <head>
       <?php
       if(defined('admin') && !admin){
        $headfile = fopen("head.txt", "r") or die("Unable to open head.txt!");
        $head_additionalcode = fread($versionfile ?? "Version2Beta8",filesize("head.txt"));
        fclose($headfile);
        echo $head_additionalcode;
        }
       ?>
        <meta charset="utf-8">
        <title><?php echo $page_name." - ".NAME ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Current service status for <?php echo NAME;?> can be found here as well as incident history.">
        <link rel="shortcut icon" href="<?php echo WEB_URL;?>/favicon.ico" type="image/png">
        <link rel="stylesheet" href="<?php echo WEB_URL;?>/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo WEB_URL;?>/css/main.css" media="screen">
        <link rel="stylesheet" href="<?php echo WEB_URL;?>/css/print.css" media="print">
        <link rel="stylesheet" href="<?php echo WEB_URL;?>/css/custom.css" media="screen">
        <link rel="apple-touch-icon" sizes="57x57" href="<?php echo WEB_URL;?>/favicon/apple-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="<?php echo WEB_URL;?>/favicon/apple-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="<?php echo WEB_URL;?>/favicon/apple-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="<?php echo WEB_URL;?>/favicon/apple-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="<?php echo WEB_URL;?>/favicon/apple-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="<?php echo WEB_URL;?>/favicon/apple-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="<?php echo WEB_URL;?>/favicon/apple-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="<?php echo WEB_URL;?>/favicon/apple-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo WEB_URL;?>/favicon/apple-icon-180x180.png">
        <link rel="icon" type="image/png" sizes="192x192"  href="<?php echo WEB_URL;?>/favicon/android-icon-192x192.png">
        <link rel="icon" type="image/png" sizes="32x32" href="<?php echo WEB_URL;?>/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="<?php echo WEB_URL;?>/favicon/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="16x16" href="<?php echo WEB_URL;?>/favicon/favicon-16x16.png">
        <link rel="manifest" href="<?php echo WEB_URL;?>/favicon/manifest.json">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
        <meta name="theme-color" content="#ffffff">
        <link href="https://use.fontawesome.com/releases/v5.0.4/css/all.css" rel="stylesheet">
        <style>
          .navbar, #footerwrap {
            display: none!important;
          }
          body {
            margin-top: 5px!important;
          }
          .text-center h2 {
            display:none!important;
          }
        </style>
      </head>
      <body>
        <div class="navbar navbar-default" role="navigation">
          <div class="container">
            <div class="navbar-header">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only"><?php echo _("Toggle navigation");?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="<?php echo WEB_URL;?>"><a class="navbar-brand" href="<?php echo WEB_URL;?>/admin"><img src="<?php if(strlen(CUSTOM_LOGO_URL)>1){ echo CUSTOM_LOGO_URL; } else { echo WEB_URL."/img/logo_white.png"; } ?>" alt="logo" class="menu-logo" style="height:50px;"></a>
            </div>
            <div class="navbar-left hidden-xs">
              <ul class="nav navbar-nav">
                <li><a href="<?php echo WEB_URL;?>/"><h1><?php echo _((defined('TITLE')?TITLE:"Service Status"));?></h1></a></li>
              </ul>
            </div>
            <div class="navbar-collapse collapse navbar-right navbar-admin">
              <?php echo $strSubsMenu; ?>
            </div><!--/.nav-collapse -->

          </div>
        </div>
        <div id="wrapper" class="center">
    <?php
      }else{
        global $user;
        ?>
      <!doctype html>
      <html lang="en">
      <head>
        <meta charset="utf-8">
        <title><?php echo $page_name." - ".NAME ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="shortcut icon" href="<?php echo WEB_URL;?>/favicon.ico" type="image/png">
        <link rel="stylesheet" href="<?php echo WEB_URL;?>/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo WEB_URL;?>/css/main.css">
        <link rel="apple-touch-icon" sizes="57x57" href="<?php echo WEB_URL;?>/favicon/apple-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="<?php echo WEB_URL;?>/favicon/apple-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="<?php echo WEB_URL;?>/favicon/apple-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="<?php echo WEB_URL;?>/favicon/apple-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="<?php echo WEB_URL;?>/favicon/apple-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="<?php echo WEB_URL;?>/favicon/apple-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="<?php echo WEB_URL;?>/favicon/apple-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="<?php echo WEB_URL;?>/favicon/apple-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="<?php echo WEB_URL;?>/favicon/apple-icon-180x180.png">
        <link rel="icon" type="image/png" sizes="192x192"  href="<?php echo WEB_URL;?>/favicon/android-icon-192x192.png">
        <link rel="icon" type="image/png" sizes="32x32" href="<?php echo WEB_URL;?>/favicon/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="<?php echo WEB_URL;?>/favicon/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="16x16" href="<?php echo WEB_URL;?>/favicon/favicon-16x16.png">
        <link rel="manifest" href="<?php echo WEB_URL;?>/favicon/manifest.json">
        <link href="https://use.fontawesome.com/releases/v5.0.4/css/all.css" rel="stylesheet">
        <link href="<?php echo WEB_URL;?>/css/jquery.growl.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
      </head>
      <body class="admin">
        <div class="navbar navbar-default" role="navigation">
          <div class="container">
            <div class="navbar-header">

              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only"><?php echo _("Toggle navigation");?></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="<?php echo WEB_URL;?>/admin"><img src="<?php if(strlen(CUSTOM_LOGO_URL)>1){ echo CUSTOM_LOGO_URL; } else { echo WEB_URL."/img/logo_white.png"; } ?>" alt="logo" class="menu-logo" width="50" height="50"></a>
            </div>
            <div class="navbar-collapse collapse navbar-right navbar-admin">
              <ul class="nav navbar-nav">
                <li><a href="<?php echo WEB_URL;?>/admin/"><?php echo _("Dashboard");?></a></li>
                <li><a href="<?php echo WEB_URL;?>/admin/?do=user"><?php printf(_("User (%s)"), $user->get_username());?></a></li>
                <li><a href="<?php echo WEB_URL;?>/admin/?do=settings"><?php echo _("Services & Users");?></a></li>
                <li><a href="<?php echo WEB_URL;?>/admin/?do=options"><?php echo _("Options");?></a></li>
                <li><a href="<?php echo WEB_URL;?>/admin/?do=logout"><?php echo _("Logout");?></a></li>
              </ul>
            </div><!--/.nav-collapse -->
          </div>
        </div>
        <div id="wrapper" class="center admin">
      <?php
    }
  }
  /**
  * Renders a toggle switch
  * Created by Yigit Kerem Oktay
  * @param String $toggletext will decide what the description text next to the toggle will be
  * @param String $input_name will decide what the HTML Name attribute of the toggle will be
  * @param Boolean $checked will decide if the toggle will initially be on or off
  */
  public static function render_toggle($toggletext,$input_name,$checked){
    ?>
          <div>
          <h3><?php echo $toggletext; ?></h3>
          <label class="switch">
              <input type="checkbox" name="<?php echo $input_name; ?>" <?php if($checked){ echo "checked"; } ?> >
              <span class="slider round"></span>
          </label>
          </div>
  <?php
  }
  /**
  * Renders footer
  * @param Boolean $admin decides whether to load admin scripts
  */
  public static function render_footer($admin = false)
  {
    global $negotiator;
    $lang_names = $negotiator->get_accepted_langs();
    ?>
    </div>
    <div id="footerwrap">
      <div class="container">
        <div class="row centered">
          <div class="col-md-4 text-left"><a href="https://github.com/server-status-project/server-status/graphs/contributors" target="_blank">Copyright © <?php echo date("Y");?> Server Status Project Contributors </a><?php if(strlen(COPYRIGHT_TEXT)>1){ echo " and ".COPYRIGHT_TEXT; } ?></div>
          <div class="col-md-4 text-center">
            <div class="btn-group dropup">
              <button type="button" class="btn btn-primary"><?php echo '<img src="'.WEB_URL.'/locale/'.$_SESSION['locale'].'/flag.png" alt="'.$lang_names[$_SESSION['locale']].'">'.$lang_names[$_SESSION['locale']];?></button>
              <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="caret"></span>
                <span class="sr-only"><?php echo _("Toggle Dropdown");?></span>
              </button>
              <div class="dropdown-menu">
                <?php
                foreach ($lang_names as $key => $value) {
                  echo '<a href="?lang='.$key.'"><img src="'.WEB_URL.'/locale/'.$key.'/flag.png" alt="'.$value.'">'.$value.'</a>';
                }
                ?>
                <hr role="separator" class="divider">
                <a href="https://poeditor.com/join/project/37SpmJtyOm"><?php echo _("Help with translation!");?></a>
              </div>
            </div>
          </div>
          <div class="col-md-4 text-right"><a href="<?php echo POLICY_URL; ?>"><?php echo _("Imprint & Privacy Policy");?></a><!-- <a href="https://github.com/Pryx/server-status/" target="_blank"><i class="fab fa-github" aria-hidden="true"></i></a> --></div>
        </div><!--/row -->
      </div><!--/container -->
    </div>
    <script src="<?php echo WEB_URL;?>/js/vendor/jquery-3.5.1.min.js"></script>
    <script src="<?php echo WEB_URL;?>/js/vendor/jquery.timeago.js"></script>
    <script src="<?php echo WEB_URL;?>/locale/<?php echo $_SESSION['locale'];?>/jquery.timeago.js"></script>
    <?php if ($admin){?>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="<?php echo WEB_URL;?>/js/admin.js"></script>
    <script src="<?php echo WEB_URL;?>/js/vendor/jquery.growl.js"></script>
    <?php }?>
    <script src="<?php echo WEB_URL;?>/js/vendor/bootstrap.min.js"></script>
    <script src="<?php echo WEB_URL;?>/js/main.js"></script>
    <script src="<?php echo WEB_URL;?>/js/custom.js"></script>
    <?php if ( defined('GOOGLE_RECAPTCHA') ) { ?><script src='https://www.google.com/recaptcha/api.js'></script><?php }?>
  </body>
  </html>
<?php
  }
}
