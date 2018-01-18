<?php
require_once("template.php");

if (!file_exists("config.php"))
{
  require_once("install.php");
}
else{

require_once("config.php");
require_once("classes/constellation.php");

$offset = 0;

if (isset($_GET['ajax']))
{
  $constellation->render_incidents(false,$_GET['offset'],5);
  exit();
}else if (isset($_GET['offset']))
{
  $offset = $_GET['offset'];
}

Template::render_header("Status");
?>
    <div class="text-center">
      <h2><?php echo _("Current status");?></h2>
    </div>
    <div id="current">
    <?php $constellation->render_status();?>  
    </div>

<?php if ($mysqli->query("SELECT count(*) FROM status")->num_rows)
{      
  ?>
      <div id="timeline">
        <div class="item">
          <div class="timeline">
            <div class="line text-muted"></div>
            <?php
            $constellation->render_incidents(true,$offset);
            $constellation->render_incidents(false,$offset);
            ?>
          </div>
        </div>
      </div>
<?php } 

Template::render_footer();
}