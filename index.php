<?php
require("header.php");
require("footer.php");

if (!file_exists("config.php"))
{
  require("install.php");
}
else{

require("config.php");
require("classes/constellation.php");

$offset = 0;

if (isset($_GET['ajax']))
{
  $constellation->render_incidents(false,$_GET['offset'],5);
  exit();
}else if (isset($_GET['offset']))
{
  $offset = $_GET['offset'];
}

render_header("Status");
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

render_footer();
}