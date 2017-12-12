<?php 
$offset = 0;
if (isset($_GET['ajax']))
{
  $constellation->render_incidents(false,$_GET['offset'],5);
  exit();
}else if (isset($_GET['offset']))
{
  $offset = $_GET['offset'];
}

if ($_GET['new']=="incident")
{
  Incident::add();
}

if (isset($_GET['delete']))
{
  Incident::delete($_GET['delete']);
}

//TODO: Pamatovat si data

render_header("Dashboard", true); 
?>

  <div class="text-center">
    <h1>Dashboard</h1>
    <h3>Welcome <?php echo $user->get_name();?></h3>
  </div>

  <div id="current">
    <?php
    $services = $constellation->render_status(true);
    ?>
  </div>
  <div id="timeline">
    <div class="item">
      <div class="timeline">
        <div class="line text-muted"></div>
        <h3>New incident</h3>
        <form id="new-incident" action="/admin/?new=incident" method="POST" class="clearfix">
        <article class="panel">
          <?php if (isset($message))
          {?>
          <p class="alert alert-danger"><?php echo $message?></p>
          <?php
          } ?>
          <div id="status-container" class="clearfix">
          <?php 
          if (!is_array($_POST['services']))
          {
            $_POST['services'] = array($_POST['services']);
          }
          foreach($services as $service){
          ?>
            <div class="item clearfix">
              <div class="service"><?php if ($service->get_status()!=-1){?><input type="checkbox" name="services[]" value="<?php echo $service->get_id(); ?>" <?php echo (in_array($service->get_id(), $_POST['services']))?"checked":'';?> id="service-<?php echo $service->get_id(); ?>"><?php } ?><label for="service-<?php echo $service->get_id(); ?>"><?php echo $service->get_name(); ?></label></div>
              <div class="status <?php echo $classes[$service->get_status()];?>"><?php echo $statuses[$service->get_status()];?></div>
            </div>
          <?php
          }
          ?>
          </div>
        </article>
          <article class="panel new panel-primary">
            <div class="panel-heading icon">
              <i class="glyphicon glyphicon-info-sign"></i>
            </div>
            <div class="panel-heading clearfix">
              <input type="text" name="title" id="title" placeholder="Title" value="<?php echo htmlspecialchars($_POST['title']); ?>" required> <span id="time"><input id="time_input" type="text" pattern="(\d{4}-[01]\d-[0-3]\dT[0-2]\d:[0-5]\d:[0-5]\d\.\d+([+-][0-2]\d:[0-5]\d|Z))|(\d{4}-[01]\d-[0-3]\dT[0-2]\d:[0-5]\d:[0-5]\d([+-][0-2]\d:[0-5]\d|Z))|(\d{4}-[01]\d-[0-3]\dT[0-2]\d:[0-5]\d([+-][0-2]\d:[0-5]\d|Z))" name="time" value="<?php echo htmlspecialchars($_POST['time']); ?>" class="pull-right" title="Use ISO 8601 format (e.g. 2017-11-23T19:50:51+00:00)" placeholder="Time"></span>
            </div>
            <div class="panel-body">
              <textarea name="text" placeholder="Here goes your text..." required><?php echo htmlspecialchars($_POST['text']); ?></textarea>
            </div>
            <div class="panel-footer">
              <small>Posted by: <?php echo $user->get_username();?> <span class="pull-right" id="end_time_wrapper">Ending:&nbsp;<input id="end_time" title="Use ISO 8601 format (e.g. 2017-11-23T19:50:51+00:00)" type="text" pattern="(\d{4}-[01]\d-[0-3]\dT[0-2]\d:[0-5]\d:[0-5]\d\.\d+([+-][0-2]\d:[0-5]\d|Z))|(\d{4}-[01]\d-[0-3]\dT[0-2]\d:[0-5]\d:[0-5]\d([+-][0-2]\d:[0-5]\d|Z))|(\d{4}-[01]\d-[0-3]\dT[0-2]\d:[0-5]\d([+-][0-2]\d:[0-5]\d|Z))" name="end_time" class="pull-right" placeholder="End time" value="<?php echo htmlspecialchars($_POST['end_time']); ?>"></span></small>
            </div>
          </article>
          <select class="form-control pull-left" id="type" name="type">
            <?php 
            if (isset($_POST['type']))
            {
              $selected_status = $_POST['type'];
            }else
            {
              $selected_status = 2;
            }

            foreach ($statuses as $key => $value) {
              echo '<option value="'.$key.'"'.(($key==$selected_status)?' selected':'').'>'.$value.'</option>';
            }
            ?>
          </select>
          <button type="submit" class="btn btn-primary pull-right">Submit</button>
        </form>
         <?php
            $constellation->render_incidents(true,$offset,5,true);
            $constellation->render_incidents(false,$offset,5,true);
          ?>
      </div>
    </div>
  </div>
