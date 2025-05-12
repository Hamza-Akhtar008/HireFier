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

use App\Models\SalaryType;
use Exception;

class SalaryTypeObserver
{
	/**
	 * Listen to the Entry saved event.
	 *
	 * @param SalaryType $salaryType
	 * @return void
	 */
	public function saved(SalaryType $salaryType)
	{
		// Removing Entries from the Cache
		$this->clearCache($salaryType);
	}
	
	/**
	 * Listen to the Entry deleted event.
	 *
	 * @param SalaryType $salaryType
	 * @return void
	 */
	public function deleted(SalaryType $salaryType)
	{
		// Removing Entries from the Cache
		$this->clearCache($salaryType);
	}
	
	/**
	 * Removing the Entity's Entries from the Cache
	 *
	 * @param $salaryType
	 * @return void
	 */
	private function clearCache($salaryType): void
	{
		try {
			cache()->flush();
		} catch (Exception $e) {
		}
	}
}
