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

namespace App\Http\Controllers\Web\Front\Account;

use App\Helpers\Services\Referrer;
use App\Http\Requests\Front\CompanyRequest;
use App\Services\CompanyService;
use App\Services\UserService;
use Bedigit\Breadcrumbs\BreadcrumbFacade;
use Illuminate\Http\RedirectResponse;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class CompanyController extends AccountBaseController
{
	protected CompanyService $companyService;
	
	/**
	 * @param \App\Services\UserService $userService
	 * @param \App\Services\CompanyService $companyService
	 */
	public function __construct(UserService $userService, CompanyService $companyService)
	{
		parent::__construct($userService);
		
		$this->companyService = $companyService;
	}
	
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function index()
	{
		// Get the user's companies
		$queryParams = [
			'belongLoggedUser' => true,
			'countPosts'       => true,
			'keyword'          => request()->input('q', request()->input('keyword')),
			'sort'             => 'created_at',
		];
		$data = getServiceData($this->companyService->getEntries($queryParams));
		
		$apiMessage = data_get($data, 'message');
		$apiResult = data_get($data, 'result');
		
		/*
		 * If the "Multiple Companies Per User" option is not enabled,
		 * redirect automatically the user to his last added company edition form.
		 * If the user has not added any company yet, redirect him to the company creation form.
		 */
		$companiesList = data_get($apiResult, 'data');
		if (!isCompanyFormEnabled($companiesList)) {
			$company = data_get($companiesList, '0');
			$companyUrl = !empty($company)
				? url(urlGen()->getAccountBasePath() . '/companies/' . data_get($company, 'id') . '/edit')
				: url(urlGen()->getAccountBasePath() . '/companies/create');
			
			return redirect()->to($companyUrl);
		}
		
		$appName = config('settings.app.name', 'Site Name');
		$title = t('my_companies_list') . ' - ' . $appName;
		
		// Meta Tags
		MetaTag::set('title', $title);
		MetaTag::set('description', t('my_companies_list_on', ['appName' => config('settings.app.name')]));
		
		// Breadcrumb
		BreadcrumbFacade::add(t('my_companies'));
		
		return view('front.account.company.index', compact('apiResult', 'apiMessage'));
	}
	
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function create()
	{
		// Get all the user's companies
		$companiesList = Referrer::getLoggedUserCompanies();
		
		/*
		 * If the "Multiple Companies Per User" option is not enabled,
		 * redirect automatically the user to his last added company edition form.
		 */
		if (!isCompanyFormEnabled($companiesList)) {
			$company = data_get($companiesList, '0');
			if (!empty($company)) {
				$companyUrl = url(urlGen()->getAccountBasePath() . '/companies/' . data_get($company, 'id') . '/edit');
				
				return redirect()->to($companyUrl);
			}
		}
		
		// Meta Tags
		MetaTag::set('title', t('Create a new company'));
		MetaTag::set('description', t('Create a new company on', ['appName' => config('settings.app.name')]));
		
		return view('front.account.company.create');
	}
	
	/**
	 * Store a newly created resource in storage.
	 *
	 * @param CompanyRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function store(CompanyRequest $request): RedirectResponse
	{
		// Store the company
		$data = getServiceData($this->companyService->store($request));
		
		// Parsing the API response
		$message = data_get($data, 'message');
		
		// Notification Message
		if (data_get($data, 'success')) {
			flash($message)->success();
		} else {
			$message = $message ?? t('unknown_error');
			flash($message)->error();
			
			return redirect()->back()->withErrors(['error' => $message])->withInput();
		}
		
		$pathTo = urlGen()->getAccountBasePath() . '/companies';
		$id = data_get($data, 'result.id');
		if (!empty($id)) {
			$pathTo = urlGen()->getAccountBasePath() . '/companies/' . $id . '/edit';
		}
		
		return redirect()->to($pathTo);
	}
	
	/**
	 * Display the specified resource.
	 *
	 * @param $id
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function show($id): RedirectResponse
	{
		return redirect()->to(urlGen()->getAccountBasePath() . '/companies/' . $id . '/edit');
	}
	
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param $id
	 * @return \Illuminate\Contracts\View\View
	 */
	public function edit($id)
	{
		// Get the company
		$queryParams = [
			'embed'            => 'city,subAdmin1,subAdmin2',
			'belongLoggedUser' => true,
		];
		$data = getServiceData($this->companyService->getEntry($id, $queryParams));
		
		$message = data_get($data, 'message');
		$company = data_get($data, 'result');
		
		abort_if(empty($company), 404, $message ?? t('company_not_found'));
		
		// Meta Tags
		MetaTag::set('title', t('Edit the Company'));
		MetaTag::set('description', t('Edit the Company on', ['appName' => config('settings.app.name')]));
		
		return view('front.account.company.edit', compact('company'));
	}
	
	/**
	 * Update the specified resource in storage.
	 *
	 * @param $id
	 * @param CompanyRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function update($id, CompanyRequest $request): RedirectResponse
	{
		// Update the company
		$data = getServiceData($this->companyService->update($id, $request));
		
		// Parsing the API response
		$message = data_get($data, 'message');
		
		// Notification Message
		if (data_get($data, 'success')) {
			flash($message)->success();
			
			return redirect()->to(urlGen()->getAccountBasePath() . '/companies/' . $id . '/edit');
		} else {
			$message = $message ?? t('unknown_error');
			flash($message)->error();
			
			return redirect()->back()->withErrors(['error' => $message])->withInput();
		}
	}
	
	/**
	 * Remove the specified resource from storage.
	 *
	 * @param null $id
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function destroy($id = null): RedirectResponse
	{
		// Get entries ID(s)
		$ids = getSelectedEntryIds($id, request()->input('entries'), asString: true);
		
		// Delete company(ies)
		$data = getServiceData($this->companyService->destroy($ids));
		
		// Parsing the API response
		$message = data_get($data, 'message');
		
		// Notification Message
		if (data_get($data, 'success')) {
			flash($message)->success();
			
			return redirect()->to(urlGen()->getAccountBasePath() . '/companies');
		} else {
			$message = $message ?? t('unknown_error');
			flash($message)->error();
			
			return redirect()->back()->withErrors(['error' => $message])->withInput();
		}
	}
}
