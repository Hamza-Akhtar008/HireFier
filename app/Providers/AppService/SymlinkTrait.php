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

namespace App\Providers\AppService;

use Exception;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Throwable;

trait SymlinkTrait
{
	/**
	 * Setup Storage Symlink
	 * Check the local storage symbolic link and Create it if it does not exist.
	 *
	 * @return void
	 */
	private function setupStorageSymlink(): void
	{
		$symlinkPath = public_path('storage');
		
		// Check if $symlinkPath exists and check if it is a real directory and not a symlink,
		// Then, remove the directory to allow the symlink's creation without file conflict.
		try {
			if (isRealDirectory($symlinkPath)) {
				File::deleteDirectory($symlinkPath);
			}
		} catch (Throwable $e) {
			$defaultMessage = 'Error occurred while deleting the "' . $symlinkPath . '" folder which should be a symbolic link. ';
			$defaultMessage .= 'Please make sure the apache user has permissions to perform this action.';
			
			$message = $e->getMessage();
			$message = !empty($message) ? $message : $defaultMessage;
			flash($message)->error();
			
			return;
		}
		
		// Create the symlink
		try {
			if (!is_link($symlinkPath)) {
				// Symbolic links on Windows are created by symlink() which accept only absolute paths.
				// Relative paths on Windows are not supported for symlinks: http://php.net/manual/en/function.symlink.php
				if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
					Artisan::call('storage:link');
				} else {
					if (function_exists('symlink')) {
    symlink('../storage/app/public', './storage');
} else {
    Artisan::call('storage:link');
}
				}
			}
		} catch (Exception $e) {
			$message = $this->getSymlinkErrorMessage($e->getMessage());
			flash($message)->error();
		}
	}
	
	/**
	 * @param string|null $message
	 * @return string
	 */
	private function getSymlinkErrorMessage(?string $message): string
	{
		$message = !empty($message) ? $message : 'Error with the PHP symlink() function';
		
		$docSymlink = 'https://support.laraclassifier.com/hc/articles/71/images-dont-appear-in-my-website';
		$docDirExists = 'https://support.laraclassifier.com/hc/articles/18/10/80/symlink-file-exists-or-no-such-file-or-directory';
		
		if ($this->isRealDirErrorMessage($message)) {
			$docSymlink = $docDirExists;
		}
		
		$message .= ' - Please <a href="' . $docSymlink . '" target="_blank">see this article</a> for more information.';
		
		return $message;
	}
	
	/**
	 * @param string|null $message
	 * @return bool
	 */
	private function isRealDirErrorMessage(?string $message): bool
	{
		$message = getAsString($message);
		
		return (str_contains($message, 'File exists') || str_contains($message, 'No such file or directory'));
	}
}
