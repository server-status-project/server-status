<?php 
class Template(){
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
        <link rel="shortcut icon" href="/favicon.ico" type="image/png">
        <link rel="stylesheet" href="/css/bootstrap.min.css">
        <link rel="stylesheet" href="/css/main.css" media="screen">
        <link rel="stylesheet" href="/css/print.css" media="print">
        <link href="/css/font-awesome.min.css" rel="stylesheet">
      </head>
      <body>
        <div class="navbar navbar-default" role="navigation">
          <div class="container">
            <div class="navbar-header">
              <a class="navbar-brand" href="<?php echo WEB_URL;?>"><img src="/img/logo_white.png" alt="logo" class="menu-logo" width="50" height="50"></a>
            </div>
            <div class="navbar-left hidden-xs">
              <ul class="nav navbar-nav">
                <li><a href="<?php echo WEB_URL;?>/"><h1><?php echo _("Service Status");?></h1></a></li>
              </ul>
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
        <link rel="shortcut icon" href="/favicon.ico" type="image/png">
        <link rel="stylesheet" href="/css/bootstrap.min.css">
        <link rel="stylesheet" href="/css/main.css">
        <link href="/css/font-awesome.min.css" rel="stylesheet">
        <link href="/css/jquery.growl.css" rel="stylesheet">
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
              <a class="navbar-brand" href="<?php echo WEB_URL;?>/admin"><img src="/img/logo_white.png" alt="logo" class="menu-logo" width="50" height="50"></a>
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

      public static function render_footer($admin = false)
      {
        global $lang_names;
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
                <ul class="dropdown-menu">
                  <?php 
                  foreach ($lang_names as $key => $value) {
                    echo '<a href="?lang='.$key.'"><img src="'.WEB_URL.'/locale/'.$key.'/flag.png" alt="'.$value.'">'.$value.'</a>';
                  }
                  ?>
                  <hr role="separator" class="divider">
                  <a href="https://poeditor.com/join/project/37SpmJtyOm"><?php echo _("Help with translation!");?></a>
                </ul>
              </div>
            </div>
            <div class="col-md-4 text-right"><a href="https://github.com/Pryx/server-status/" target="_blank"><i class="fa fa-github" aria-hidden="true"></i></a></div>
          </div><!--/row -->
        </div><!--/container -->
      </div>
      <script src="/js/vendor/jquery-1.11.2.min.js"></script>
      <script src="/js/vendor/jquery.timeago.js"></script>
      <?php if ($admin){?>
      <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
      <script src="/js/admin.js"></script>
      <script src="/js/vendor/jquery.growl.js"></script>
      <? }?>
      <script src="/js/vendor/bootstrap.min.js"></script>
      <script src="/js/main.js"></script>
    </body>
    </html>
    <?
  }
}