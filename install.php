<?php 
require_once("header.php");
require_once("footer.php");
define("NAME", 'Status page'); //Website name
render_header("Install");?>
<h1 class="text-center">Installation</h1>
<?php

if (isset($_POST['server']))
{
	$mysqli = new mysqli($_POST['server'],$_POST['dbuser'],$_POST['dbpassword'],$_POST['database']);

	if ($mysqli->connect_errno) {
	    $message = "Connection failed: %s\n", $mysqli->connect_error;
	}
}

if (filter_var($_POST['url'], FILTER_VALIDATE_URL) === false)
{
	$message = "Please set valid url!"
}

//Ostatní má checky existence ve funkci pro pridani 
if (0 == strlen(trim($_POST['servername']))  || 0 == strlen(trim($_POST['url']))  || 0 == strlen(trim($_POST['mailer']))  
	|| 0 == strlen(trim($_POST['mailer_email']))  || 0 == strlen(trim($_POST['server']))  || 0 == strlen(trim($_POST['database'])) 
	|| 0 == strlen(trim($_POST['dbuser']))  || 0 == strlen(trim($_POST['dbpassword'])))
{
	$message = "Please enter all data!";
}

if(isset($_POST['server']) && !isset($message))
{	
	define("INSTALL_OVERRIDE", true);

	//No need to include config, as we have connection from testing it... :)
	//There may be better way to do this...
	$sql = file_get_contents("install.sql");
	$array = explode(";", $sql);
	
	foreach ($array as $value) {
		$q_res = $mysqli->query($value);
		if ($q_res === false)
		{
			$message = "Error while creating database. Please check permission for your account or MYSQL version.<br>Error: ".$mysqli->error;
			break;
		}
	}

	if (!isset($message))
	{
		require("classes/constellation.php");

		User::add();
	}

	if (!isset($message))
	{
		//Create config
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

		unlink("config.php.temlpate");
		unlink("install.sql");
		unlink(__FILE__);

		header("Location: /");
	}
}

if (isset($message))
{
?>
<p class="alert alert-danger"><?php echo $message; ?></p>
<?php 
} 
?>
<form method="post" action="install.php" class="clearfix install">
	<section class="install-section clearfix">
		<h2>Website details</h2>
		<summary>We need a name for your status page and a url, so we can mail users link for forgotten password etc.</summary>

			<div class="form-group clearfix">
				<div class="col-sm-6"><label for="servername">Name: </label><input type="text" name="servername" value="<?php echo htmlspecialchars($_POST['servername'], ENT_QUOTES);?>" id="servername" placeholder="Name" class="form-control" required></div>
				<div class="col-sm-6"><label for="url">Url: </label><input type="url" name="url" value="<?php echo htmlspecialchars($_POST['url'], ENT_QUOTES);?>" id="url" placeholder="Url" class="form-control" required></div>
			</div>
			<summary>Also an email address for mailer would be nice :)</summary>
			<div class="form-group clearfix">
				<div class="col-sm-6"><label for="mailer">Name: </label><input type="text" name="mailer" value="<?php echo htmlspecialchars($_POST['mailer'], ENT_QUOTES);?>" id="mailer" placeholder="Name" class="form-control" required></div>
				<div class="col-sm-6"><label for="mailer_email">Email: </label><input type="email" name="mailer_email" value="<?php echo htmlspecialchars($_POST['mailer_email'], ENT_QUOTES);?>" id="mailer_email" placeholder="Email" class="form-control" required></div>
			</div>
	</section>
	<section class="install-section clearfix">
		<h2>Database connection</h2>
		<summary>We need database connection to be able to create tables. Please check that your account has the permission needed to do that.</summary>

			<div class="form-group clearfix">
				<div class="col-sm-6"><label for="server">Server: </label><input type="text" name="server" value="<?php echo htmlspecialchars($_POST['server'], ENT_QUOTES);?>" id="server" placeholder="Server" class="form-control" required></div>
				<div class="col-sm-6"><label for="database">Database: </label><input type="text" name="database" value="<?php echo htmlspecialchars($_POST['database'], ENT_QUOTES);?>" id="database" placeholder="Database" class="form-control" required></div>
			</div>
			<div class="form-group clearfix">
				<div class="col-sm-6"><label for="dbuser">User: </label><input type="text" name="dbuser" value="<?php echo htmlspecialchars($_POST['dbuser'], ENT_QUOTES);?>" id="dbuser" placeholder="User" class="form-control" required></div>
				<div class="col-sm-6"><label for="dbpassword">Password: </label><input type="password" name="dbpassword" value="<?php echo htmlspecialchars($_POST['dbpassword'], ENT_QUOTES);?>" id="dbpassword" placeholder="Password" class="form-control" required></div>
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