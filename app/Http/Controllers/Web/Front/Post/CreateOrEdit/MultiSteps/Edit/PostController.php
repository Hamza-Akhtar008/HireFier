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

use App\Helpers\Services\Referrer;
use App\Http\Requests\Front\PostRequest;
use App\Services\Payment\RetrievePackageFeatures;
use App\Services\PostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class PostController extends BaseController
{
	use RetrievePackageFeatures;
	
	/**
	 * @param \App\Services\PostService $postService
	 */
	public function __construct(PostService $postService)
	{
		parent::__construct($postService);
		
		$this->commonQueries();
	}
	
	/**
	 * Common Queries
	 *
	 * @return void
	 */
	private function commonQueries(): void
	{
		// Get postTypes
		$postTypes = Referrer::getPostTypes();
		view()->share('postTypes', $postTypes);
		
		// Get Salary Types
		$salaryTypes = Referrer::getSalaryTypes();
		view()->share('salaryTypes', $salaryTypes);
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
		
		// Get Post
		$post = null;
		$message = null;
		if (auth()->check()) {
			// Get post
			$queryParams = [
				'embed'               => 'category,city,subAdmin1,subAdmin2,company',
				'countryCode'         => config('country.code'),
				'unactivatedIncluded' => true,
				'belongLoggedUser'    => true, // Logged user required
				'noCache'             => true,
			];
			$data = getServiceData($this->postService->getEntry($id, $queryParams));
			
			$message = data_get($data, 'message');
			$post = data_get($data, 'result');
		}
		
		abort_if(empty($post), 404, $message ?? t('post_not_found'));
		
		view()->share('post', $post);
		$this->shareNavItems($post);
		
		// Share the post's current active payment info (If exists)
		$this->getCurrentActivePaymentInfo($post);
		
		// Get the Post's Company
		if (!empty(data_get($post, 'company_id'))) {
			view()->share('selectedCompany', data_get($post, 'company'));
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
		
		return view('front.post.createOrEdit.multiSteps.edit.post', $data);
	}
	
	/**
	 * Submit the form
	 *
	 * @param $id
	 * @param \App\Http\Requests\Front\PostRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postForm($id, PostRequest $request): RedirectResponse
	{
		// Add required data in the request for API
		$inputArray = [
			'count_packages'        => $this->countPackages ?? 0,
			'count_payment_methods' => $this->countPaymentMethods ?? 0,
		];
		request()->merge($inputArray);
		
		// Update the post
		$data = getServiceData($this->postService->update($id, $request));
		
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
		if (data_get($data, 'extra.steps.payment')) {
			$nextUrl = urlGen()->editPostPayment($post);
		} else {
			$nextUrl = urlGen()->post($post);
		}
		
		// Get user's verification data
		$vEmailData = data_get($data, 'extra.sendEmailVerification');
		$vPhoneData = data_get($data, 'extra.sendPhoneVerification');
		$isUnverifiedEmail = (bool)(data_get($vEmailData, 'extra.isUnverifiedField') ?? false);
		$isUnverifiedPhone = (bool)(data_get($vPhoneData, 'extra.isUnverifiedField') ?? false);
		
		if ($isUnverifiedEmail || $isUnverifiedPhone) {
			session()->put('itemNextUrl', $nextUrl);
			
			if ($isUnverifiedEmail) {
				// Create Notification Trigger
				$resendEmailVerificationData = data_get($vEmailData, 'extra');
				session()->put('resendEmailVerificationData', collect($resendEmailVerificationData)->toJson());
			}
			
			if ($isUnverifiedPhone) {
				// Create Notification Trigger
				$resendPhoneVerificationData = data_get($vPhoneData, 'extra');
				session()->put('resendPhoneVerificationData', collect($resendPhoneVerificationData)->toJson());
				
				// Phone Number verification
				// Get the token|code verification form page URL
				// The user is supposed to have received this token|code by SMS
				$nextUrl = urlGen()->phoneVerification('posts');
			}
		}
		
		// Get mail sending data
		$mailData = data_get($data, 'extra.mail');
		
		// Mail Notification Message
		if (data_get($mailData, 'message')) {
			$mailMessage = data_get($mailData, 'message');
			if (data_get($mailData, 'success')) {
				flash($mailMessage)->success();
			} else {
				flash($mailMessage)->error();
			}
		}
		
		return redirect()->to($nextUrl);
	}
}
