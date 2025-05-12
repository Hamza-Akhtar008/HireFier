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

namespace App\Http\Controllers\Web\Front\Post\Show\Traits;

trait UserResumes
{
	/**
	 * Get the logged user's resumes in view
	 *
	 * @return array
	 */
	protected function getLoggedUserResumes(): array
	{
		// Get the logged user's resume
		$queryParams = [
			'belongLoggedUser' => true,
			'forApplyingJob'   => true,
			'sort'             => 'created_at',
		];
		$data = getServiceData($this->resumeService->getEntries($queryParams));
		
		if (!data_get($data, 'success')) {
			return [];
		}
		
		$apiResult = data_get($data, 'result');
		
		return (array)data_get($apiResult, 'data');
	}
}
