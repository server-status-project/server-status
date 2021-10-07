<?php
if (isset($_GET['new'])) {
  Service::add();
}

if (isset($_GET['edit'])) {
  Service::edit();
}

/*
    if (isset($_GET['delete']))
    {
    Service::delete();
}*/

$boolEdit          = false;
$service_value     = isset($_POST['service']) ? $_POST['service'] : '';
$description_value = isset($_POST['description']) ? $_POST['description'] : '';
$group_id_value    = isset($_POST['group_id']) ? $_POST['group_id'] : '';

if (isset($_GET['id']) && !isset($_POST['id'])) {
  $service_id = (int) $_GET['id'];
  $boolEdit   = true;
  $stmt       = $mysqli->prepare('SELECT * FROM services WHERE id LIKE ?');
  $stmt->bind_param('i', $service_id);
  $stmt->execute();
  $query = $stmt->get_result();
  $data  = $query->fetch_assoc();
  // print_r($data);
  $service_value     = $data['name'];
  $description_value = $data['description'];
  $group_id_value    = $data['group_id'];
}


if (!$boolEdit) {
  Template::render_header(_('New service'), 'service', true); ?>
  <div class="text-center">
    <h2><?php echo _('Add new service'); ?></h2>
  </div>
<?php
  $form_url = WEB_URL . '/admin/?do=new-service&amp;new=service';
} else {
  Template::render_header(_('New service'), 'service', true);
?>
  <div class="text-center">
    <h2><?php echo _('Add new service'); ?></h2>
  </div>
<?php
  $form_url = WEB_URL . '/admin/?do=edit-service&amp;edit&amp;id=' . $service_id;
}
?>
<form action="<?php echo $form_url; ?>" method="POST" class="form-horizontal">
  <?php if (isset($message)) { ?>
    <p class="alert alert-danger"><?php echo $message ?></p>
  <?php
  } ?>
  <div class="form-group">
    <div class="input-group mb-3">
      <span class="input-group-text" id="service"><?php echo _("Service"); ?></span>
      <input type="text" class="form-control" maxlength="50" name="service" value="<?php echo ((isset($_POST['service'])) ? htmlspecialchars($_POST['service'], ENT_QUOTES) : $service_value); ?>" id="service" class="form-control" aria-describedby="service" required>
    </div>
    <div class="input-group mb-3">
      <span class="input-group-text" id="description"><?php echo _("Description"); ?></span>
      <input type="text" class="form-control" maxlength="50" name="description" value="<?php echo ((isset($_POST['description'])) ? htmlspecialchars($_POST['description'], ENT_QUOTES) : $description_value); ?>" id="description" class="form-control" aria-describedby="description">
    </div>

    <div class="input-group mb-3">
      <label class="input-group-text" for="group_id"><?php echo _("Service Group"); ?></label>
      <select class="form-select" name="group_id" id="group_id">
        <?php
        if (!empty($group_id_value)) {
          $group_id = $group_id_value;
        } else {
          $group_id = null;
        }
        $groups = ServiceGroup::get_groups();
        foreach ($groups as $key => $value) {
          if ($group_id == $key) {
            echo '<option value="' . $key . '" selected>' . $value . '</option>';
          } else {
            echo '<option value="' . $key . '">' . $value . '</option>';
          }
        }
        ?>
      </select>
      <button class="btn btn-outline-primary" type="submit"><?php echo _("Submit"); ?></button>
    </div>
  </div>
  <?php
  if ($boolEdit) {
    echo '<input type="hidden" id="id" name="id" value="' . $service_id . '">';
  }
  ?>
</form>