
<?php
//This should later be translatable, maybe find a better solution?
//This is here for better generation of POT files :)
$statuses = array(_("Major outage"), _("Minor outage"), _("Planned maintenance"), _("Operational") );
$classes = array("danger", "warning", "primary", "success" );
$icons = array("fa fa-times", "fa fa-exclamation", "fa fa-info", "fa fa-check" );
$some = array(_("Some systems are experiencing major outages"), _("Some systems are experiencing minor outages"), _("Some systems are under maintenance"));
$all = array(_("Our systems are experiencing major outages."), _("Our systems are experiencing minor outages"), _("Our systems are under maintenance"), _("All systems operational"));
$permissions = array(_("Super admin"), _("Admin"), _("Editor"));

require_once("telegram.php"); 

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
      ?>
      <!doctype html>
      <html lang="en">
      <head>
        <meta charset="utf-8">
        <title><?php echo $page_name." - ".NAME ?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Current service status for <?php echo NAME;?> can be found here as well as incident history.">
        <link rel="shortcut icon" href="<?php echo WEB_URL;?>/favicon.ico" type="image/png">
        <link rel="stylesheet" href="<?php echo WEB_URL;?>/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo WEB_URL;?>/css/main.css" media="screen">
        <link rel="stylesheet" href="<?php echo WEB_URL;?>/css/print.css" media="print">
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
            <a class="navbar-brand" href="<?php echo WEB_URL;?>"><img src="<?php echo WEB_URL;?>/img/logo_white.png" alt="logo" class="menu-logo" width="50" height="50"></a>
            </div>
            <div class="navbar-left hidden-xs">
              <ul class="nav navbar-nav">
                <li><a href="<?php echo WEB_URL;?>/"><h1><?php echo _((defined('TITLE')?TITLE:"Service Status"));?></h1></a></li>
              </ul>
            </div>
            <div class="navbar-collapse collapse navbar-right navbar-admin">
              <ul class="nav navbar-nav mr-auto">
              <?php
              $tg_user = getTelegramUserData();
              if($tg_user !== false){
                echo'<li><a href="?do=subscriptions">Subscriptions</a></li>';
                echo '<li><a href="https://status.jhuesser.ch/index.php?subscriber_logout=1">Logout</a></li>';
              } else {
                echo '<li><a href="#"><script async src="https://telegram.org/js/telegram-widget.js?4" data-telegram-login="jhuesserstatusbot" data-size="small" data-userpic="false" data-auth-url="https://status.jhuesser.ch/check.php" data-request-access="write"></script></a></li>';
              }?>
                </ul>
            </div><!--/.nav-collapse -->
          </div>
        </div>
        <div id="wrapper" class="center admin">

    
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
              <a class="navbar-brand" href="<?php echo WEB_URL;?>/admin"><img src="<?php echo WEB_URL;?>/img/logo_white.png" alt="logo" class="menu-logo" width="50" height="50"></a>
            </div>
            <div class="navbar-left hidden-xs">
              <ul class="nav navbar-nav">
                <li><a href="<?php echo WEB_URL;?>/"><h1><?php echo _((defined('TITLE')?TITLE:"Service Status"));?></h1></a></li>
              </ul>
            </div>
            <div class="navbar-collapse collapse navbar-right navbar-admin">
              <ul class="nav navbar-nav">
                <li><a href="<?php echo WEB_URL;?>/admin/"><?php echo _("Dashboard");?></a></li>
                <li><a href="<?php echo WEB_URL;?>/admin/?do=user"><?php printf(_("User (%s)"), $user->get_username());?></a></li>
                <li><a href="<?php echo WEB_URL;?>/admin/?do=settings"><?php echo _("Settings");?></a></li>
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
          <div class="col-md-4 text-left">Copyright © <?php echo date("Y");?> Vojtěch Sajdl</div>
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
          <div class="col-md-4 text-right"><a href="policy.php">Imprint & Privacy Policy</a><a href="https://github.com/Pryx/server-status/" target="_blank"><i class="fab fa-github" aria-hidden="true"></i></a></div>
        </div><!--/row -->
      </div><!--/container -->
    </div>
    <script src="<?php echo WEB_URL;?>/js/vendor/jquery-3.2.1.min.js"></script>
    <script src="<?php echo WEB_URL;?>/js/vendor/jquery.timeago.js"></script>
    <script src="<?php echo WEB_URL;?>/locale/<?php echo $_SESSION['locale'];?>/jquery.timeago.js"></script>
    <?php if ($admin){?>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="<?php echo WEB_URL;?>/js/admin.js"></script>
    <script src="<?php echo WEB_URL;?>/js/vendor/jquery.growl.js"></script>
    <?php }?>
    <script src="<?php echo WEB_URL;?>/js/vendor/bootstrap.min.js"></script>
    <script src="<?php echo WEB_URL;?>/js/main.js"></script>
  </body>
  </html>
<?php
  }
}