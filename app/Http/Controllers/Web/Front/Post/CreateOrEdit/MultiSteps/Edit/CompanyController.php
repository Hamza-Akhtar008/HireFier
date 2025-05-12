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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Edit;

use App\Http\Controllers\Web\Auth\Traits\ShowReSendVerificationCodeButton;
use App\Http\Requests\Front\CompanyRequest;
use App\Services\CompanyService;
use App\Services\Payment\RetrievePackageFeatures;
use App\Services\PostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class CompanyController extends BaseController
{
	use ShowReSendVerificationCodeButton;
	use RetrievePackageFeatures;
	
	protected CompanyService $companyService;
	
	/**
	 * @param \App\Services\PostService $postService
	 * @param \App\Services\CompanyService $companyService
	 */
	public function __construct(PostService $postService, CompanyService $companyService)
	{
		parent::__construct($postService);
		
		$this->companyService = $companyService;
		
		$this->commonQueries();
	}
	
	/**
	 * Common Queries
	 *
	 * @return void
	 */
	private function commonQueries(): void
	{
		// ...
	}
	
	/**
	 * Show the form
	 *
	 * @param $id
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function showForm($id, Request $request)
	{
		// Check if the form type is 'Single-Step Form' and make redirection to it (permanently).
		if (isSingleStepFormEnabled()) {
			$url = urlGen()->editPost($id);
			if ($url != request()->fullUrl()) {
				return redirect()->to($url, 301)->withHeaders(config('larapen.core.noCacheHeaders'));
			}
		}
		
		$data = [];
		
		$post = null;
		$message = null;
		if (auth()->check()) {
			// Get Post
			$queryParams = [
				'embed'               => 'category,city,subAdmin1,subAdmin2,company',
				'countryCode'         => config('country.code'),
				'unactivatedIncluded' => true,
				'belongLoggedUser'    => true, // Logged user required
				'noCache'             => true,
			];
			$data = getServiceData($this->postService->getEntry($id, $queryParams));
			
			$post = data_get($data, 'result');
			$message = data_get($data, 'message');
		}
		
		abort_if(empty($post), 404, $message ?? t('post_not_found'));
		
		view()->share('post', $post);
		$this->shareNavItems($post);
		
		// Share the post's current active payment info (If exists)
		$this->getCurrentActivePaymentInfo($post);
		
		// Get the Post's Company
		// $companyId = data_get($post, 'company_id');
		$selectedCompany = data_get($post, 'company', []);
		view()->share('selectedCompany', $selectedCompany);
		
		// If the multiple companies per user option is enabled,
		// then auto-select the latest user's company and go to the listing details page
		if (!isCompanyFormEnabled($this->companies)) {
			if (!empty(data_get($selectedCompany, 'id'))) {
				// Get the next step URL
				$currentStep = $this->getStepByKey(get_class($this));
				$nextUrl = $this->getNextStepUrl($currentStep);
				
				return redirect()->to($nextUrl)->withHeaders(config('larapen.core.noCacheHeaders'));
			}
		}
		
		// Get the Post's City's Administrative Division
		$adminType = config('country.admin_type', 0);
		$admin = data_get($post, 'city.subAdmin' . $adminType);
		if (!empty($admin)) {
			view()->share('admin', $admin);
		}
		
		// Meta Tags
		MetaTag::set('title', t('update_my_ad'));
		MetaTag::set('description', t('update_my_ad'));
		
		// Get steps URLs & labels
		$previousStepUrl = urlGen()->post($post);
		$previousStepLabel = t('Back');
		$formActionUrl = url()->current();
		$nextStepUrl = null;
		$nextStepLabel = t('Update');
		
		// Share steps URLs & label variables
		view()->share('previousStepUrl', $previousStepUrl);
		view()->share('previousStepLabel', $previousStepLabel);
		view()->share('formActionUrl', $formActionUrl);
		view()->share('nextStepUrl', $nextStepUrl);
		view()->share('nextStepLabel', $nextStepLabel);
		
		return view('front.post.createOrEdit.multiSteps.edit.company', $data);
	}
	
	/**
	 * Submit the form
	 *
	 * @param $id
	 * @param \App\Http\Requests\Front\CompanyRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postForm($id, CompanyRequest $request): RedirectResponse
	{
		// Add required data in the request for API
		$inputArray = [
			'count_packages'        => $this->countPackages ?? 0,
			'count_payment_methods' => $this->countPaymentMethods ?? 0,
		];
		request()->merge($inputArray);
		
		// Update the post's company
		$data = getServiceData($this->postService->updatePostCompany($id, $request));
		
		// Parsing the API response
		$message = data_get($data, 'message');
		
		// Notification Message
		if (data_get($data, 'success')) {
			flash($message)->success();
		} else {
			$message = $message ?? t('unknown_error');
			flash($message)->error();
			
			$previousUrl = data_get($data, 'extra.previousUrl');
			$previousUrl = !empty($previousUrl) ? $previousUrl : url()->previous();
			
			return redirect()->to($previousUrl)->withInput();
		}
		
		// Get Post Resource
		$post = data_get($data, 'result');
		
		// Get the next URL
		$nextUrl = urlGen()->editPost($post);
		
		return redirect()->to($nextUrl);
	}
}
