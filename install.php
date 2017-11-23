<?php 
require_once("header.php");
require_once("footer.php");
define("NAME", 'Status page'); //Website name
render_header("Install");?>
<h1 class="text-center">Installation</h1>
<?php
if(isset($_POST['url']))
{	
	define("INSTALL_OVERRIDE", true);
	error_reporting(E_ALL);
	require("classes/constellation.php");
	$config = file_get_contents("config.php.template");
	$config = str_replace("##name##", $_POST['servername'], $config);
	$config = str_replace("##url##", $_POST['url'], $config);
	$config = str_replace("##mailer##", $_POST['mailer'], $config);
	$config = str_replace("##mailer_email##", $_POST['mailer_email'], $config);
	$config = str_replace("##server##", $_POST['server'], $config);
	$config = str_replace("##database##", $_POST['database'], $config);
	$config = str_replace("##user##", $_POST['dbuser'], $config);
	$config = str_replace("##password##", $_POST['dbpassword'], $config);
	$config = str_replace("##name##", $_POST['servername'], $config);
	file_put_contents("config.php", $config);
	require("config.php");
	$sql = file_get_contents("install.sql");
	$array = explode(";", $sql);
	//TODO: Checkovat pls
	foreach ($array as $value) {
		$mysqli->query($value);
	}

	User::add();
	if (isset($message))
	{
		echo "<p class=\"alert alert-danger\">$message</p>";
		render_footer();
		die;
	}

	unlink("config.php.temlpate");
	unlink("install.sql");
	unlink(__FILE__);

	header("Location: /");
}else{
?>
<form method="post" action="install.php" class="clearfix install">
	<section class="install-section clearfix">
		<h2>Website details</h2>
		<summary>We need a name for your status page and a url, so we can mail users link for forgotten password etc.</summary>

			<div class="form-group clearfix">
				<div class="col-sm-6"><label for="servername">Name: </label><input type="text" name="servername" id="servername" placeholder="Name" class="form-control" required></div>
				<div class="col-sm-6"><label for="url">Url: </label><input type="url" name="url" id="url" placeholder="Url" class="form-control" required></div>
			</div>
			<summary>Also an email address for mailer would be nice :)</summary>
			<div class="form-group clearfix">
				<div class="col-sm-6"><label for="mailer">Name: </label><input type="text" name="mailer" id="mailer" placeholder="Name" class="form-control" required></div>
				<div class="col-sm-6"><label for="mailer_email">Email: </label><input type="email" name="mailer_email" id="mailer_email" placeholder="Email" class="form-control" required></div>
			</div>
	</section>
	<section class="install-section clearfix">
		<h2>Database connection</h2>
		<summary>We need database connection to be able to create tables. Please check that your account has the permission needed to do that.</summary>

			<div class="form-group clearfix">
				<div class="col-sm-6"><label for="server">Server: </label><input type="text" name="server" id="server" placeholder="Server" class="form-control" required></div>
				<div class="col-sm-6"><label for="database">Database: </label><input type="text" name="database" id="database" placeholder="Database" class="form-control" required></div>
			</div>
			<div class="form-group clearfix">
				<div class="col-sm-6"><label for="dbuser">User: </label><input type="text" name="dbuser" id="dbuser" placeholder="User" class="form-control" required></div>
				<div class="col-sm-6"><label for="dbpassword">Password: </label><input type="password" name="dbpassword" id="dbpassword" placeholder="Password" class="form-control" required></div>
			</div>
	</section>
	<section class="install-section clearfix">
		<h2>User</h2>
		<summary>And finally, we need info to create a new user. You don't have to provide it, but then... No status page admin...</summary>
<div class="form-group">
		<div class="col-sm-6"><label for="name">Name: </label><input type="text" maxlength="50" name="name" value="<?php echo htmlspecialchars($_POST['name'],ENT_QUOTES);?>" id="name" placeholder="Name" class="form-control" required></div>
		<div class="col-sm-6"><label for="surname">Surname: </label><input type="text" maxlength="50" name="surname" value="<?php echo htmlspecialchars($_POST['surname'],ENT_QUOTES);?>" id="surname" placeholder="Surname" class="form-control" required></div>
	</div>
	<div class="form-group">
		<div class="col-sm-6"><label for="username">Username:</label><input type="text" maxlength="50" name="username" value="<?php echo htmlspecialchars($_POST['username'],ENT_QUOTES);?>" id="username" placeholder="Username" class="form-control" required></div>
		<div class="col-sm-6"><label for="email">Email:</label><input type="email" maxlength="60" name="email" value="<?php echo htmlspecialchars($_POST['email'],ENT_QUOTES);?>" id="email" placeholder="Email" class="form-control" required></div>
	</div>
	<div class="form-group">
		<div class="col-sm-6"><label for="password">Password:</label><input type="password" name="password" value="<?php echo htmlspecialchars($_POST['password'],ENT_QUOTES);?>" id="password" placeholder="Password" class="form-control" required></div>
		<div class="col-sm-6">
			<input type="hidden" value="0" class="permission">
			<button type="submit" class="btn btn-success pull-right">Run install!</button>
		</div>
	</div>
	</section>
</form>
<?php
render_footer();
}