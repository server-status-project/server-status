<?php 
require("config.php");
require("template.php");
Template::render_header("Page not found");
?>
  <div class="text-center">
    <h1><?php echo _("Page Not Found");?></h1>
    <p><?php echo _("Sorry, but the page you were trying to view does not exist.");?></p>
  </div>
<?php 
Template::render_footer();