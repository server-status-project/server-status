<?php
$id = $_SESSION['user'];
if (isset($_GET['id'])) {
	$id = $_GET['id'];
}
try {
	$displayed_user = new User($id);
} catch (Exception $e) {
	header("Location: " . WEB_URL . "/admin/?do=user");
}


if (isset($_POST['password'])) {
	$displayed_user->change_password();
}

if (isset($_POST['username'])) {
	$displayed_user->change_username();
}

if (isset($_POST['name'])) {
	$displayed_user->change_name();
}

if (isset($_POST['email'])) {
	$success = $displayed_user->email_link();
}

if (isset($_POST['permission'])) {
	$displayed_user->change_permission();
}


if (isset($_GET['what']) && $_GET['what'] == 'toggle') {
	$displayed_user->toggle();
}

Template::render_header(_("User"), "user", true);

?>
<div class="text-center">
	<h1><?php
			if ($_SESSION['user'] == $_GET['id']) {
				echo _("User settings");
			} else {
				echo _("User");
			} ?></h1>
</div>
<?php if (isset($message)) { ?>
	<p class="alert alert-danger"><?php echo $message ?></p>
<?php }

if (isset($success)) { ?>
	<p class="alert alert-success"><?php echo $success ?></p>
<?php }
$displayed_user->render_user_settings();
