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

namespace App\Http\Controllers\Web\Front\Search\Traits;

use App\Http\Controllers\Web\Front\Post\Show\Traits\CatBreadcrumb;
use Illuminate\Support\Arr;

trait TitleTrait
{
	use CatBreadcrumb;
	
	/**
	 * Get Search HTML Title
	 *
	 * @param array|null $preSearch
	 * @param array|null $sidebar
	 * @return string
	 */
	public function getHtmlTitle(?array $preSearch = [], ?array $sidebar = []): string
	{
		// Get the Location's right arguments
		$cityId = request()->input('l');
		$stateName = request()->input('r');
		$isStateRequested = (!empty($stateName) && empty($cityId));
		
		// Get pre-searched objects/vars
		$state = data_get($preSearch, 'admin');
		$city = data_get($preSearch, 'city');
		$currentDistance = data_get($preSearch, 'distance.current', 0);
		$category = data_get($preSearch, 'cat');
		$parentCat = data_get($preSearch, 'cat.parent');
		$company = data_get($preSearch, 'company');
		
		// Title
		$htmlTitle = '';
		
		// Init.
		$htmlTitle .= t('All jobs');
		
		// Location
		if ($isStateRequested) {
			// State (Admin. Division)
			if (!empty($state)) {
				$searchUrl = urlGen()->searchWithoutCity($category, $state);
				
				$htmlTitle .= ' ' . t('in') . ' ';
				$htmlTitle .= '<a rel="nofollow" class="jobs-s-tag" href="' . $searchUrl . '">';
				$htmlTitle .= data_get($state, 'name');
				$htmlTitle .= '</a>';
			}
		} else {
			// City
			if (!empty($city)) {
				$searchUrl = urlGen()->searchWithoutCity($category, $city);
				
				if (config('settings.listings_list.cities_extended_searches')) {
					$distance = ($currentDistance == 1) ? 0 : $currentDistance;
					$htmlTitle .= ' ' . t('within') . ' ';
					$htmlTitle .= '<a rel="nofollow" class="jobs-s-tag" href="' . $searchUrl . '">';
					$htmlTitle .= t('x_distance_around_city', [
						'distance' => $distance,
						'unit'     => getDistanceUnit(config('country.code')),
						'city'     => data_get($city, 'name'),
					]);
				} else {
					$htmlTitle .= ' ' . t('in') . ' ';
					$htmlTitle .= '<a rel="nofollow" class="jobs-s-tag" href="' . $searchUrl . '">';
					$htmlTitle .= data_get($city, 'name');
				}
				$htmlTitle .= '</a>';
			}
		}
		
		// Category
		if (!empty($category)) {
			if (!empty($parentCat)) {
				$searchUrl = urlGen()->searchWithoutCategory($parentCat, $city);
				
				$htmlTitle .= ' ' . t('in') . ' ';
				$htmlTitle .= '<a rel="nofollow" class="jobs-s-tag" href="' . $searchUrl . '">';
				$htmlTitle .= data_get($parentCat, 'name');
				$htmlTitle .= '</a>';
			}
			$searchUrl = urlGen()->searchWithoutCategory($category, $city);
			
			$htmlTitle .= ' ' . t('in') . ' ';
			$htmlTitle .= '<a rel="nofollow" class="jobs-s-tag" href="' . $searchUrl . '">';
			$htmlTitle .= data_get($category, 'name');
			$htmlTitle .= '</a>';
		}
		
		// Company
		if (!empty($company)) {
			$htmlTitle .= ' ' . t('among') . ' ';
			$htmlTitle .= '<a rel="nofollow" class="jobs-s-tag" href="' . urlGen()->searchWithoutQuery() . '">';
			$htmlTitle .= data_get($company, 'name');
			$htmlTitle .= '</a>';
		}
		
		// Tag
		if (!empty($this->tag)) {
			$htmlTitle .= ' ' . t('for') . ' ';
			$htmlTitle .= '<a rel="nofollow" class="jobs-s-tag" href="' . urlGen()->searchWithoutQuery() . '">';
			$htmlTitle .= $this->tag;
			$htmlTitle .= '</a>';
		}
		
		// Date
		$postedDate = request()->input('postedDate');
		$postedDateLabel = data_get($sidebar, 'periodList.' . $postedDate);
		if (!empty($postedDateLabel)) {
			$searchUrl = urlGen()->searchWithoutDate($category, $city);
			
			$htmlTitle .= '<a rel="nofollow" class="jobs-s-tag" href="' . $searchUrl . '">';
			$htmlTitle .= $postedDateLabel;
			$htmlTitle .= '</a>';
		}
		
		// Job Type
		$requestedJobTypes = request()->input('type', []);
		if (!empty($requestedJobTypes)) {
			if (!is_array($requestedJobTypes)) {
				$requestedJobTypes = [$requestedJobTypes];
			}
			
			foreach ($requestedJobTypes as $key => $value) {
				$jobTypeLabel = data_get($sidebar, 'postTypes.' . $value . '.name');
				if (!empty($jobTypeLabel)) {
					$searchUrl = urlGen()->searchWithoutSpecificType($key, $category, $city);
					
					$htmlTitle .= '<a rel="nofollow" class="jobs-s-tag" href="' . $searchUrl . '">';
					$htmlTitle .= $jobTypeLabel;
					$htmlTitle .= '</a>';
				}
			}
		}
		
		view()->share('htmlTitle', $htmlTitle);
		
		return $htmlTitle;
	}
	
	/**
	 * Get Breadcrumbs Tabs
	 *
	 * @param array|null $preSearch
	 * @return array
	 */
	public function getBreadcrumb(?array $preSearch = []): array
	{
		// Get pre-searched objects
		$state = data_get($preSearch, 'admin');
		$city = data_get($preSearch, 'city');
		$currentDistance = data_get($preSearch, 'distance.current', 0);
		$category = data_get($preSearch, 'cat');
		$company = data_get($preSearch, 'company');
		
		// ...
		
		$bcTab = [];
		
		// City
		if (!empty($city)) {
			$distance = ($currentDistance == 1) ? 0 : $currentDistance;
			$title = t('in_x_distance_around_city', [
				'distance' => $distance,
				'unit'     => getDistanceUnit(config('country.code')),
				'city'     => data_get($city, 'name'),
			]);
			
			$bcTab[] = collect([
				'name'     => t('All jobs') . ' ' . $title,
				'url'      => urlGen()->city($city),
				'position' => !empty($category) ? 5 : 3,
				'location' => true,
			]);
		}
		
		// State (Admin. Division)
		if (!empty($state)) {
			$params = [
				'country' => config('country.icode'),
				'r'       => data_get($state, 'name'),
			];
			$searchUrl = urlGen()->search($params);
			$paramsToRemove = ['l', 'location', 'distance'];
			$searchUrl = urlQuery($searchUrl)->removeParameters($paramsToRemove)->toString();
			
			$title = data_get($state, 'name');
			
			$bcTab[] = collect([
				'name'     => !empty($category) ? (t('All jobs') . ' ' . $title) : data_get($state, 'name'),
				'url'      => $searchUrl,
				'position' => !empty($category) ? 5 : 3,
				'location' => true,
			]);
		}
		
		// Category
		$catBreadcrumb = $this->getCatBreadcrumb($category, 3);
		$bcTab = array_merge($bcTab, $catBreadcrumb);
		
		// Company
		if (!empty($company)) {
			$bcTab[] = collect([
				'name'     => data_get($company, 'name'),
				'url'      => urlGen()->company(data_get($company, 'id')),
				'position' => !empty($category) ? 5 : 3,
				'location' => true,
			]);
		}
		
		// Sort by Position
		$bcTab = array_values(Arr::sort($bcTab, function ($value) {
			return $value->get('position');
		}));
		
		view()->share('bcTab', $bcTab);
		
		return $bcTab;
	}
}
