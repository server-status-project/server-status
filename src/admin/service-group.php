<?php
if (isset($_GET['new'])) {
  ServiceGroup::add();
}

if (isset($_GET['edit'])) {
  ServiceGroup::edit();
}

if (isset($_GET['delete'])) {
  ServiceGroup::delete();
}

$boolEdit            = false;
$group_value         = isset($_POST['group']) ? $_POST['group'] : '';
$description_value   = isset($_POST['description']) ? $_POST['description'] : '';
$visibility_id_value = isset($_POST['visibility_id']) ? $_POST['visibility_id'] : '';

if (isset($_GET['id']) && !isset($_POST['id'])) {
  $group_id = (int) $_GET['id'];
  $boolEdit = true;
  $stmt     = $mysqli->prepare('SELECT * FROM services_groups WHERE id LIKE ?');
  $stmt->bind_param('i', $group_id);
  $stmt->execute();
  $query               = $stmt->get_result();
  $data                = $query->fetch_assoc();
  $group_value         = $data['name'];
  $description_value   = $data['description'];
  $visibility_id_value = $data['visibility'];
}


if (!$boolEdit) {
  Template::render_header(_('New service group'), 'service-group', true); ?>
  <div class="text-center">
    <h2><?php echo _('Add new service group'); ?></h2>
  </div>
<?php
  $form_url = WEB_URL . '/admin/?do=new-service-group&amp;new=group';
} else {
  Template::render_header(_('Edit service group'), 'service-group', true);
?>
  <div class="text-center">
    <h2><?php echo _('Edit service group'); ?></h2>
  </div>
<?php
  $form_url = WEB_URL . '/admin/?do=edit-service-group&amp;edit&amp;id=' . $group_id;
}
?>

<form action="<?php echo $form_url; ?>" method="POST" class="form-horizontal">
  <?php if (isset($message)) { ?>
    <p class="alert alert-danger"><?php echo $message ?></p>
  <?php
  } ?>
  <div class="form-group">
    <div class="input-group mb-3">
      <span class="input-group-text" id="group"><?php echo _("Service Group Name"); ?></span>
      <input type="text" class="form-control" maxlength="50" name="group" value="<?php echo ((isset($_POST['group'])) ? htmlspecialchars($_POST['group'], ENT_QUOTES) : $group_value); ?>" id="group" class="form-control" aria-describedby="group" required>
    </div>
    <div class="input-group mb-3">
      <span class="input-group-text" id="description"><?php echo _("Description"); ?></span>
      <input type="text" class="form-control" maxlength="50" name="description" value="<?php echo ((isset($_POST['description'])) ? htmlspecialchars($_POST['description'], ENT_QUOTES) : $description_value); ?>" id="description" class="form-control" aria-describedby="description">
    </div>

    <div class="input-group mb-3">
      <label class="input-group-text" for="visibility_id"><?php echo _("Visibility"); ?></label>
      <select class="form-select" name="visibility_id" id="visibility_id">
        <?php
        if (!empty($visibility_id_value)) {
          $visibility_id = $visibility_id_value;
        } else {
          $visibility_id = null;
        }
        //$visibilitys = Service::get_groups();
        foreach ($visibility as $key => $value) {
          if ($visibility_id == $key) {
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
    echo '<input type="hidden" id="id" name="id" value="' . $group_id . '">';
  }
  ?>
</form>