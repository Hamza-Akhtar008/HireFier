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

use App\Helpers\Common\Arr;

trait FiltersCleaner
{
	use FiltersCleanerBtn;
	
	/**
	 * Remove category from filters
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function searchWithoutCategory($cat = null, $city = null): string
	{
		$url = request()->fullUrl();
		
		$cat = is_array($cat) ? Arr::toObject($cat) : $cat;
		$city = is_array($city) ? Arr::toObject($city) : $city;
		
		if ($this->doesCategoryIsFiltered($cat)) {
			$paramsToRemove = ['page', 'cf', 'minPrice', 'maxPrice'];
			if (!empty($cat)) {
				$paramsToRemove[] = 'sc';
				if (empty($cat->parent)) {
					$paramsToRemove[] = 'c';
				}
			}
			
			$url = urlQuery($this->search())->removeParameters($paramsToRemove)->toString();
			
			if (!empty($city)) {
				if (empty($cat)) {
					$url = $this->city($city);
				}
			} else {
				if (!empty($cat->parent)) {
					$url = $this->category($cat->parent, null, $city);
				}
			}
		}
		
		return $url;
	}
	
	/**
	 * Remove city from filters
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function searchWithoutCity($cat = null, $city = null): string
	{
		$url = request()->fullUrl();
		
		$cat = is_array($cat) ? Arr::toObject($cat) : $cat;
		$city = is_array($city) ? Arr::toObject($city) : $city;
		
		if ($this->doesCityIsFiltered($city)) {
			$paramsToRemove = ['l', 'r', 'location', 'distance', 'page'];
			$url = urlQuery($this->search())->removeParameters($paramsToRemove)->toString();
			
			if (!empty($cat)) {
				if (empty($city)) {
					$url = $this->category($cat);
				}
			}
		}
		
		return $url;
	}
	
	/**
	 * Remove date from filters
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function searchWithoutDate($cat = null, $city = null): string
	{
		$url = request()->fullUrl();
		
		$cat = is_array($cat) ? Arr::toObject($cat) : $cat;
		$city = is_array($city) ? Arr::toObject($city) : $city;
		
		if ($this->doesDateIsFiltered($cat, $city)) {
			$params = [];
			if (!empty($cat) && !empty($cat->id)) {
				$params['c'] = $cat->id;
				if (!empty($cat->parent)) {
					$params['c'] = $cat->parent->id;
					$params['sc'] = $cat->id;
				}
			}
			if (!empty($city) && !empty($city->id)) {
				$params['l'] = $city->id;
			}
			
			$paramsToRemove = ['page', 'postedDate'];
			$url = urlQuery($this->search())->removeParameters($paramsToRemove)->setParameters($params)->toString();
		}
		
		return $url;
	}
	
	/**
	 * Remove minSalary from filters
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function searchWithoutMinSalary($cat = null, $city = null): string
	{
		$url = request()->fullUrl();
		
		$cat = is_array($cat) ? Arr::toObject($cat) : $cat;
		$city = is_array($city) ? Arr::toObject($city) : $city;
		
		if ($this->doesMinSalaryIsFiltered($cat, $city)) {
			$params = [];
			if (!empty($cat) && !empty($cat->id)) {
				$params['c'] = $cat->id;
				if (!empty($cat->parent)) {
					$params['c'] = $cat->parent->id;
					$params['sc'] = $cat->id;
				}
			}
			if (!empty($city) && !empty($city->id)) {
				$params['l'] = $city->id;
			}
			
			$paramsToRemove = ['page', 'minSalary'];
			$url = urlQuery($this->search())->removeParameters($paramsToRemove)->setParameters($params)->toString();
		}
		
		return $url;
	}
	
	/**
	 * Remove maxSalary from filters
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function searchWithoutMaxSalary($cat = null, $city = null): string
	{
		$url = request()->fullUrl();
		
		$cat = is_array($cat) ? Arr::toObject($cat) : $cat;
		$city = is_array($city) ? Arr::toObject($city) : $city;
		
		if ($this->doesMaxSalaryIsFiltered($cat, $city)) {
			$params = [];
			if (!empty($cat) && !empty($cat->id)) {
				$params['c'] = $cat->id;
				if (!empty($cat->parent)) {
					$params['c'] = $cat->parent->id;
					$params['sc'] = $cat->id;
				}
			}
			if (!empty($city) && !empty($city->id)) {
				$params['l'] = $city->id;
			}
			
			$paramsToRemove = ['page', 'maxSalary'];
			$url = urlQuery($this->search())->removeParameters($paramsToRemove)->setParameters($params)->toString();
		}
		
		return $url;
	}
	
	/**
	 * Remove listing type from filters
	 *
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function searchWithoutType($cat = null, $city = null): string
	{
		$url = request()->fullUrl();
		
		$cat = is_array($cat) ? Arr::toObject($cat) : $cat;
		$city = is_array($city) ? Arr::toObject($city) : $city;
		
		if ($this->doesTypeIsFiltered($cat, $city)) {
			$params = [];
			if (!empty($cat) && !empty($cat->id)) {
				$params['c'] = $cat->id;
				if (!empty($cat->parent)) {
					$params['c'] = $cat->parent->id;
					$params['sc'] = $cat->id;
				}
			}
			if (!empty($city) && !empty($city->id)) {
				$params['l'] = $city->id;
			}
			
			$paramsToRemove = ['page', 'type'];
			$url = urlQuery($this->search())->removeParameters($paramsToRemove)->setParameters($params)->toString();
		}
		
		return $url;
	}
	
	/**
	 * Remove a specific listing type from filters
	 *
	 * @param $typeId
	 * @param null $cat
	 * @param null $city
	 * @return string
	 */
	public function searchWithoutSpecificType($typeId, $cat = null, $city = null): string
	{
		$url = request()->fullUrl();
		
		$cat = is_array($cat) ? Arr::toObject($cat) : $cat;
		$city = is_array($city) ? Arr::toObject($city) : $city;
		
		$type = !str_starts_with($typeId, 'type.') ? 'type.' . $typeId : $typeId;
		
		if ($this->doesSpecificTypeIsFiltered($type, $cat, $city)) {
			$params = [];
			if (!empty($cat) && !empty($cat->id)) {
				$params['c'] = $cat->id;
				if (!empty($cat->parent)) {
					$params['c'] = $cat->parent->id;
					$params['sc'] = $cat->id;
				}
			}
			if (!empty($city) && !empty($city->id)) {
				$params['l'] = $city->id;
			}
			
			$paramsToRemove = ['page', $type];
			$url = urlQuery($this->search())->removeParameters($paramsToRemove)->setParameters($params)->toString();
		}
		
		return $url;
	}
}
