<?php 

if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);

    if (count($lang_parse[1])) {
        $langs = array_combine($lang_parse[1], $lang_parse[4]);
    	
        foreach ($langs as $lang => $val) {
            if ($val === '') $langs[$lang] = 1;
        }

        arsort($langs, SORT_NUMERIC);
    }
}

$langs = array_flip($langs);

$accepted_langs = glob(__DIR__ . '/locale/*' , GLOB_ONLYDIR);

foreach ($accepted_langs as $key => $value) {
	$accepted_langs[$key] = basename($value);
}

$best_match = false;

foreach ($langs as $lang) {
	if (strlen($lang)>2){
		if (in_array($lang, $accepted_langs)){
			$best_match = $lang;
			break;
		}
	}else{
		$possible = array_filter($accepted_langs, function($key) {
			global $lang;
		    return strpos($key, $lang) === 0;
		});

		if (count($possible)){
			$best_match = $possible[0];
			break;
		}
	}
}

if ($best_match === false){
	$best_match = DEFAULT_LANGUAGE;
}

setlocale(LC_ALL, $best_match.".UTF-8");

bindtextdomain("server-status", __DIR__ . "/locale/");
bind_textdomain_codeset($best_match, "utf-8"); 
textdomain("server-status");