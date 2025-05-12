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

namespace App\Helpers\Services\Search\Traits\Filters;

trait CompanyFilter
{
	protected function applyCompanyFilter(): void
	{
		if (!isset($this->posts)) {
			return;
		}
		
		$companyId = data_get($this->input, 'companyId');
		$companyId = (is_numeric($companyId) || is_string($companyId)) ? $companyId : null;
		
		if (empty($companyId)) {
			return;
		}
		
		$this->posts->where('company_id', $companyId);
	}
}
