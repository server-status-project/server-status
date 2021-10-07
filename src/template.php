<?php
$statuses = array(_("Major outage"), _("Minor outage"), _("Planned maintenance"), _("Operational"));
$classes = array("danger", "warning", "primary", "success");
$icons = array("fa fa-times", "fa fa-exclamation", "fa fa-info", "fa fa-check");
$some = array(_("Some systems are experiencing major outages"), _("Some systems are experiencing minor outages"), _("Some systems are under maintenance"));
$all = array(_("Our systems are experiencing major outages."), _("Our systems are experiencing minor outages"), _("Our systems are under maintenance"), _("All systems operational"));
$permissions = array(_("Super admin"), _("Admin"), _("Editor"));
$visibility = array(_("Collapsed"), _("Expanded"), _("Expand on events"));

/**
 * Class that encapsulates methods to render header and footer
 */
class Template {
  /**
   * Renders header
   * @param String $page_name name of the page to be displayed as title
   * @param Boolean $admin decides whether to show admin menu
   */
  public static function render_header($page_name, $page_id, $admin = false) {
    $strSubsMenu = '';
    if (!$admin) {
      // Create subscriber menu sections for later inclusion
      // Check if we are on admin menu, if so do not display
      $arr_url = explode("/", $_SERVER['PHP_SELF']);
      $str_url = strtolower($arr_url[count($arr_url) - 2]);
      if ('admin' != $str_url) {
        if ((defined('SUBSCRIBE_EMAIL') && SUBSCRIBE_EMAIL) || (defined('SUBSCRIBE_TELEGRAM') && SUBSCRIBE_TELEGRAM)) {
          // Subscriber menu is to be shown...
        $strSubsMenu = '<ul class="nav navbar-nav mr-auto">';
          // If subscriber is not logged on, display subscriber menus
        if ((!isset($_SESSION['subscriber_valid'])) || false == $_SESSION['subscriber_valid']) {
          $strSubsMenu .= '<li class="dropdown">
                                  <a class="dropdown-toggle" data-bs-toggle="dropdown" role="button" href="#"><span class="bi bi--grid-3x3-gap-fill"></span>&nbsp;' . _('Subscribe') . '</a>
                                  <ul class="dropdown-menu ">';

          if (SUBSCRIBE_EMAIL) {
            $strSubsMenu .= '<li><a class="dropdown-item" href="?do=email_subscription&amp;new=1"><span class="bi bi-envelope"></span>&nbsp;' . _('Subscribe via email') . '</a></li>';
          }
          if (SUBSCRIBE_TELEGRAM) {
            $strSubsMenu .= '<li><a class="dropdown-item" href="#"><script async src="https://telegram.org/js/telegram-widget.js?4" data-telegram-login="' . TG_BOT_USERNAME . '" data-size="small" data-userpic="false" data-auth-url="' . WEB_URL . '/telegram_check.php" data-request-access="write"></script></a></li>';
          }
        }
      }
       // If subscriber is logged on, display unsub and logoff menu points
      if ((isset($_SESSION['subscriber_valid'])) &&  $_SESSION['subscriber_valid']) {
        $strSubsMenu .= '<li><a href="?do=subscriptions">' . _('Subscriptions') . '</a></li>';
        $strSubsMenu .= '<li><a href="' . WEB_URL . '/index.php?subscriber_logout=1">' . _('Logout') . '</a></li>';
      }
      if (!empty($strSubsMenu)){
        $strSubsMenu .=  '</ul>';
      }
      }
    }
?>
    <!doctype html>
    <html lang="en">

    <head>
      <meta charset="utf-8">
      <title><?php echo $page_name . " - " . NAME ?></title>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="description" content="Current service status for <?php echo NAME; ?> can be found here as well as incident history.">
      <link rel="shortcut icon" href="<?php echo WEB_URL; ?>/favicon.ico" type="image/png">
      <link rel="stylesheet" href="<?php echo WEB_URL; ?>/vendor/bootstrap/css/bootstrap.min.css">
      <link rel="stylesheet" href="<?php echo WEB_URL; ?>/css/main.css" media="screen">
      <link rel="stylesheet" href="<?php echo WEB_URL; ?>/css/print.css" media="print">
      <link rel="apple-touch-icon" sizes="57x57" href="<?php echo WEB_URL; ?>/favicon/apple-icon-57x57.png">
      <link rel="apple-touch-icon" sizes="60x60" href="<?php echo WEB_URL; ?>/favicon/apple-icon-60x60.png">
      <link rel="apple-touch-icon" sizes="72x72" href="<?php echo WEB_URL; ?>/favicon/apple-icon-72x72.png">
      <link rel="apple-touch-icon" sizes="76x76" href="<?php echo WEB_URL; ?>/favicon/apple-icon-76x76.png">
      <link rel="apple-touch-icon" sizes="114x114" href="<?php echo WEB_URL; ?>/favicon/apple-icon-114x114.png">
      <link rel="apple-touch-icon" sizes="120x120" href="<?php echo WEB_URL; ?>/favicon/apple-icon-120x120.png">
      <link rel="apple-touch-icon" sizes="144x144" href="<?php echo WEB_URL; ?>/favicon/apple-icon-144x144.png">
      <link rel="apple-touch-icon" sizes="152x152" href="<?php echo WEB_URL; ?>/favicon/apple-icon-152x152.png">
      <link rel="apple-touch-icon" sizes="180x180" href="<?php echo WEB_URL; ?>/favicon/apple-icon-180x180.png">
      <link rel="icon" type="image/png" sizes="192x192" href="<?php echo WEB_URL; ?>/favicon/android-icon-192x192.png">
      <link rel="icon" type="image/png" sizes="32x32" href="<?php echo WEB_URL; ?>/favicon/favicon-32x32.png">
      <link rel="icon" type="image/png" sizes="96x96" href="<?php echo WEB_URL; ?>/favicon/favicon-96x96.png">
      <link rel="icon" type="image/png" sizes="16x16" href="<?php echo WEB_URL; ?>/favicon/favicon-16x16.png">
      <link rel="manifest" href="<?php echo WEB_URL; ?>/favicon/manifest.json">
      <meta name="msapplication-TileColor" content="#ffffff">
      <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
      <meta name="theme-color" content="#ffffff">
      <link href="<?php echo WEB_URL; ?>/vendor/fontawesome/css/all.min.css" rel="stylesheet">
      <?php
      if (!$admin) {
        $headpath = $_SERVER['DOCUMENT_ROOT'] . "/head.txt";
        $headfile = fopen("$headpath", "r") or die("Unable to open head.txt!");
        $head_additionalcode = fread($headfile, filesize($headpath));
        fclose($headfile);
        echo $head_additionalcode;
      } else {
        global $user;
      ?>
        <link rel="stylesheet" href="<?php echo WEB_URL; ?>/css/flatpickr.min.css">
      <?php
      }
      ?>
    </head>

    <body>
      <nav class="navbar navbar-expand-lg navbar-default">
        <div class="container">
          <a class="navbar-brand" href="<?php echo WEB_URL; ?>"><img src="<?php
                                                                          if (strlen(CUSTOM_LOGO_URL) > 1) {
                                                                            echo CUSTOM_LOGO_URL;
                                                                          } else {
                                                                            echo WEB_URL . "/img/logo_white.png";
                                                                          } ?>" alt="logo" class="menu-logo" style="height:50px;">
            <h1 class="ms-3"><?php echo _((defined('TITLE') ? TITLE : "Service Status")); ?></h1>
          </a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggler" aria-controls="navbarToggler" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarToggler">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
              <?php
              if (!$admin) {
              ?>
                <li class="d-flex">
                  <button class="nav-link" id="theme-toggle" accesskey="t" title="(Alt + T)" onclick="themeToggle()"></button>
                </li>
                <li class="nav-item">
                </li>
              <?php
              } else {
                global $user;
              ?>
                <li class="nav-item">
                  <a class="nav-link link-light" href="<?php echo WEB_URL; ?>/admin/"><?php echo _("Dashboard"); ?></a>
                </li>
                <li class="nav-item">
                  <a class="nav-link link-light" href="<?php echo WEB_URL; ?>/admin/?do=user"><?php printf(_("User (%s)"), $user->get_username()); ?></a>
                </li>
                <li class="nav-item">
                  <a class="nav-link link-light" href="<?php echo WEB_URL; ?>/admin/?do=settings"><?php echo _("Services & Users"); ?></a>
                </li>
                <li class="nav-item">
                  <a class="nav-link link-light" href="<?php echo WEB_URL; ?>/admin/?do=options"><?php echo _("Options"); ?></a>
                </li>
                <li class="nav-item">
                  <a class="nav-link link-light" href="<?php echo WEB_URL; ?>/admin/?do=logout"><?php echo _("Logout"); ?></a>
                </li>
                <li class="d-flex">
                  <button class="nav-link" id="theme-toggle" accesskey="t" title="(Alt + T)" onclick="themeToggle()"></button>
                </li>
              <?php
              }
              ?>
            </ul>
            <?php echo $strSubsMenu; ?>
          </div>
        </div>
      </nav>
      <main id="<?php echo $page_id; ?>" class="container wrapper<?php echo $admin ? ' admin' : ''?>">
        <?php
      }
      /**
       * Renders a toggle switch
       * Created by Yigit Kerem Oktay
       * @param String $toggletext will decide what the description text next to the toggle will be
       * @param String $input_name will decide what the HTML Name attribute of the toggle will be
       * @param Boolean $checked will decide if the toggle will initially be on or off
       */
      public static function render_toggle($toggletext, $input_name, $checked)
      {
        ?>
              <div>
              <h3><?php echo $toggletext; ?> 
                <label class="switch d-block float-end">
                    <input type="checkbox" name="<?php echo $input_name; ?>" <?php if ($checked) {
                      echo "checked";
                                                 } ?> >
                    <span class="slider round"></span>
                </label>
              </h3>
              </div>
        <?php
      }
      /**
       * Renders footer
       * @param Boolean $admin decides whether to load admin scripts
       */
      public static function render_footer($admin = false) {
        global $negotiator;
        $lang_names = $negotiator->get_accepted_langs();
      ?>
      </main>
      <footer id="footerwrap">
        <div class="container pb-3">
          <div class="row centered">
            <div class="col-md-4 text-left"><a class="link-light" href="https://github.com/server-status-project/server-status/graphs/contributors" target=”_blank” rel=”noopener noreferrer”>Copyright © <?php echo date("Y"); ?> Server Status Project Contributors </a><?php if (strlen(COPYRIGHT_TEXT) > 1) {
                                                                                                                                                                                                                                                                            echo " and " . COPYRIGHT_TEXT;
                                                                                                                                                                                                                                                                          } ?></div>
            <div class="col-md-4 text-center">
              <div class="btn-group">
                <button type="button" class="btn btn-primary"><?php echo '<img src="' . WEB_URL . '/locale/' . $_SESSION['locale'] . '/flag.png" alt="' . $lang_names[$_SESSION['locale']] . '">' . $lang_names[$_SESSION['locale']]; ?></button>
                <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                  <span class="visually-hidden"><?php echo _("Toggle Dropdown"); ?></span>
                </button>
                <div class="dropdown-menu">
                  <?php
                  foreach ($lang_names as $key => $value) {
                    echo '<a href="?lang=' . $key . '" class="dropdown-item"><img src="' . WEB_URL . '/locale/' . $key . '/flag.png" alt="' . $value . '">' . $value . '</a>';
                  }
                  ?>
                  <hr class="dropdown-divider">
                  <a href="https://poeditor.com/join/project/37SpmJtyOm" target=”_blank” rel=”noopener noreferrer”><?php echo _("Help with translation!"); ?></a>
                </div>
              </div>
            </div>
            <div class="col-md-4 text-right"><a class="link-light me-3" href="<?php echo IMPRINT_URL; ?>"><?php echo _("Imprint"); ?></a><a class="link-light" href="<?php echo POLICY_URL; ?>"><?php echo _("Privacy Policy"); ?></a></div>
          </div>
          <!--/row -->
        </div>
        <!--/container -->
      </footer>
      <script src="<?php echo WEB_URL; ?>/vendor/jquerry/jquery-3.6.0.min.js"></script>
      <script src="<?php echo WEB_URL; ?>/vendor/jquerry/jquery.timeago.js"></script>
      <script src="<?php echo WEB_URL; ?>/vendor/bootstrap/js/bootstrap.min.js"></script>
      <script src="<?php echo WEB_URL; ?>/js/main.js"></script>
      <script src="<?php echo WEB_URL; ?>/js/themeToggle.js"></script>
      <?php if ($admin) { ?>
        <script src="<?php echo WEB_URL; ?>/vendor/flatpickr/flatpickr.min.js"></script>
        <script src="<?php echo WEB_URL; ?>/js/admin.js"></script>
      <?php } ?>
      <?php if (GOOGLE_RECAPTCHA) {
      ?><script src='https://www.google.com/recaptcha/api.js'></script><?php
                                                                      } ?>
    </body>

    </html>
<?php
      }
    }
