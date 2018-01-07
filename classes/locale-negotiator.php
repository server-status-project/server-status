<?php
/**
* This class is used to negotiate language displayed to user.
* Reads browser preferences and chooses the best language from list
*/
class LocaleNegotiator
{	
	private $accepted_langs = [];
	private $default_language;

	/**
	* This method scans for languages and creates a list of language and its name (localized ofc.)
	* @param String $default_language language displayed to user in case no suitable lang is found
	*/
	function __construct($default_language)
	{
		$tmp = glob(__DIR__ . '/locale/*' , GLOB_ONLYDIR);
		$this->default_language = $default_language;
		//Works only if the server supports the locale
		//This basically means $accepted_langs[<lang_code>] = "<lang name>";
		foreach ($accepted_langs as $key => $value) {
			$this->accepted_langs[basename($value)] = self::mb_ucfirst(locale_get_display_language($lang, $lang));
		}
	}

	/**
	* Returns list of accepted langs so it can be reused for rendering language list for switching...
	*/
	public function get_accepted_langs(){
		return $this->accepted_langs;
	}

	/**
	* This methid does ucfirst() on multibyte encodings like UTF-8 - good for edge cases when locale starts with Č or similar.
	* @param String $string string
	* @return String string with first char uppercase
	*/
	private static function mb_ucfirst($string)
	{
	    return mb_strtoupper(mb_substr($string, 0, 1)).mb_strtolower(mb_substr($string, 1));
	}

	/**
	* This method does the actual negotiation. It has override parameter in case user wants to switch
	* languages. 
	* @param String $override adds language to list of preffered languages with highest priority
	* @return String language code that matched best with browser preferences
	*/
	public function negotiate($override = null){
		$langs = [];

		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		    preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);

		    if (count($lang_parse[1])) {
		        $langs = array_combine($lang_parse[1], $lang_parse[4]);
		    	
		        foreach ($langs as $lang => $val) {
		        	//If browser didn't send quality of language, it is 1 by default
		            if ($val === '') $langs[$lang] = 1;
		        }

		        if (isset($override))
		        {
		        	//More important than the best lang of browser
		        	$langs[$override] = 2;
		        }

		        arsort($langs, SORT_NUMERIC);
		    }
		}

		//So we have lang code as value
		$langs = array_flip($langs);
		//False unless we set it, so we know to set default locale
		$best_match = false;
		//So we have also lang code as value
		$accepted_langs = array_flip($this->accepted_langs);
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
			$best_match = $this->default_language;
		}

		return $best_match;
	}
}

