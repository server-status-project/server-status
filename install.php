<?php
require_once("template.php");
define("WEB_URL", "."); //Website name
define("NAME", _('Status page')); //Website name
define("MINIMUM_PHP_VERSION", "5.4.0");
define("IMPRINT_URL", "policy.php"); //Default imprint URL
define("POLICY_URL", "policy.php"); //Default policy URL
define("CUSTOM_LOGO_URL", "");
define("COPYRIGHT_TEXT", "");
require_once("classes/locale-negotiator.php");
require_once("classes/db-class.php");

$isDeveleoperEnvironement = false;
if (isset($_GET["isDev"])) {
	if ($_GET["isDev"] == "devMode") {
		$isDeveleoperEnvironement = true;
	}
}


$negotiator = new LocaleNegotiator("en_GB");
$message = "";
$db = new SSDB();
if (!isset($_SESSION['locale']) || isset($_GET['lang'])) {
	$override = ((isset($_GET['lang'])) ? $_GET['lang'] : null);
	$best_match = $negotiator->negotiate($override);
	$_SESSION['locale'] = $best_match;
	setlocale(LC_ALL, $_SESSION['locale'] . ".UTF-8");

	bindtextdomain("server-status", __DIR__ . "/locale/");
	bind_textdomain_codeset($_SESSION['locale'], "utf-8");
	textdomain("server-status");
}

if (isset($_POST['server'])) {
	$mysqli = new mysqli($_POST['server'], $_POST['dbuser'], $_POST['dbpassword'], $_POST['database']);

	if ($mysqli->connect_errno) {
		$message .= sprintf(_("Connection failed: %s\n"), $mysqli->connect_error);
	}

	if (isset($_POST['url']) && filter_var($_POST['url'], FILTER_VALIDATE_URL) === false) {
		$message .= _("Please set valid url!");
	}

	//OstatnÃ­ mÃ¡ checky existence ve funkci pro pridani
	if (0 == strlen(trim($_POST['servername']))) {
		$messages[] = _("Server name");
	}

	if (0 == strlen(trim($_POST['url']))) {
		$messages[] = _("Url");
	}

	if (0 == strlen(trim($_POST['mailer']))) {
		$messages[] = _("Mailer name");
	}

	if (0 == strlen(trim($_POST['title']))) {
		$messages[] = _("Title");
	}

	if (0 == strlen(trim($_POST['mailer_email']))) {
		$messages[] = _("Mailer email");
	}

	if (0 == strlen(trim($_POST['server']))) {
		$messages[] = _("Database server");
	}

	if (0 == strlen(trim($_POST['database']))) {
		$messages[] = _("Database name");
	}

	if (0 == strlen(trim($_POST['dbuser']))) {
		$messages[] = _("Database user");
	}

	if (0 == strlen(trim($_POST['dbpassword']))) {
		$messages[] = _("Database password");
	}
	if (isset($messages)) {
		$message .= _("Please enter");
		$message .= implode(", ", $messages);
	}
}

if (isset($_POST['server']) && empty($message)) {
	define("MAILER_NAME", $_POST['mailer']);
	define("MAILER_ADDRESS", $_POST['mailer_email']);
	define("INSTALL_OVERRIDE", true);

	//No need to include config, as we have connection from testing it... :)
	//There may be better way to do this...
	$sql = file_get_contents("install.sql");
	$array = explode(";", $sql);

	foreach ($array as $value) {
		$val = trim($value);
		if (empty($val)) {
			continue;
		}
		$q_res = $mysqli->query($value);
		if ($q_res === false) {
			$message = sprintf(_("Error while creating database. Please check permission for your account or MYSQL version.<br>Error: %s"), $mysqli->error);
			break;
		}
	}

	if (empty($message)) {
		require_once("classes/constellation.php");

		User::add();
	}

	if (empty($message)) {
		//Create config
		$config = file_get_contents("config.php.template");
		//$config = str_replace("##name##", htmlspecialchars($_POST['servername'], ENT_QUOTES), $config);
		$db->setSetting($mysqli, "name", htmlspecialchars($_POST['servername'], ENT_QUOTES));
		//$config = str_replace("##title##", htmlspecialchars($_POST['title'], ENT_QUOTES), $config);
		$db->setSetting($mysqli, "title", htmlspecialchars($_POST['title'], ENT_QUOTES));
		//$config = str_replace("##url##", $_POST['url'], $config);
		$db->setSetting($mysqli, "url", $_POST['url']);
		//$config = str_replace("##mailer##", htmlspecialchars($_POST['mailer'], ENT_QUOTES), $config);
		$db->setSetting($mysqli, "mailer", htmlspecialchars($_POST['mailer'], ENT_QUOTES));
		//$config = str_replace("##mailer_email##", htmlspecialchars($_POST['mailer_email'], ENT_QUOTES), $config);
		$db->setSetting($mysqli, "mailer_email", htmlspecialchars($_POST['mailer_email'], ENT_QUOTES));
		$config = str_replace("##server##", htmlspecialchars($_POST['server'], ENT_QUOTES), $config);
		$config = str_replace("##database##", htmlspecialchars($_POST['database'], ENT_QUOTES), $config);
		$config = str_replace("##user##", htmlspecialchars($_POST['dbuser'], ENT_QUOTES), $config);
		$config = str_replace("##password##", htmlspecialchars($_POST['dbpassword'], ENT_QUOTES), $config);
		// Duplicate of lines 122-123 //$config = str_replace("##name##", htmlspecialchars($_POST['servername'], ENT_QUOTES), $config);
		$config = str_replace("##policy_name##", htmlspecialchars($_POST['policy_name'], ENT_QUOTES), $config);
		$config = str_replace("##address##", htmlspecialchars($_POST['address'], ENT_QUOTES), $config);
		$config = str_replace("##policy_mail##", htmlspecialchars($_POST['policy_mail'], ENT_QUOTES), $config);
		$config = str_replace("##policy_phone##", htmlspecialchars($_POST['policy_phone'], ENT_QUOTES), $config);
		$config = str_replace("##who_we_are##", htmlspecialchars($_POST['who_we_are'], ENT_QUOTES), $config);
		$imprint_url_conf = (!empty($_POST['imprint_url'])) ? htmlspecialchars($_POST['imprint_url'], ENT_QUOTES) : $_POST['url'] . "/imprint.php";
		$config = str_replace("##imprint_url##", $imprint_url_conf, $config);
		$policy_url_conf = (!empty($_POST['policy_url'])) ? htmlspecialchars($_POST['policy_url'], ENT_QUOTES) : $_POST['url'] . "/policy.php";
		$config = str_replace("##policy_url##", $policy_url_conf, $config);

		file_put_contents("config.php", $config);

		include_once "create-server-config.php";
		$db->setSetting($mysqli, "dbConfigVersion", "Version2Beta7");
		$db->setSetting($mysqli, "notifyUpdates", "yes");
		$db->setSetting($mysqli, "subscribe_email", "no");
		$db->setSetting($mysqli, "subscribe_telegram", "no");
		$db->setSetting($mysqli, "tg_bot_api_token", "");
		$db->setSetting($mysqli, "tg_bot_username", "");
		$db->setSetting($mysqli, "php_mailer", "no");
		$db->setSetting($mysqli, "php_mailer_host", "");
		$db->setSetting($mysqli, "php_mailer_smtp", "no");
		$db->setSetting($mysqli, "php_mailer_path", "");
		$db->setSetting($mysqli, "php_mailer_port", "");
		$db->setSetting($mysqli, "php_mailer_secure", "no");
		$db->setSetting($mysqli, "php_mailer_user", "");
		$db->setSetting($mysqli, "php_mailer_pass", "");
		$db->setSetting($mysqli, "google_recaptcha", "no");
		$db->setSetting($mysqli, "google_recaptcha_secret", "");
		$db->setSetting($mysqli, "google_recaptcha_sitekey", "");
		$db->setSetting($mysqli, "cron_server_ip", "");
		if (!$isDeveleoperEnvironement) {
			unlink("create-server-config.php");
			unlink("config.php.template");
			unlink("install.sql");
			unlink(__FILE__);
		}
		header("Location: " . WEB_URL);
	}
}
Template::render_header(_("Install"), "install");

$php_version_req = sprintf(_("Minimum PHP version %s"), MINIMUM_PHP_VERSION);
$preq_fail = array("times", "danger");
$preq_ok   = array("check", "success");

$preq_phpver = $preq_fail;
$preq_mysqlnd = $preq_fail;
$preq_writedir = $preq_fail;

// Check if PHP version if > MINIMUM_PHP_VERSION
if (strnatcmp(phpversion(), MINIMUM_PHP_VERSION) >= 0) {
	$preq_phpver = $preq_ok;
}

// Test for mysqlnd precense.  The mysqlnd driver provides some extra functions that is not available
// if the plain mysql package is installed, and mysqli_get_client_stats is one of them. This is documented
// on the PHP site at http://www.php.net/manual/en/mysqlnd.stats.php
// This test is also discussed at https://stackoverflow.com/questions/1475701/how-to-know-if-mysqlnd-is-the-active-driver
if (function_exists('mysqli_get_client_stats')) {
	$preq_mysqlnd = $preq_ok;
}

// Check if we have access to write to location
if (is_writable(__DIR__)) {
	$preq_writedir = $preq_ok;
}

?>
<div>
	<div class="card">
		<div class="card-header text-center">
			<?php echo _("Prerequisite"); ?>
		</div>
		<div class="card-body">
			<span class="card-title"><?php echo _("If any of the following prerequisites are shown as failed (red X), please correct the issue and reload the page before proceeding with the installation."); ?></span>
			<p class="card-text">
			<div class="container">
				<div class="row">
					<div class="col text-center"><?php echo $php_version_req; ?></div>
					<div class="col text-center"><a class="btn btn-<?php echo $preq_phpver[1]; ?>"><i class="fas fa-<?php echo $preq_phpver[0]; ?>"></i></a></div>
				</div>
				<div class="row mt-1">
					<div class="col text-center"><?php echo _('PHP mysqlnd library installed'); ?></div>
					<div class="col text-center"><a class="btn btn-<?php echo $preq_mysqlnd[1]; ?>"><i class="fas fa-<?php echo $preq_mysqlnd[0]; ?>"></i></a></div>
				</div>
				<div class="row mt-1">
					<div class="col text-center"><?php echo _('Write access to web directory'); ?></div>
					<div class="col text-center"><a class="btn btn-<?php echo $preq_writedir[1]; ?>"><i class="fas fa-<?php echo $preq_writedir[0]; ?>"></i></a></div>
				</div>
			</div>
			</p>
		</div>
	</div>

	<div class="settings">
		<h1 class="text-center"><?php echo _("Installation"); ?></h1>
		<?php
		if (!empty($message)) {
		?>
			<p class="alert alert-danger"><?php echo $message; ?></p>
		<?php
		}
		?>
		<span><?php echo _("We will ask you some basic questions about your website. Most of the settings can be later edited in the config.php file."); ?></span>

		<form method="post" action=".">
			<div class="card">
				<div class="card-header text-center">
					<?php echo _("Website details"); ?>
				</div>
				<div class="card-body">
					<span class="card-title"><?php echo _("We need a name for your status page (shown behind page title after the dash) and a url of your server status installation (i.e. <a href='#'>https://example.com/status</a> - without the trailing slash), so we can mail users link for forgotten password etc..."); ?></span>
					<p class="card-text">
					<div class="row">
						<div class="col form-floating">
							<input type="text" name="servername" value="<?php echo ((isset($_POST['servername'])) ? htmlspecialchars($_POST['servername'], ENT_QUOTES) : ''); ?>" id="servername" placeholder="<?php echo _("Servername"); ?>" class="form-control" required>
							<label for="servername"><?php echo _("Servername"); ?>: </label>
						</div>
						<div class="col form-floating">
							<input type="url" name="url" value="<?php echo ((isset($_POST['url'])) ? htmlspecialchars($_POST['url'], ENT_QUOTES) : ''); ?>" id="url" placeholder="<?php echo _("Url"); ?>" class="form-control" required>
							<label for="url"><?php echo _("Url"); ?>: </label>
						</div>
					</div>
					<div class="row mt-3">
						<div class="col form-floating">
							<input type="text" name="mailer" value="<?php echo ((isset($_POST['mailer'])) ? htmlspecialchars($_POST['mailer'], ENT_QUOTES) : ''); ?>" id="mailer" placeholder="<?php echo _("Mail-Name"); ?>" class="form-control" required>
							<label for="mailer"><?php echo _("Mail-Name"); ?>: </label>
						</div>
						<div class="col form-floating">
							<input type="email" name="mailer_email" value="<?php echo ((isset($_POST['mailer_email'])) ? htmlspecialchars($_POST['mailer_email'], ENT_QUOTES) : ''); ?>" id="mailer_email" placeholder="<?php echo _("Email"); ?>" class="form-control" required>
							<label for="mailer_email"><?php echo _("Email"); ?>: </label>
						</div>
					</div>
					</p>
				</div>
			</div>

			<div class="card">
				<div class="card-header text-center">
					<?php echo _("Database connection"); ?>
				</div>
				<div class="card-body">
					<span class="card-title"><?php echo _("We need database connection to be able to create tables. Please check that your account has the permission needed to do that."); ?></span>
					<p class="card-text">
					<div class="row">
						<div class="col form-floating">
							<input type="text" name="server" value="<?php echo ((isset($_POST['server'])) ? htmlspecialchars($_POST['server'], ENT_QUOTES) : ''); ?>" id="server" placeholder="<?php echo _("Server"); ?>" class="form-control" required>
							<label for="server"><?php echo _("Server"); ?>: </label>
						</div>
						<div class="col form-floating">
							<input type="text" name="database" value="<?php echo ((isset($_POST['database'])) ? htmlspecialchars($_POST['database'], ENT_QUOTES) : ''); ?>" id="database" placeholder="<?php echo _("Database"); ?>" class="form-control" required>
							<label for="database"><?php echo _("Database"); ?>: </label>
						</div>
					</div>
					<div class="row mt-3">
						<div class="col form-floating">
							<input type="text" name="dbuser" value="<?php echo ((isset($_POST['dbuser'])) ? htmlspecialchars($_POST['dbuser'], ENT_QUOTES) : ''); ?>" id="dbuser" placeholder="<?php echo _("User"); ?>" class="form-control" required>
							<label for="dbuser"><?php echo _("User"); ?>: </label>
						</div>
						<div class="col form-floating">
							<input type="password" name="dbpassword" value="<?php echo ((isset($_POST['dbpassword'])) ? htmlspecialchars($_POST['dbpassword'], ENT_QUOTES) : ''); ?>" id="dbpassword" placeholder="<?php echo _("Password"); ?>" class="form-control" required>
							<label for="dbpassword"><?php echo _("Password"); ?>: </label>
						</div>
					</div>
					</p>
				</div>
			</div>

			<div class="card">
				<div class="card-header text-center">
					<?php echo _("Privacy Policy"); ?>
				</div>
				<div class="card-body">
					<span class="card-title"><?php echo _("Since you are collecting personal information, the GDPR needs you to have a privacy policy. Enter the details below."); ?></span>
					<p class="card-text">
					<div class="row">
						<div class="col form-floating">
							<input type="text" name="policy_name" value="<?php echo ((isset($_POST['policy_name'])) ? htmlspecialchars($_POST['policy_name'], ENT_QUOTES) : ''); ?>" id="policy_name" placeholder="<?php echo _("Company name"); ?>" class="form-control" required>
							<label for="policy_name"><?php echo _("Name"); ?>: </label>
						</div>
						<div class="col form-floating">
							<input type="text" name="address" value="<?php echo ((isset($_POST['address'])) ? htmlspecialchars($_POST['address'], ENT_QUOTES) : ''); ?>" id="address" placeholder="<?php echo _("Full address"); ?>" class="form-control" required>
							<label for="address"><?php echo _("Address"); ?>: </label>
						</div>
					</div>
					<div class="row mt-3">
						<div class="col form-floating">
							<input type="text" name="policy_mail" value="<?php echo ((isset($_POST['policy_mail'])) ? htmlspecialchars($_POST['policy_mail'], ENT_QUOTES) : ''); ?>" id="policy_mail" placeholder="<?php echo _("E-Mail"); ?>" class="form-control" required>
							<label for="policy_mail"><?php echo _("E-Mail"); ?>: </label>
						</div>
						<div class="col form-floating">
							<input type="text" name="policy_phone" value="<?php echo ((isset($_POST['policy_phone'])) ? htmlspecialchars($_POST['policy_phone'], ENT_QUOTES) : ''); ?>" id="policy_phone" placeholder="<?php echo _("Phone number"); ?>" class="form-control">
							<label for="policy_phone"><?php echo _("Phone"); ?>: </label>
						</div>
					</div>
					<div class="row mt-3">
						<div class="col form-floating">
							<textarea class="form-control" id="who_we_are" rows="3" name="who_we_are" placeholder="<?php echo _("Some info about yourself"); ?>" value="<?php echo ((isset($_POST['who_we_are'])) ? htmlspecialchars($_POST['who_we_are'], ENT_QUOTES) : ''); ?>"></textarea>
							<label for="who_we_are"><?php echo _("Who we are"); ?>: </label>
						</div>
					</div>
					<div class="row mt-3">
						<span><?php echo _("If you alredy have an existing Policy published, please provide the full Url to override the local policy definition. Leave blank to use the local definition"); ?></span>
						<div class="col form-floating">
							<input type="imprint_url" name="imprint_url" value="<?php echo ((isset($_POST['imprint_url'])) ? htmlspecialchars($_POST['imprint_url'], ENT_QUOTES) : ''); ?>" id="imprint_url" placeholder="<?php echo _("External Imprint Url"); ?>" class="form-control">
							<label for="url"><?php echo _("External Imprint Url"); ?>: </label>
						</div>
					</div>
					<div class="row mt-3">
						<div class="col form-floating">
							<input type="policy_url" name="policy_url" value="<?php echo ((isset($_POST['policy_url'])) ? htmlspecialchars($_POST['policy_url'], ENT_QUOTES) : ''); ?>" id="policy_url" placeholder="<?php echo _("External Policy Url"); ?>" class="form-control">
							<label for="url"><?php echo _("External Policy Url"); ?>: </label>
						</div>
					</div>
				</div>
			</div>

			<div class="card">
				<div class="card-header text-center">
					<?php echo _("User"); ?>
				</div>
				<div class="card-body">
					<span class="card-title"><?php echo _("And finally, we need info to create a new user. You don't have to provide it, but then... No status page admin..."); ?></span>
					<p class="card-text">
					<div class="row">
						<div class="col form-floating">
							<input type="text" maxlength="50" name="name" value="<?php echo ((isset($_POST['name'])) ? htmlspecialchars($_POST['name'], ENT_QUOTES) : ''); ?>" id="name" placeholder="<?php echo _("Name"); ?>" class="form-control" required>
							<label for="name"><?php echo _("Name"); ?>: </label>
						</div>
						<div class="col form-floating">
							<input type="text" maxlength="50" name="surname" value="<?php echo ((isset($_POST['surname'])) ? htmlspecialchars($_POST['surname'], ENT_QUOTES) : ''); ?>" id="surname" placeholder="<?php echo _("Surname"); ?>" class="form-control" required>
							<label for="surname"><?php echo _("Surname"); ?>: </label>
						</div>
					</div>
					<div class="row mt-3">
						<div class="col form-floating">
							<input type="text" maxlength="50" name="username" value="<?php echo ((isset($_POST['username'])) ? htmlspecialchars($_POST['username'], ENT_QUOTES) : ''); ?>" id="username" placeholder="<?php echo _("Username"); ?>" class="form-control" required>
							<label for="username"><?php echo _("Username"); ?>:</label>
						</div>
						<div class="col form-floating">
							<input type="email" maxlength="60" name="email" value="<?php echo ((isset($_POST['email'])) ? htmlspecialchars($_POST['email'], ENT_QUOTES) : ''); ?>" id="email" placeholder="<?php echo _("Email"); ?>" class="form-control" required><input type="hidden" name="permission" value="0">
							<label for="email"><?php echo _("Email"); ?>:</label>
						</div>
					</div>
					<div class="row mt-3">
						<div class="col form-floating">
							<input type="password" name="password" value="<?php echo ((isset($_POST['password'])) ? htmlspecialchars($_POST['password'], ENT_QUOTES) : ''); ?>" id="password" placeholder="<?php echo _("Password"); ?>" class="form-control" required>
							<label for="password"><?php echo _("Password"); ?>:</label>
						</div>
					</div>
					</p>
				</div>
			</div>

			<div class="card" style="border: none;">
				<input type="hidden" value="0" name="permission">
				<button type="submit" class="btn btn-success float-end"><?php echo _("Run install!"); ?></button>
			</div>
		</form>
	</div>
</div>
<?php
Template::render_footer();
