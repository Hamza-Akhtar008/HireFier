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

namespace App\Observers;

use App\Helpers\Common\Files\Storage\StorageDisk;
use App\Models\Resume;
use Exception;

class ResumeObserver
{
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param Resume $resume
	 * @return void
	 */
	public function deleting(Resume $resume)
	{
		// Storage Disk Init.
		$pDisk = StorageDisk::getDisk('private');
		
		// Remove resume files (if exists)
		if (!empty($resume->file_path)) {
			$filePath = str_replace('uploads/', '', $resume->file_path);
			
			if ($pDisk->exists($filePath)) {
				$pDisk->delete($filePath);
			}
		}
	}
	
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param Resume $resume
	 * @return void
	 */
	public function saved(Resume $resume)
	{
		// Removing Entries from the Cache
		$this->clearCache($resume);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param Resume $resume
	 * @return void
	 */
	public function deleted(Resume $resume)
	{
		// Removing Entries from the Cache
		$this->clearCache($resume);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $resume
	 * @return void
	 */
	private function clearCache($resume): void
	{
		$limit = config('larapen.core.selectResumeInto', 5);
		
		try {
			cache()->forget('resumes.take.' . $limit . '.where.user.' . $resume->user_id);
			cache()->forget('resume.where.user.' . $resume->user_id);
		} catch (Exception $e) {
		}
	}
}
