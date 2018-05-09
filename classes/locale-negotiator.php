<?php
/**
* This class is used to negotiate language displayed to user.
* Reads browser preferences and chooses the best language from list
*/
class LocaleNegotiator
{	
	private $accepted_langs = [];
	private $default_language;
	private $all_locales = array(
		'af_ZA' => 'Afrikaans',
		'am_ET' => 'አማርኛ',
		'ar_AE' => 'العربية',
		'ar_BH' => 'العربية',
		'ar_DZ' => 'العربية',
		'ar_EG' => 'العربية',
		'ar_IQ' => 'العربية',
		'ar_JO' => 'العربية',
		'ar_KW' => 'العربية',
		'ar_LB' => 'العربية',
		'ar_LY' => 'العربية',
		'ar_MA' => 'العربية',
		'arn_CL' => 'Mapuche',
		'ar_OM' => 'العربية',
		'ar_QA' => 'العربية',
		'ar_SA' => 'العربية',
		'ar_SY' => 'العربية',
		'ar_TN' => 'العربية',
		'ar_YE' => 'العربية',
		'as_IN' => 'অসমীয়া',
		'az_Cyrl_AZ' => 'Азәрбајҹан',
		'az_Latn_AZ' => 'Azərbaycan',
		'ba_RU' => 'Bashkir',
		'be_BY' => 'Беларуская',
		'bg_BG' => 'Български',
		'bn_BD' => 'বাংলা',
		'bn_IN' => 'বাংলা',
		'bo_CN' => 'བོད་སྐད་',
		'br_FR' => 'Brezhoneg',
		'bs_Cyrl_BA' => 'Босански',
		'bs_Latn_BA' => 'Bosanski',
		'ca_ES' => 'Català',
		'co_FR' => 'Corsican',
		'cs_CZ' => 'Čeština',
		'cy_GB' => 'Cymraeg',
		'da_DK' => 'Dansk',
		'de_AT' => 'Deutsch',
		'de_CH' => 'Deutsch',
		'de_DE' => 'Deutsch',
		'de_LI' => 'Deutsch',
		'de_LU' => 'Deutsch',
		'dsb_DE' => 'Dolnoserbšćina',
		'dv_MV' => 'Divehi',
		'el_GR' => 'Ελληνικά',
		'en_029' => 'English',
		'en_AU' => 'English',
		'en_BZ' => 'English',
		'en_CA' => 'English',
		'en_GB' => 'English',
		'en_IE' => 'English',
		'en_IN' => 'English',
		'en_JM' => 'English',
		'en_MY' => 'English',
		'en_NZ' => 'English',
		'en_PH' => 'English',
		'en_SG' => 'English',
		'en_TT' => 'English',
		'en_US' => 'English',
		'en_ZA' => 'English',
		'en_ZW' => 'English',
		'es_AR' => 'Español',
		'es_BO' => 'Español',
		'es_CL' => 'Español',
		'es_CO' => 'Español',
		'es_CR' => 'Español',
		'es_DO' => 'Español',
		'es_EC' => 'Español',
		'es_ES' => 'Español',
		'es_GT' => 'Español',
		'es_HN' => 'Español',
		'es_MX' => 'Español',
		'es_NI' => 'Español',
		'es_PA' => 'Español',
		'es_PE' => 'Español',
		'es_PR' => 'Español',
		'es_PY' => 'Español',
		'es_SV' => 'Español',
		'es_US' => 'Español',
		'es_UY' => 'Español',
		'es_VE' => 'Español',
		'et_EE' => 'Eesti',
		'eu_ES' => 'Euskara',
		'fa_IR' => 'فارسی',
		'fi_FI' => 'Suomi',
		'fil_PH' => 'Filipino',
		'fo_FO' => 'Føroyskt',
		'fr_BE' => 'Français',
		'fr_CA' => 'Français',
		'fr_CH' => 'Français',
		'fr_FR' => 'Français',
		'fr_LU' => 'Français',
		'fr_MC' => 'Français',
		'fy_NL' => 'West_frysk',
		'ga_IE' => 'Gaeilge',
		'gd_GB' => 'Gàidhlig',
		'gl_ES' => 'Galego',
		'gsw_FR' => 'Schwiizertüütsch',
		'gu_IN' => 'ગુજરાતી',
		'ha_Latn_NG' => 'Hausa',
		'he_IL' => 'עברית',
		'hi_IN' => 'हिन्दी',
		'hr_BA' => 'Hrvatski',
		'hr_HR' => 'Hrvatski',
		'hsb_DE' => 'Hornjoserbšćina',
		'hu_HU' => 'Magyar',
		'hy_AM' => 'Հայերեն',
		'id_ID' => 'Bahasa indonesia',
		'ig_NG' => 'Igbo',
		'ii_CN' => 'ꆈꌠꉙ',
		'is_IS' => 'Íslenska',
		'it_CH' => 'Italiano',
		'it_IT' => 'Italiano',
		'iu_Cans_CA' => 'Inuktitut',
		'iu_Latn_CA' => 'Inuktitut',
		'ja_JP' => '日本語',
		'ka_GE' => 'ქართული',
		'kk_KZ' => 'Қазақ тілі',
		'kl_GL' => 'Kalaallisut',
		'km_KH' => 'ខ្មែរ',
		'kn_IN' => 'ಕನ್ನಡ',
		'kok_IN' => 'कोंकणी',
		'ko_KR' => '한국어',
		'ky_KG' => 'Кыргызча',
		'lb_LU' => 'Lëtzebuergesch',
		'lo_LA' => 'ລາວ',
		'lt_LT' => 'Lietuvių',
		'lv_LV' => 'Latviešu',
		'mi_NZ' => 'Maori',
		'mk_MK' => 'Македонски',
		'ml_IN' => 'മലയാളം',
		'mn_MN' => 'Монгол',
		'mn_Mong_CN' => 'Монгол',
		'moh_CA' => 'Mohawk',
		'mr_IN' => 'मराठी',
		'ms_BN' => 'Bahasa melayu',
		'ms_MY' => 'Bahasa melayu',
		'mt_MT' => 'Malti',
		'nb_NO' => 'Norsk bokmål',
		'ne_NP' => 'नेपाली',
		'nl_BE' => 'Nederlands',
		'nl_NL' => 'Nederlands',
		'nn_NO' => 'Nynorsk',
		'nso_ZA' => 'Northern sotho',
		'oc_FR' => 'Occitan',
		'or_IN' => 'ଓଡ଼ିଆ',
		'pa_IN' => 'ਪੰਜਾਬੀ',
		'pl_PL' => 'Polski',
		'prs_AF' => 'Prs',
		'ps_AF' => 'پښتو',
		'pt_BR' => 'Português',
		'pt_PT' => 'Português',
		'qut_GT' => 'Qut',
		'quz_BO' => 'Quz',
		'quz_EC' => 'Quz',
		'quz_PE' => 'Quz',
		'rm_CH' => 'Rumantsch',
		'ro_RO' => 'Română',
		'ru_RU' => 'Русский',
		'rw_RW' => 'Kinyarwanda',
		'sah_RU' => 'Саха тыла',
		'sa_IN' => 'Sanskrit',
		'se_FI' => 'Davvisámegiella',
		'se_NO' => 'Davvisámegiella',
		'se_SE' => 'Davvisámegiella',
		'si_LK' => 'සිංහල',
		'sk_SK' => 'Slovenčina',
		'sl_SI' => 'Slovenščina',
		'sma_NO' => 'Southern sami',
		'sma_SE' => 'Southern sami',
		'smj_NO' => 'Lule sami',
		'smj_SE' => 'Lule sami',
		'smn_FI' => 'Anarâškielâ',
		'sms_FI' => 'Skolt sami',
		'sq_AL' => 'Shqip',
		'sr_Cyrl_BA' => 'Српски',
		'sr_Cyrl_CS' => 'Српски',
		'sr_Cyrl_ME' => 'Српски',
		'sr_Cyrl_RS' => 'Српски',
		'sr_Latn_BA' => 'Srpski',
		'sr_Latn_CS' => 'Srpski',
		'sr_Latn_ME' => 'Srpski',
		'sr_Latn_RS' => 'Srpski',
		'sr_RS' => 'Srpski',
		'sv_FI' => 'Svenska',
		'sv_SE' => 'Svenska',
		'sw_KE' => 'Kiswahili',
		'syr_SY' => 'Syriac',
		'ta_IN' => 'தமிழ்',
		'te_IN' => 'తెలుగు',
		'tg_Cyrl_TJ' => 'Tajik',
		'th_TH' => 'ไทย',
		'tk_TM' => 'Turkmen',
		'tn_ZA' => 'Tswana',
		'tr_TR' => 'Türkçe',
		'tt_RU' => 'Tatar',
		'tzm_Latn_DZ' => 'Tamaziɣt',
		'ug_CN' => 'ئۇيغۇرچە',
		'uk_UA' => 'Українська',
		'ur_PK' => 'اردو',
		'uz_Cyrl_UZ' => 'Ўзбек',
		'uz_Latn_UZ' => 'Oʻzbekcha',
		'vi_VN' => 'Tiếng việt',
		'wo_SN' => 'Wolof',
		'xh_ZA' => 'Xhosa',
		'yo_NG' => 'Èdè yorùbá',
		'zh_CN' => '中文',
		'zh_HK' => '中文',
		'zh_MO' => '中文',
		'zh_SG' => '中文',
		'zh_TW' => '中文',
		'zu_ZA' => 'Isizulu',
		);

	/**
	* This method scans for languages and creates a list of language and its name (localized ofc.)
	* @param String $default_language language displayed to user in case no suitable lang is found
	*/
	function __construct($default_language)
	{
		$tmp = glob(__DIR__ . '/../locale/*' , GLOB_ONLYDIR);
		$this->default_language = $default_language;
		//Works only if the server supports the locale
		//This basically means $accepted_langs[<lang_code>] = "<lang name>";
		foreach ($tmp as $value) {
			$lang = basename($value);
			$this->accepted_langs[$lang] = $this->all_locales[$lang];
		}
	}

	/**
	* Returns list of accepted langs so it can be reused for rendering language list for switching...
	*/
	public function get_accepted_langs(){
		return $this->accepted_langs;
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
			$http_accept_language = str_replace("-", "_", $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		    preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $http_accept_language, $lang_parse);

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

		global $lang;
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
					foreach ($possible as $value) {
						$best_match = $value;
					}
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

