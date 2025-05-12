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

use App\Services\CompanyService;
use App\Services\ContactService;
use App\Services\PostService;
use Illuminate\Http\Request;
use Larapen\LaravelMetaTags\Facades\MetaTag;
use Throwable;

class CompanyController extends BaseController
{
	protected CompanyService $companyService;
	
	public ?array $company;
	
	/**
	 * @param \App\Services\PostService $postService
	 * @param \App\Services\ContactService $contactService
	 * @param \Illuminate\Http\Request $request
	 * @param \App\Services\CompanyService $companyService
	 */
	public function __construct(
		PostService    $postService,
		ContactService $contactService,
		Request        $request,
		CompanyService $companyService
	)
	{
		parent::__construct($postService, $contactService, $request);
		
		$this->companyService = $companyService;
	}
	
	/**
	 * Listing of Companies
	 *
	 * @return \Illuminate\Contracts\View\View
	 */
	public function index()
	{
		// Get companies
		$queryParams = [
			'countPosts' => true,
			'keyword'    => request()->input('q', request()->input('keyword')),
			'perPage'    => 24,
			'sort'       => 'created_at',
		];
		$queryParams = array_merge(request()->all(), $queryParams);
		$data = getServiceData($this->companyService->getEntries($queryParams));
		
		$apiMessage = data_get($data, 'message');
		$apiResult = data_get($data, 'result');
		
		// Meta Tags
		$title = t('companies_list_title', ['appName' => config('settings.app.name')]);
		$description = t('companies_list_description', ['appName' => config('settings.app.name')]);
		MetaTag::set('title', $title);
		MetaTag::set('description', $description);
		
		// Open Graph
		try {
			$this->og->title($title)->description($description)->type('website');
		} catch (Throwable $e) {
		}
		view()->share('og', $this->og);
		
		$isFromSearchCompany = currentRouteActionContains('Search\CompanyController');
		
		// SEO: noindex
		$noIndexCompaniesPages = (
			config('settings.seo.no_index_companies')
			&& currentRouteActionContains('Search\CompanyController')
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
			'front.search.company.index',
			compact(
				'apiResult',
				'apiMessage',
				'isFromSearchCompany',
				'noIndexCompaniesPages',
				'noIndexFiltersOnEntriesPages',
				'noIndexNoResultPages'
			)
		);
	}
	
	/**
	 * Show a Company profiles (with its Jobs ads)
	 *
	 * @param $countryCode
	 * @param $companyId
	 * @return \Illuminate\Contracts\View\View
	 */
	public function profile($countryCode, $companyId = null)
	{
		// Check if the multi-country site option is enabled
		if (!isMultiCountriesUrlsEnabled()) {
			$companyId = $countryCode;
		}
		
		// Get Posts
		$queryParams = [
			'op'        => 'search',
			'companyId' => trim($companyId),
		];
		$queryParams = array_merge(request()->all(), $queryParams);
		$data = getServiceData($this->postService->getEntries($queryParams));
		
		$apiMessage = data_get($data, 'message');
		$apiResult = data_get($data, 'result');
		$apiExtra = data_get($data, 'extra');
		$preSearch = data_get($apiExtra, 'preSearch');
		$company = data_get($preSearch, 'company');
		
		abort_if(empty($company), 404, $apiMessage ?? t('company_not_found'));
		
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
		
		$isFromSearchCompany = currentRouteActionContains('Search\CompanyController');
		
		// SEO: noindex
		$noIndexCompaniesPages = (
			config('settings.seo.no_index_companies')
			&& currentRouteActionContains('Search\CompanyController')
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
			'front.search.company.profile',
			compact(
				'company',
				'apiMessage',
				'apiResult',
				'apiExtra',
				'isFromSearchCompany',
				'noIndexCompaniesPages',
				'noIndexFiltersOnEntriesPages',
				'noIndexNoResultPages'
			)
		);
	}
}
