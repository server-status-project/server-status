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

$boolEdit 					 = false;
$group_value 				 = isset($_POST['group']) ? $_POST['group'] : '';
$description_value   = isset($_POST['description']) ? $_POST['description'] : '';
$visibility_id_value = isset($_POST['visibility_id']) ? $_POST['visibility_id'] : '';

if (isset($_GET['id']) && !isset($_POST['id'])) {
	$group_id = (int) $_GET['id'];
	$boolEdit = true;
	$stmt = $mysqli->prepare("SELECT * FROM services_groups WHERE id LIKE ?");
	$stmt->bind_param("i", $group_id);
	$stmt->execute();
	$query = $stmt->get_result();
	$data = $query->fetch_assoc();
	$group_value   = $data['name'];
	$description_value   = $data['description'];
	$visibility_id_value = $data['visibility'];
}


if (!$boolEdit) {

	Template::render_header(_("New service group"), "servicegroup", true); ?>
	<div class="text-center">
		<h2><?php echo _("Add new service group"); ?></h2>
	</div>
<?php
	$form_url = WEB_URL . '/admin/?do=new-service-group&amp;new=group';
} else {
	Template::render_header(_("Edit service group"), "servicegroup", true); ?>
	<div class="text-center">
		<h2><?php echo _("Edit service group"); ?></h2>
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
		<div class="col-sm-6"><label for="group"><?php echo _("Service Group Name"); ?>: </label><input type="text" maxlength="50" name="group" value="<?php echo ((isset($_POST['group'])) ? htmlspecialchars($_POST['group'], ENT_QUOTES) : $group_value); ?>" id="group" placeholder="<?php echo _("service group name"); ?>" class="form-control" required></div>
		<div class="col-sm-6"><label for="description"><?php echo _("Description"); ?>: </label><input type="text" maxlength="100" name="description" value="<?php echo ((isset($_POST['description'])) ? htmlspecialchars($description_value, ENT_QUOTES) : $description_value); ?>" id="description" placeholder="<?php echo _("Description"); ?>" class="form-control"></div>
	</div>
	<div class="form-group">
		<div class="col-sm-6">
			<label for="visibility_id"><?php echo _("Visibility"); ?>: </label>
			<select name="visibility_id" id="visibility_id" class="form-control">
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
		</div>
	</div>
	<?php
	if ($boolEdit) {
		echo '<input type="hidden" id="id" name="id" value="' . $group_id . '">';
	}
	?>
	<button type="submit" class="btn btn-primary float-end"><?php echo _("Submit"); ?></button>
</form>