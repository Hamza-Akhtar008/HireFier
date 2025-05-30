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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\SingleStep;

use App\Helpers\Services\Referrer;
use App\Http\Controllers\Web\Front\FrontController;
use App\Http\Controllers\Web\Front\Payment\HasPaymentRedirection;
use App\Http\Requests\Front\PostRequest;
use App\Services\Payment\HasPaymentReferrers;
use App\Services\Payment\HasPaymentTrigger;
use App\Services\Payment\Promotion\SingleStepPayment;
use App\Services\PostService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Larapen\LaravelMetaTags\Facades\MetaTag;

class EditController extends FrontController
{
	use HasPaymentReferrers;
	use SingleStepPayment, HasPaymentTrigger, HasPaymentRedirection;
	
	protected PostService $postService;
	
	// Payment's properties
	public array $msg = [];
	public array $uri = [];
	public Collection $packages;
	public Collection $paymentMethods;
	
	/**
	 * @param \App\Services\PostService $postService
	 */
	public function __construct(PostService $postService)
	{
		parent::__construct();
		
		$this->postService = $postService;
		
		$this->commonQueries();
	}
	
	/**
	 * Common Queries
	 *
	 * @return void
	 */
	public function commonQueries(): void
	{
		$this->getPaymentReferrersData();
		$this->setPaymentSettingsForPromotion();
		
		// References
		$data = [];
		
		// Get postTypes
		$data['postTypes'] = Referrer::getPostTypes();
		view()->share('postTypes', $data['postTypes']);
		
		// Get Salary Types
		$data['salaryTypes'] = Referrer::getSalaryTypes();
		view()->share('salaryTypes', $data['salaryTypes']);
		
		// Get the User's Company
		if (auth()->check()) {
			$data['companies'] = Referrer::getLoggedUserCompanies();
			view()->share('companies', $data['companies']);
		}
		
		// Save common's data
		$this->data = $data;
	}
	
	/**
	 * Show the form
	 *
	 * @param $postId
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function showForm($postId)
	{
		// Check if the form type is 'Multi-Step Form' and make redirection to it (permanently).
		if (isMultipleStepsFormEnabled()) {
			$url = urlGen()->editPost($postId);
			if ($url != request()->fullUrl()) {
				return redirect()->to($url, 301)->withHeaders(config('larapen.core.noCacheHeaders'));
			}
		}
		
		$viewData = [];
		
		// Get the post
		$queryParams = [
			'embed'               => 'category,pictures,city,subAdmin1,subAdmin2,possiblePayment,package,paymentEndingLater,company',
			'countryCode'         => config('country.code'),
			'unactivatedIncluded' => true,
			'belongLoggedUser'    => true, // Logged user required
			'noCache'             => true,
		];
		$data = getServiceData($this->postService->getEntry($postId, $queryParams));
		
		$message = data_get($data, 'message');
		$post = data_get($data, 'result');
		
		abort_if(empty($post), 404, $message ?? t('post_not_found'));
		
		view()->share('post', $post);
		
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
		
		return view('front.post.createOrEdit.singleStep.edit', $viewData);
	}
	
	/**
	 * Submit the form
	 *
	 * @param $postId
	 * @param \App\Http\Requests\Front\PostRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postForm($postId, PostRequest $request): RedirectResponse
	{
		// Update Post
		$data = getServiceData($this->postService->update($postId, $request));
		
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
			
			return redirect()->to($previousUrl)->withInput($request->except('company.logo_path'));
		}
		
		// Get the post
		$post = data_get($data, 'result');
		
		abort_if(empty($post), 404, t('post_not_found'));
		
		// Get the next URL
		$nextUrl = urlGen()->post($post);
		
		// Get the post ID
		$postId = data_get($post, 'id');
		
		// Check if the payment process has been triggered
		// NOTE: Payment bypass email or phone verification
		// ===| Make|send payment (if needed) |==============
		
		$postObj = $this->retrievePayableModel($request, $postId);
		if (!empty($postObj)) {
			$payResult = $this->isPaymentRequested($request, $postObj);
			if (data_get($payResult, 'success')) {
				return $this->sendPayment($request, $postObj);
			}
			if (data_get($payResult, 'failure')) {
				flash(data_get($payResult, 'message'))->error();
			}
		}
		
		// ===| If no payment is made (continue) |===========
		
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
