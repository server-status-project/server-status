<?php
$offset = 0;
if (isset($_GET['ajax'])) {
  $constellation->render_incidents(false, $_GET['offset'], 5);
  exit();
} else if (isset($_GET['offset'])) {
  $offset = $_GET['offset'];
}

if (isset($_GET['new']) && $_GET['new'] == "incident") {
  Incident::add();
}

if (isset($_GET['delete'])) {
  Incident::delete($_GET['delete']);
}
if (isset($_GET['tasks'])) {
  Queue::process_queue();
}

Template::render_header(_("Dashboard"), "dashboard", true);
?>

<div class="text-center">
  <h1><?php echo _("Dashboard"); ?></h1>
  <h3><?php echo _("Welcome"); ?> <?php echo $user->get_name(); ?></h3>
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
      <h3><?php echo _("New incident"); ?></h3>
      <form id="new-incident" action="<?php echo WEB_URL; ?>/admin/?new=incident" method="POST">
        <div class="servicelist">
          <?php if (isset($message)) { ?>
            <p class="alert alert-danger"><?php echo $message ?></p>
          <?php
          } ?>
          <div id="status-container">
            <?php
            if (isset($_POST['services']) && !is_array($_POST['services'])) {
              $post_services = array($_POST['services']);
            } else {
              $post_services = array();
            }

            foreach ($services as $service) {
            ?>
              <div class="input-group mb-2">
                <?php if ($service->get_status() != -1) { ?>
                  <div class="input-group-text service">
                    <input type="checkbox" name="services[]" value="<?php echo $service->get_id(); ?>" <?php echo (in_array($service->get_id(), $post_services)) ? "checked" : ''; ?> id="service-<?php echo $service->get_id(); ?>">
                  </div>
                  <label id="name" class="input-group-text form-control" for="service-<?php echo $service->get_id(); ?>"><?php echo $service->get_name(); ?></label>
                  <label id="status" class="input-group-text btn-<?php if ($service->get_status() != -1) {
                                                                    echo $classes[$service->get_status()];
                                                                  } ?>" for="service-<?php echo $service->get_id(); ?>"><?php echo $statuses[$service->get_status()]; ?></label>
                <?php } ?>
              </div>
            <?php
            }
            ?>
          </div>
        </div>
        <article class="card new border-primary mb-3">
          <div class="card-colore icon bg-primary"><i class="fa fa-info"></i></div>
          <div class="card-colore card-header bg-primary border-primary">
            <input type="text" name="title" id="title" placeholder="<?php echo _("Title"); ?>" value="<?php echo (isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''); ?>" required>
            <span id="time" class="float-end">
              <input id="time_input" type="text" pattern="(\d{4}-[01]\d-[0-3]\dT[0-2]\d:[0-5]\d:[0-5]\d\.\d+([+-][0-2]\d:[0-5]\d|Z))|(\d{4}-[01]\d-[0-3]\dT[0-2]\d:[0-5]\d:[0-5]\d([+-][0-2]\d:[0-5]\d|Z))|(\d{4}-[01]\d-[0-3]\dT[0-2]\d:[0-5]\d([+-][0-2]\d:[0-5]\d|Z))" name="time" value="<?php echo (isset($_POST['time']) ? htmlspecialchars($_POST['time']) : ''); ?>" title="Use ISO 8601 format (e.g. 2017-11-23T19:50:51+00:00)" placeholder="<?php echo _("Time"); ?>">
              <input id="time_input_js" name="time_js" type="hidden">
            </span>
          </div>
          <div class="card-body text-primary">
            <p class="card-text"><textarea name="text" placeholder="<?php echo _("Here goes your text..."); ?>" required><?php echo (isset($_POST['text']) ? htmlspecialchars($_POST['text']) : ''); ?></textarea></p>
          </div>
          <div class="card-footer bg-transparent border-primary">
            <small><?php echo _("Posted by"); ?>: <?php echo $user->get_username(); ?></small>
            <span class="float-end" id="end_time_wrapper"><?php echo _("Ending"); ?>:&nbsp;
              <input id="end_time" title="Use ISO 8601 format (e.g. 2017-11-23T19:50:51+00:00)" type="text" pattern="(\d{4}-[01]\d-[0-3]\dT[0-2]\d:[0-5]\d:[0-5]\d\.\d+([+-][0-2]\d:[0-5]\d|Z))|(\d{4}-[01]\d-[0-3]\dT[0-2]\d:[0-5]\d:[0-5]\d([+-][0-2]\d:[0-5]\d|Z))|(\d{4}-[01]\d-[0-3]\dT[0-2]\d:[0-5]\d([+-][0-2]\d:[0-5]\d|Z))" name="end_time" placeholder="<?php echo _("End time"); ?>" value="<?php echo (isset($_POST['end_time']) ? htmlspecialchars($_POST['end_time']) : ''); ?>">
              <input id="end_time_js" name="end_time_js" type="hidden">
            </span>
          </div>
        </article>
        <div class="input-group">
          <select class="form-select" id="type" name="type">
            <?php
            if (isset($_POST['type'])) {
              $selected_status = $_POST['type'];
            } else {
              $selected_status = 2;
            }

            foreach ($statuses as $key => $value) {
              echo '<option value="' . $key . '"' . (($key == $selected_status) ? ' selected' : '') . '>' . $value . '</option>';
            }
            ?>
          </select>
          <button class="card-colore btn btn-secondary" type="submit"><?php echo _("Submit"); ?></button>
        </div>
      </form>
      <?php
      $constellation->render_incidents(true, $offset, 5, true);
      $constellation->render_incidents(false, $offset, 5, true);
      ?>
    </div>
  </div>
</div>