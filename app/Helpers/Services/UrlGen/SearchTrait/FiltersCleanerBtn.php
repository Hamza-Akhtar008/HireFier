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

namespace App\Helpers\Services\UrlGen\SearchTrait;

trait FiltersCleanerBtn
{
	/**
	 * Generate button link for the category filter removal
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function getCategoryFilterClearLink($cat = null, $city = null): string
	{
		$out = '';
		
		if ($this->doesCategoryIsFiltered($cat)) {
			$url = $this->searchWithoutCategory($cat, $city);
			$out = getFilterClearBtn($url);
		}
		
		return $out;
	}
	
	/**
	 * Generate button link for the city filter removal
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function getCityFilterClearLink($cat = null, $city = null): string
	{
		$out = '';
		
		if ($this->doesCityIsFiltered($city)) {
			$url = $this->searchWithoutCity($cat, $city);
			$out = getFilterClearBtn($url);
		}
		
		return $out;
	}
	
	/**
	 * Generate button link for the date filter removal
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function getDateFilterClearLink($cat = null, $city = null): string
	{
		$out = '';
		
		if ($this->doesDateIsFiltered($cat, $city)) {
			$url = $this->searchWithoutDate($cat, $city);
			$out = getFilterClearBtn($url);
		}
		
		return $out;
	}
	
	/**
	 * Generate button link for the minSalary filter removal
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function getMinSalaryFilterClearLink($cat = null, $city = null): string
	{
		$out = '';
		
		if ($this->doesMinSalaryIsFiltered($cat, $city)) {
			$url = $this->searchWithoutMinSalary($cat, $city);
			$out = getFilterClearBtn($url);
		}
		
		return $out;
	}
	
	/**
	 * Generate button link for the maxSalary filter removal
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function getMaxSalaryFilterClearLink($cat = null, $city = null): string
	{
		$out = '';
		
		if ($this->doesMaxSalaryIsFiltered($cat, $city)) {
			$url = $this->searchWithoutMaxSalary($cat, $city);
			$out = getFilterClearBtn($url);
		}
		
		return $out;
	}
	
	/**
	 * Generate button link for the listing type filter removal
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function getTypeFilterClearLink($cat = null, $city = null): string
	{
		$out = '';
		
		if ($this->doesTypeIsFiltered($cat, $city)) {
			$url = $this->searchWithoutType($cat, $city);
			$out = getFilterClearBtn($url);
		}
		
		return $out;
	}
}
