<?php
if (isset($_GET['new'])) {
	Service::add();
}

if (isset($_GET['edit'])) {
	Service::edit();
}

/*if (isset($_GET['delete']))
{
	Service::delete();
}*/

$boolEdit						= false;
$service_value			= isset($_POST['service']) ? $_POST['service'] : '';
$description_value	= isset($_POST['description']) ? $_POST['description'] : '';
$url_value 					= isset($_POST['url']) ? $_POST['url'] : '';
$group_id_value			= isset($_POST['group_id']) ? $_POST['group_id'] : '';

if (isset($_GET['id']) && !isset($_POST['id'])) {
	$service_id = (int) $_GET['id'];
	$boolEdit = true;
	$stmt = $mysqli->prepare("SELECT * FROM services WHERE id LIKE ?");
	$stmt->bind_param("i", $service_id);
	$stmt->execute();
	$query = $stmt->get_result();
	$data = $query->fetch_assoc();
	//print_r($data);
	$service_value			= $data['name'];
	$description_value	= $data['description'];
	$url_value					= $data['url'];
	$group_id_value			= $data['group_id'];
}


if (!$boolEdit) {

	Template::render_header(_("New service"), "service", true); ?>
	<div class="text-center">
		<h2><?php echo _("Add new service"); ?></h2>
	</div>
<?php
	$form_url = WEB_URL . '/admin/?do=new-service&amp;new=service';
} else {
	Template::render_header(_("New service"), "service", true); ?>
	<div class="text-center">
		<h2><?php echo _("Add new service"); ?></h2>
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
		<div class="col-sm-6"><label for="service"><?php echo _("Service"); ?>: </label><input type="text" maxlength="50" name="service" value="<?php echo ((isset($_POST['service'])) ? htmlspecialchars($_POST['service'], ENT_QUOTES) : $service_value); ?>" id="service" placeholder="<?php echo _("service"); ?>" class="form-control" required></div>
		<div class="col-sm-6"><label for="description"><?php echo _("Description"); ?>: </label><input type="text" maxlength="200" name="description" value="<?php echo ((isset($_POST['description'])) ? htmlspecialchars($_POST['description'], ENT_QUOTES) : $description_value); ?>" id="description" placeholder="<?php echo _("Description"); ?>" class="form-control"></div>
		<div class="col-sm-6"><label for="url"><?php echo _("Adress"); ?>: </label><input type="text" maxlength="50" name="url" value="<?php echo ((isset($_POST['url'])) ? htmlspecialchars($_POST['url'], ENT_QUOTES) : $url_value); ?>" id="url" placeholder="<?php echo _("Adress"); ?>" class="form-control"></div>
	</div>
	<div class="form-group">
		<div class="col-sm-6">
			<label for="group_id"><?php echo _("Service Group"); ?>: </label>
			<select name="group_id" id="group_id" class="form-control">
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
		</div>
	</div>
	<?php
	if ($boolEdit) {
		echo '<input type="hidden" id="id" name="id" value="' . $service_id . '">';
	}
	?>
	<button type="submit" class="btn btn-primary float-end"><?php echo _("Submit"); ?></button>
</form>