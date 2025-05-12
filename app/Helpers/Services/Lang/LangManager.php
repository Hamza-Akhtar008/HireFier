<?php
/*
 * JobClass - Job Board Web Application
 * Copyright (c) BeDigit. All Rights Reserved
 *
 * Website: https://laraclassifier.com/jobclass
 * Author: Mayeul Akpovi (BeDigit - https://bedigit.com)
 *
 * LICENSE
 * -------
 * This software is provided under a license agreement and may only be used or copied
 * in accordance with its terms, including the inclusion of the above copyright notice.
 * As this software is sold exclusively on CodeCanyon,
 * please review the full license details here: https://codecanyon.net/licenses/standard
 */

namespace App\Helpers\Services\Lang;

use App\Helpers\Services\Lang\Traits\LangFilesTrait;
use App\Helpers\Services\Lang\Traits\LangLinesTrait;

class LangManager
{
	use LangFilesTrait, LangLinesTrait;
	
	/**
	 * The path to the language files.
	 *
	 * @var string
	 */
	protected string $path;
	
	/**
	 * The master language code
	 *
	 * @var string
	 */
	protected string $masterLangCode = 'en';
	
	/**
	 * Included languages files
	 *
	 * @var array
	 */
	protected array $includedLanguagesFiles = [
		'en', // English (en_US)
		'fr', // French (fr_FR) - Français
		'es', // Spanish (es_ES) - Español
		'ar', // Arabic (ar_SA) - ‫العربية
		'de', // German (de_DE) - Deutsch
		'it', // Italian (it_IT) - Italiano
		'ru', // Russian (ru_RU) - Русский
		'nl', // Dutch (nl_NL) - Nederlands
		'nb', // Norwegian Bokmål (nb_NO) - Norsk Bokmål
		'uk', // Ukrainian (uk_UA) - українська
		'pl', // Polish (pl_PL) - Polski
		'ro', // Romanian (ro_RO) - Română
		'el', // Greek (el_GR) - ελληνικά
		'pt', // Portuguese (pt_PT) - Português
		'da', // Danish (da_DK) - Dansk
		'sv', // Swedish (sv_SE) - Svenska
		'fi', // Finnish (fi_FI) - Suomi
		'hu', // Hungarian (hu_HU) - Magyar
		'sr', // Serbian (sr_RS) - српски
		'cs', // Czech (cs_CZ) - čeština
		'bg', // Bulgarian (bg_BG) - български
		'hr', // Croatian (hr_HR) - Hrvatski
		'et', // Estonian (et_EE) - Eesti
		'lt', // Lithuanian (lt_LT) - Lietuvių
		'lv', // Latvian (lv_LV) - Latviešu (Latviski)
		'sk', // Slovak (sk_SK) - Slovenský
		'sl', // Slovenian (sl_SI) - Slovenski
		'is', // Icelandic (is_IS) - íslenska (íslenskur)
		'sq', // Albanian (sq_AL) - Shqip
	
	];
	
	/**
	 * LangManager constructor.
	 */
	public function __construct()
	{
		$this->path = base_path('lang/');
	}
	
	/**
	 * Get all codes of the included languages
	 *
	 * @return array
	 */
	public function getIncludedLanguages(): array
	{
		return $this->includedLanguagesFiles;
	}
	
	/**
	 * Get all the codes of included and existing languages
	 *
	 * @return array
	 */
	public function getTranslatedLanguages(): array
	{
		$languages = [];
		
		if (!empty($this->includedLanguagesFiles)) {
			foreach ($this->includedLanguagesFiles as $code) {
				$path = $this->path . $code;
				if (file_exists($path) && is_dir($path)) {
					$languages[] = $code;
				}
			}
		}
		
		return $languages;
	}
}
