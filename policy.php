<?php
	require_once("template.php");
	require_once("config.php");
	Template::render_header("Privacy Policy");

	echo "<h1>" . _("Contact & Privacy Policy") . "</h2>";
	echo "<h2>" . _("Contact") . "</h2>";
	echo "##name## <br>";
	echo "##email## <br>";
	Template::render_footer();
