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
use App\Models\ThreadMessage;

class ThreadMessageObserver
{
	/**
	 * Listen to the Entry deleting event.
	 *
	 * @param ThreadMessage $message
	 * @return void
	 */
	public function deleting(ThreadMessage $message)
	{
		if (!empty($message->file_path)) {
			// Storage Disk Init.
			$pDisk = StorageDisk::getDisk();
			if (str_starts_with($message->file_path, 'resumes/')) {
				$pDisk = StorageDisk::getDisk('private');
			}
			
			// Delete the message's file
			if ($pDisk->exists($message->file_path)) {
				$pDisk->delete($message->file_path);
			}
		}
	}
}
