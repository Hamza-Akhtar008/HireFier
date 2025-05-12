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

trait SalaryFilter
{
	protected function applySalaryFilter(): void
	{
		// The 'salary_min' or 'salary_max' are not calculated columns, so WHERE clause is recommended (the HAVING clause is not required)
		if (!isset($this->posts)) {
			return;
		}
		
		$this->applyMinSalaryFilter();
		$this->applyMaxSalaryFilter();
	}
	
	private function applyMinSalaryFilter(): void
	{
		// The 'salary_min' is not calculated columns, so WHERE clause is recommended (the HAVING clause is not required)
		if (!isset($this->posts)) {
			return;
		}
		
		$minSalary = data_get($this->input, 'minSalary');
		$minSalary = (!empty($minSalary) && is_array($minSalary)) ? $minSalary : [];
		
		if (empty($minSalary)) {
			return;
		}
		
		$minSalaryMin = null;
		if (array_key_exists('min', $minSalary) && is_numeric($minSalary['min'])) {
			$minSalaryMin = $minSalary['min'];
		}
		
		$minSalaryMax = null;
		if (array_key_exists('max', $minSalary) && is_numeric($minSalary['max'])) {
			$minSalaryMax = $minSalary['max'];
		}
		
		$minSalaryMin = (is_numeric($minSalaryMin)) ? $minSalaryMin : null;
		$minSalaryMax = (is_numeric($minSalaryMax)) ? $minSalaryMax : null;
		
		if (!is_null($minSalaryMin) && !is_null($minSalaryMax)) {
			if ($minSalaryMax > $minSalaryMin) {
				$where = '(salary_min >= ? AND salary_min <= ?)';
				$this->posts->whereRaw($where, [$minSalaryMin, $minSalaryMax]);
			}
		} else {
			if (!is_null($minSalaryMin)) {
				$this->posts->whereRaw('salary_min >= ?', [$minSalaryMin]);
			}
			if (!is_null($minSalaryMax)) {
				$this->posts->whereRaw('salary_min <= ?', [$minSalaryMax]);
			}
		}
	}
	
	private function applyMaxSalaryFilter(): void
	{
		// The 'salary_max' is not calculated column, so WHERE clause is recommended (the HAVING clause is not required)
		if (!isset($this->posts)) {
			return;
		}
		
		$maxSalary = data_get($this->input, 'maxSalary');
		$maxSalary = (!empty($maxSalary) && is_array($maxSalary)) ? $maxSalary : [];
		
		if (empty($maxSalary)) {
			return;
		}
		
		$maxSalaryMin = null;
		if (array_key_exists('min', $maxSalary) && is_numeric($maxSalary['min'])) {
			$maxSalaryMin = $maxSalary['min'];
		}
		
		$maxSalaryMax = null;
		if (array_key_exists('max', $maxSalary) && is_numeric($maxSalary['max'])) {
			$maxSalaryMax = $maxSalary['max'];
		}
		
		$maxSalaryMin = (is_numeric($maxSalaryMin)) ? $maxSalaryMin : null;
		$maxSalaryMax = (is_numeric($maxSalaryMax)) ? $maxSalaryMax : null;
		
		if (!is_null($maxSalaryMin) && !is_null($maxSalaryMax)) {
			if ($maxSalaryMax > $maxSalaryMin) {
				$where = '(salary_max >= ? AND salary_max <= ?)';
				$this->posts->whereRaw($where, [$maxSalaryMin, $maxSalaryMax]);
			}
		} else {
			if (!is_null($maxSalaryMin)) {
				$this->posts->whereRaw('salary_max >= ?', [$maxSalaryMin]);
			}
			if (!is_null($maxSalaryMax)) {
				$this->posts->whereRaw('salary_max <= ?', [$maxSalaryMax]);
			}
		}
	}
}
