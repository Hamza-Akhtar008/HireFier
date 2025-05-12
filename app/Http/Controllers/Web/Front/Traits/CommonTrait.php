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

namespace App\Http\Controllers\Web\Front\Traits;

use App\Helpers\Common\DotenvEditor;
use App\Helpers\Common\Files\Storage\StorageDisk;

trait CommonTrait
{
	public $disk;
	public $pDisk;
	
	/**
	 * Set the storage disk
	 */
	private function setStorageDisk(): void
	{
		// Get the storage disk
		$this->disk = StorageDisk::getDisk();
		view()->share('disk', $this->disk);
		
		// Get the storage disk for Resumes
		$this->pDisk = StorageDisk::getDisk('private');
		view()->share('pDisk', $this->pDisk);
	}
	
	/**
	 * Check & update the App Key (If needed, for security reasons)
	 *
	 * @return void
	 */
	private function checkAndGenerateAppKey(): void
	{
		$isUnsecureAppKey = (DotenvEditor::getValue('APP_KEY') == 'SomeRandomStringWith32Characters');
		
		// Generate a new App Key
		if ($isUnsecureAppKey) {
			updateAppKeyWithArtisan();
		}
	}
}
