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

namespace App\Http\Controllers\Web\Front\Search;

use Larapen\LaravelMetaTags\Facades\MetaTag;
use Throwable;

class CityController extends BaseController
{
	/**
	 * City URL
	 * Pattern: (countryCode/)free-ads/city-slug/ID
	 *
	 * @param $countryCode
	 * @param $citySlug
	 * @param $cityId
	 * @return \Illuminate\Contracts\View\View
	 */
	public function index($countryCode, $citySlug, $cityId = null)
	{
		// Check if the multi-country site option is enabled
		if (!isMultiCountriesUrlsEnabled()) {
			$cityId = $citySlug;
		}
		
		// Get Posts
		$queryParams = [
			'op' => 'search',
			'l'  => $cityId,
		];
		$queryParams = array_merge(request()->all(), $queryParams);
		$data = getServiceData($this->postService->getEntries($queryParams));
		
		$apiMessage = data_get($data, 'message');
		$apiResult = data_get($data, 'result');
		$apiExtra = data_get($data, 'extra');
		$preSearch = data_get($apiExtra, 'preSearch');
		
		// Sidebar
		$this->bindSidebarVariables((array)data_get($apiExtra, 'sidebar'));
		
		// Get Titles
		$this->getBreadcrumb($preSearch);
		$this->getHtmlTitle($preSearch);
		
		// Meta Tags
		[$title, $description, $keywords] = $this->getMetaTag($preSearch);
		MetaTag::set('title', $title);
		MetaTag::set('description', $description);
		MetaTag::set('keywords', $keywords);
		
		// Open Graph
		try {
			$this->og->title($title)->description($description)->type('website');
		} catch (Throwable $e) {
		}
		view()->share('og', $this->og);
		
		// SEO: noindex
		$noIndexCitiesPermalinkPages = (
			config('settings.seo.no_index_cities')
			&& currentRouteActionContains('Search\CityController')
		);
		// Filters (and Orders) on Jobs Pages (Except Pagination)
		$noIndexFiltersOnEntriesPages = (
			config('settings.seo.no_index_filters_orders')
			&& currentRouteActionContains('Search\\')
			&& !empty(request()->except(['page']))
		);
		// "No result" Pages (Empty Searches Results Pages)
		$noIndexNoResultPages = (
			config('settings.seo.no_index_no_entry_found')
			&& currentRouteActionContains('Search\\')
			&& empty(data_get($apiResult, 'data'))
		);
		
		return view(
			'front.search.results',
			compact(
				'apiMessage',
				'apiResult',
				'apiExtra',
				'noIndexCitiesPermalinkPages',
				'noIndexFiltersOnEntriesPages',
				'noIndexNoResultPages'
			)
		);
	}
}
