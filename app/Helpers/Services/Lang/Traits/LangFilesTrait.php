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

namespace App\Helpers\Services\Lang\Traits;

use Illuminate\Support\Facades\File;

trait LangFilesTrait
{
	/**
	 * Copy the master language folder to the new language folder
	 *
	 * @param string $defaultLangCode
	 * @param string $langCodeTo
	 */
	public function copyFiles(string $defaultLangCode, string $langCodeTo)
	{
		if ($this->masterLangExists()) {
			$defaultLangCode = $this->masterLangCode;
		}
		
		// Copy the language files (If the destination files don't exist)
		if (!File::exists($this->path . $langCodeTo)) {
			File::copyDirectory($this->path . $defaultLangCode, $this->path . $langCodeTo);
		}
		if (!File::exists($this->path . 'vendor/admin/' . $langCodeTo)) {
			File::copyDirectory($this->path . 'vendor/admin/' . $defaultLangCode, $this->path . 'vendor/admin/' . $langCodeTo);
		}
	}
	
	/**
	 * Remove the Language files
	 *
	 * @param string $langCode
	 * @return bool
	 */
	public function removeFiles(string $langCode): bool
	{
		// Don't remove the master Language files
		if ($langCode == $this->masterLangCode) {
			return false;
		}
		
		// Don't remove the included languages files
		if (in_array($langCode, $this->includedLanguagesFiles)) {
			return false;
		}
		
		// Remove the Language files
		File::deleteDirectory($this->path . $langCode);
		File::deleteDirectory($this->path . 'vendor/admin/' . $langCode);
		
		return true;
	}
	
	/**
	 * Check if the master language exists
	 *
	 * @return bool
	 */
	protected function masterLangExists(): bool
	{
		$masterFrontLangPath = $this->path . $this->masterLangCode;
		$masterBackendLangPath = $this->path . 'vendor/admin/' . $this->masterLangCode;
		if (File::exists($masterFrontLangPath) && File::exists($masterBackendLangPath)) {
			return true;
		}
		
		return false;
	}
}
