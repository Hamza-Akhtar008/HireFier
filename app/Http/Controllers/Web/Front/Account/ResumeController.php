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

use App\Http\Requests\Front\ResumeRequest;
use App\Services\ResumeService;
use App\Services\UserService;
use Bedigit\Breadcrumbs\BreadcrumbFacade;
use Illuminate\Http\RedirectResponse;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class ResumeController extends AccountBaseController
{
	protected ResumeService $resumeService;
	
	public function __construct(UserService $userService, ResumeService $resumeService)
	{
		parent::__construct($userService);
		
		$this->resumeService = $resumeService;
	}
	
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Contracts\View\View
	 */
	public function index()
	{
		// Get user's resumes
		$queryParams = [
			'belongLoggedUser' => true,
			'keyword'          => request()->input('q', request()->input('keyword')),
			'sort'             => 'created_at',
		];
		$data = getServiceData($this->resumeService->getEntries($queryParams));
		
		$apiMessage = data_get($data, 'message');
		$apiResult = data_get($data, 'result');
		
		$appName = config('settings.app.name', 'Site Name');
		$title = t('my_resumes_list') . ' - ' . $appName;
		
		// Meta Tags
		MetaTag::set('title', $title);
		MetaTag::set('description', t('my_resumes_list_on', ['appName' => config('settings.app.name')]));
		
		// Breadcrumb
		BreadcrumbFacade::add(t('my_resumes'));
		
		return view('front.account.resume.index', compact('apiResult', 'apiMessage'));
	}
	
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Contracts\View\View
	 */
	public function create()
	{
		// Meta Tags
		MetaTag::set('title', t('Create a resume'));
		MetaTag::set('description', t('Create a resume on', ['appName' => config('settings.app.name')]));
		
		return view('front.account.resume.create');
	}
	
	/**
	 * Store a newly created resource in storage.
	 *
	 * @param ResumeRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function store(ResumeRequest $request): RedirectResponse
	{
		// Store the resume
		$data = getServiceData($this->resumeService->store($request));
		
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
		
		$pathTo = urlGen()->getAccountBasePath() . '/resumes';
		$id = data_get($data, 'result.id');
		if (!empty($id)) {
			$pathTo = urlGen()->getAccountBasePath() . '/resumes/' . $id . '/edit';
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
		return redirect()->to(urlGen()->getAccountBasePath() . '/resumes/' . $id . '/edit');
	}
	
	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param $id
	 * @return \Illuminate\Contracts\View\View
	 */
	public function edit($id)
	{
		// Get a user's resume
		$queryParams = [
			'belongLoggedUser' => true,
		];
		$data = getServiceData($this->resumeService->getEntry($id, $queryParams));
		
		$message = data_get($data, 'message');
		$resume = data_get($data, 'result');
		
		abort_if(empty($resume), 404, $message ?? t('resume_not_found'));
		
		// Meta Tags
		MetaTag::set('title', t('Edit the resume'));
		MetaTag::set('description', t('Edit the resume on', ['appName' => config('settings.app.name')]));
		
		return view('front.account.resume.edit', compact('message', 'resume'));
	}
	
	/**
	 * Update the specified resource in storage.
	 *
	 * @param $id
	 * @param ResumeRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function update($id, ResumeRequest $request): RedirectResponse
	{
		// Update the resume
		$data = getServiceData($this->resumeService->update($id, $request));
		
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
		
		return redirect()->to(urlGen()->getAccountBasePath() . '/resumes/' . $id . '/edit');
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
		
		// Delete resume(s)
		$data = getServiceData($this->resumeService->destroy($ids));
		
		// Parsing the API response
		$message = data_get($data, 'message');
		
		// Notification Message
		if (data_get($data, 'success')) {
			flash($message)->success();
			
			return redirect()->to(urlGen()->getAccountBasePath() . '/resumes');
		} else {
			$message = $message ?? t('unknown_error');
			flash($message)->error();
			
			return redirect()->back()->withErrors(['error' => $message])->withInput();
		}
	}
}
