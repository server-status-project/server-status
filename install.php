<?php 
require_once("template.php");
define("WEB_URL", "."); //Website name
define("NAME", _('Status page')); //Website name
require_once("classes/locale-negotiator.php");

$negotiator = new LocaleNegotiator("en_GB");
$message = "";
if (!isset($_SESSION['locale'])||isset($_GET['lang']))
{
	$override = ((isset($_GET['lang']))?$_GET['lang']:null);
	$best_match = $negotiator->negotiate($override);
	$_SESSION['locale'] = $best_match;
	setlocale(LC_ALL, $_SESSION['locale'].".UTF-8");

	bindtextdomain("server-status", __DIR__ . "/locale/");
	bind_textdomain_codeset($_SESSION['locale'], "utf-8"); 
	textdomain("server-status");
}

if (isset($_POST['server']))
{
	$mysqli = new mysqli($_POST['server'],$_POST['dbuser'],$_POST['dbpassword'],$_POST['database']);

	if ($mysqli->connect_errno) {
		$message .= sprintf(_("Connection failed: %s\n"), $mysqli->connect_error);
	}

	if (isset($_POST['url']) && filter_var($_POST['url'], FILTER_VALIDATE_URL) === false)
	{
		$message .= _("Please set valid url!");
	}

	//Ostatní má checky existence ve funkci pro pridani 
	if (0 == strlen(trim($_POST['servername']))){
		$messages[] = _("Server name");
	} 

	if (0 == strlen(trim($_POST['url']))){
		$messages[] = _("Url");
	} 

	if (0 == strlen(trim($_POST['mailer']))){
		$messages[] = _("Mailer name");
	} 

	if (0 == strlen(trim($_POST['title']))){
		$messages[] = _("Title");
	} 

	if (0 == strlen(trim($_POST['mailer_email']))){
		$messages[] = _("Mailer email");
	} 

	if (0 == strlen(trim($_POST['server']))){
		$messages[] = _("Database server");
	} 

	if (0 == strlen(trim($_POST['database']))){
		$messages[] = _("Database name");
	} 

	if (0 == strlen(trim($_POST['dbuser']))){
		$messages[] = _("Database user");
	} 

	if (0 == strlen(trim($_POST['dbpassword'])))
	{
		$messages[] = _("Database password");
	}
	if (isset($messages))
	{
		$message .= _("Please enter");
		$message .= implode(", ", $messages);
	}
}

if(isset($_POST['server']) && empty($message))
{	
	define("MAILER_NAME", $_POST['mailer']);
	define("MAILER_ADDRESS", $_POST['mailer_email']);
	define("INSTALL_OVERRIDE", true);

	//No need to include config, as we have connection from testing it... :)
	//There may be better way to do this...
	$sql = file_get_contents("install.sql");
	$array = explode(";", $sql);
	
	foreach ($array as $value) {
		$val = trim($value);
		if (empty($val))
		{
			continue;
		}
		$q_res = $mysqli->query($value);
		if ($q_res === false)
		{
			$message = sprintf(_("Error while creating database. Please check permission for your account or MYSQL version.<br>Error: %s"), $mysqli->error);
			break;
		}
	}

	if (empty($message))
	{
		require_once("classes/constellation.php");

		User::add();
	}

	if (empty($message))
	{
		//Create config
		$config = file_get_contents("config.php.template");
		$config = str_replace("##name##", $_POST['servername'], $config);
		$config = str_replace("##title##", $_POST['title'], $config);
		$config = str_replace("##url##", $_POST['url'], $config);
		$config = str_replace("##mailer##", $_POST['mailer'], $config);
		$config = str_replace("##mailer_email##", $_POST['mailer_email'], $config);
		$config = str_replace("##server##", $_POST['server'], $config);
		$config = str_replace("##database##", $_POST['database'], $config);
		$config = str_replace("##user##", $_POST['dbuser'], $config);
		$config = str_replace("##password##", $_POST['dbpassword'], $config);
		$config = str_replace("##name##", $_POST['servername'], $config);
		$config = str_replace("##tg_bot_token##", $_POST['tgtoken'], $config);
		$config = str_replace("##tg_bot_username##", $_POST['tgbot'], $config);
		$config = str_replace("##policy_name##", $_POST['policy_name'], $config);
		$config = str_replace("##address##", $_POST['address'], $config);
		$config = str_replace("##policy_mail##", $_POST['policy_mail'], $config);
		$config = str_replace("##policy_phone##", $_POST['policy_phone'],$config);
		$config = str_replace("##who_we_are##", $_POST['who_we_are'], $config);
		file_put_contents("config.php", $config);
		

		unlink("config.php.template");
		unlink("install.sql");
		unlink(__FILE__);

		header("Location: ".WEB_URL);
	}
}
Template::render_header(_("Install"));
?>
<h1 class="text-center"><?php echo _("Installation");?></h1>
<?php
if (!empty($message))
{
	?>
	<p class="alert alert-danger"><?php echo $message; ?></p>
	<?php 
} 
?>
<summary><?php echo _("We will ask you some basic questions about your website. Most of the settings can be later edited in the config.php file.");?></summary>

<form method="post" action="." class="clearfix install">
	<section class="install-section clearfix">
		<h2><?php echo _("Website details");?></h2>
		<summary><?php echo _("We need a name for your status page (shown behind page title after the dash) and a url of your server status installation (i.e. <a href='#'>https://example.com/status</a> - without the trailing slash), so we can mail users link for forgotten password etc...");?></summary>

		<div class="form-group clearfix">
			<div class="col-sm-6"><label for="servername"><?php echo _("Name");?>: </label><input type="text" name="servername" value="<?php echo ((isset($_POST['servername']))?htmlspecialchars($_POST['servername'], ENT_QUOTES):'');?>" id="servername" placeholder="<?php echo _("Name");?>" class="form-control" required></div>
			<div class="col-sm-6"><label for="url"><?php echo _("Url");?>: </label><input type="url" name="url" value="<?php echo ((isset($_POST['url']))?htmlspecialchars($_POST['url'], ENT_QUOTES):'');?>" id="url" placeholder="<?php echo _("Url");?>" class="form-control" required></div>
		</div>
		<summary><?php echo _("A title that you want to be shown on the top of the page.");?></summary>
		<div class="form-group clearfix">
			<div class="col-sm-6"><label for="title"><?php echo _("Title");?>: </label><input type="text" name="title" value="<?php echo ((isset($_POST['title']))?htmlspecialchars($_POST['title'], ENT_QUOTES):'Server Status');?>" id="title" placeholder="<?php echo _("Title");?>" class="form-control" required></div>
			<div class="col-sm-6"></div>
		</div>
		<summary><?php echo _("Also an email address for mailer would be nice :)");?></summary>
		<div class="form-group clearfix">
			<div class="col-sm-6"><label for="mailer"><?php echo _("Name");?>: </label><input type="text" name="mailer" value="<?php echo ((isset($_POST['mailer']))?htmlspecialchars($_POST['mailer'], ENT_QUOTES):'');?>" id="mailer" placeholder="<?php echo _("Name");?>" class="form-control" required></div>
			<div class="col-sm-6"><label for="mailer_email"><?php echo _("Email");?>: </label><input type="email" name="mailer_email" value="<?php echo ((isset($_POST['mailer_email']))?htmlspecialchars($_POST['mailer_email'], ENT_QUOTES):'');?>" id="mailer_email" placeholder="<?php echo _("Email");?>" class="form-control" required></div>
		</div>
	</section>
	<section class="install-section clearfix">
		<h2><?php echo _("Database connection");?></h2>
		<summary><?php echo _("We need database connection to be able to create tables. Please check that your account has the permission needed to do that.");?></summary>

		<div class="form-group clearfix">
			<div class="col-sm-6"><label for="server"><?php echo _("Server");?>: </label><input type="text" name="server" value="<?php echo ((isset($_POST['server']))?htmlspecialchars($_POST['server'], ENT_QUOTES):'');?>" id="server" placeholder="<?php echo _("Server");?>" class="form-control" required></div>
			<div class="col-sm-6"><label for="database"><?php echo _("Database");?>: </label><input type="text" name="database" value="<?php echo ((isset($_POST['database']))?htmlspecialchars($_POST['database'], ENT_QUOTES):'');?>" id="database" placeholder="<?php echo _("Database");?>" class="form-control" required></div>
		</div>
		<div class="form-group clearfix">
			<div class="col-sm-6"><label for="dbuser"><?php echo _("User");?>: </label><input type="text" name="dbuser" value="<?php echo ((isset($_POST['dbuser']))?htmlspecialchars($_POST['dbuser'], ENT_QUOTES):'');?>" id="dbuser" placeholder="<?php echo _("User");?>" class="form-control" required></div>
			<div class="col-sm-6"><label for="dbpassword"><?php echo _("Password");?>: </label><input type="password" name="dbpassword" value="<?php echo ((isset($_POST['dbpassword']))?htmlspecialchars($_POST['dbpassword'], ENT_QUOTES):'');?>" id="dbpassword" placeholder="<?php echo _("Password");?>" class="form-control" required></div>
		</div>
	</section>
	<section class="install-section clearfix">
		<h2><?php echo _("Telegram");?></h2>
		<summary><?php echo _("You can provide a subscription feature through telegram.");?></summary>

		<div class="form-group clearfix">
			<div class="col-sm-6"><label for="tgtoken"><?php echo _("Telegram bot API Token");?>: </label><input type="text" name="tgtoken" value="<?php echo ((isset($_POST['tgtoken']))?htmlspecialchars($_POST['tgtoken'], ENT_QUOTES):'');?>" id="tgtoken" placeholder="<?php echo _("Telegram Bot API Token");?>" class="form-control" required></div>
			<div class="col-sm-6"><label for="tgbot"><?php echo _("Telegram Bot Username");?>: </label><input type="text" name="tgbot" value="<?php echo ((isset($_POST['tgbot']))?htmlspecialchars($_POST['tgbot'], ENT_QUOTES):'');?>" id="tgbot" placeholder="<?php echo _("Telegram Bot Username");?>" class="form-control" required></div>

		<h2><?php echo _("Privacy Policy");?></h2>
		<summary><?php echo _("Since you are collection personal information, the GDPR forces you to have a privacy policy. Enter the details below.");?></summary>
		<h2><?php echo _("Privacy Policy");?></h2>
		<summary><?php echo _("Since you are collecting personal information, the GDPR needs you to have a privacy policy. Enter the details below.");?></summary>


		<div class="form-group clearfix">
			<div class="col-sm-6"><label for="policy_name"><?php echo _("Name");?>: </label><input type="text" name="policy_name" value="<?php echo ((isset($_POST['policy_name']))?htmlspecialchars($_POST['policy_name'], ENT_QUOTES):'');?>" id="policy_name" placeholder="<?php echo _("Company name");?>" class="form-control" required></div>
			<div class="col-sm-6"><label for="address"><?php echo _("Address");?>: </label><input type="text" name="address" value="<?php echo ((isset($_POST['address']))?htmlspecialchars($_POST['address'], ENT_QUOTES):'');?>" id="address" placeholder="<?php echo _("Full address");?>" class="form-control" required></div>
		</div>
		<div class="form-group clearfix">
			<div class="col-sm-6"><label for="policy_mail"><?php echo _("E-Mail");?>: </label><input type="text" name="policy_mail" value="<?php echo ((isset($_POST['policy_mail']))?htmlspecialchars($_POST['policy_mail'], ENT_QUOTES):'');?>" id="policy_mail" placeholder="<?php echo _("E-Mail");?>" class="form-control" required></div>
			<div class="col-sm-6"><label for="policy_phone"><?php echo _("Phone");?>: </label><input type="text" name="policy_phone" value="<?php echo ((isset($_POST['policy_phone']))?htmlspecialchars($_POST['policy_phone'], ENT_QUOTES):'');?>" id="policy_phone" placeholder="<?php echo _("Phone number");?>" class="form-control"></div>
		</div>
		<div class="form-group clearfix">
			<div class=""><label for="who_we_are"><?php echo _("Who we are");?>: </label><textarea class="form-control" id="who_we_are" rows="3" name="who_we_are" placeholder="<?php echo _("A small text about yourself");?>" value="<?php echo ((isset($_POST['who_we_are']))?htmlspecialchars($_POST['who_we_are'], ENT_QUOTES):'');?>"></textarea></div>

		</div>
	</section>
	<section class="install-section clearfix">
		<h2><?php echo _("User");?></h2>
		<summary><?php echo _("And finally, we need info to create a new user. You don't have to provide it, but then... No status page admin...");?></summary>
		<div class="form-group">
			<div class="col-sm-6"><label for="name"><?php echo _("Name");?>: </label><input type="text" maxlength="50" name="name" value="<?php echo ((isset($_POST['name']))?htmlspecialchars($_POST['name'], ENT_QUOTES):'');?>" id="name" placeholder="<?php echo _("Name");?>" class="form-control" required></div>
			<div class="col-sm-6"><label for="surname"><?php echo _("Surname");?>: </label><input type="text" maxlength="50" name="surname" value="<?php echo ((isset($_POST['surname']))?htmlspecialchars($_POST['surname'], ENT_QUOTES):'');?>" id="surname" placeholder="<?php echo _("Surname");?>" class="form-control" required></div>
		</div>
		<div class="form-group">
			<div class="col-sm-6"><label for="username"><?php echo _("Username");?>:</label><input type="text" maxlength="50" name="username" value="<?php echo ((isset($_POST['username']))?htmlspecialchars($_POST['username'], ENT_QUOTES):'');?>" id="username" placeholder="<?php echo _("Username");?>" class="form-control" required></div>
			<div class="col-sm-6"><label for="email"><?php echo _("Email");?>:</label><input type="email" maxlength="60" name="email" value="<?php echo ((isset($_POST['email']))?htmlspecialchars($_POST['email'], ENT_QUOTES):'');?>" id="email" placeholder="<?php echo _("Email");?>" class="form-control" required><input type="hidden" name="permission" value="0"></div>
		</div>
		<div class="form-group">
			<div class="col-sm-6"><label for="password"><?php echo _("Password");?>:</label><input type="password" name="password" value="<?php echo ((isset($_POST['password']))?htmlspecialchars($_POST['password'], ENT_QUOTES):'');?>" id="password" placeholder="<?php echo _("Password");?>" class="form-control" required></div>
			<div class="col-sm-6">
				<input type="hidden" value="0" name="permission">
				<button type="submit" class="btn btn-success pull-right"><?php echo _("Run install!");?></button>
			</div>
		</div>
	</section>
</form>
<?php
Template::render_footer();