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

trait PostTypeFilter
{
	protected function applyPostTypeFilter(): void
	{
		if (!isset($this->posts)) {
			return;
		}
		
		$postTypeIds = data_get($this->input, 'type', []);
		
		if (empty($postTypeIds)) {
			return;
		}
		
		if (is_array($postTypeIds)) {
			$this->posts->whereIn('post_type_id', $postTypeIds);
		}
		
		// Optional
		if (is_numeric($postTypeIds)) {
			$this->posts->where('post_type_id', $postTypeIds);
		}
	}
}
