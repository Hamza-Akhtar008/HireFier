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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\Traits;

use App\Helpers\Common\Files\Upload;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\FinishController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\PostController;
use App\Http\Requests\Front\PackageRequest;
use App\Http\Requests\Front\PostRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;

trait SubmitTrait
{
	/**
	 * Store all input data in database
	 *
	 * @param \App\Http\Requests\Front\PostRequest|\App\Http\Requests\Front\PackageRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	protected function storeInputDataInDatabase(PostRequest|PackageRequest $request): RedirectResponse
	{
		// Get all saved input data
		$companyInput = (array)session('companyInput');
		$postInput = (array)session('postInput');
		$paymentInput = (array)session('paymentInput');
		
		if (empty($postInput)) {
			$postStep = $this->getStepByKey(PostController::class);
			$postStepUrl = $this->getNextStepUrl($postStep);
			
			return redirect()->to($postStepUrl);
		}
		
		// Create the global input to send for database saving
		$inputArray = array_merge($companyInput, $postInput, $paymentInput);
		
		if (isset($inputArray['company'])) {
			if (isset($inputArray['company']['id'])) {
				unset($inputArray['company']['id']);
			}
			
			if (isset($inputArray['company']['logo_path'])) {
				$filePath = $inputArray['company']['logo_path'];
				if (!empty($filePath)) {
					if (hasTemporaryPath($filePath)) {
						$uploadedFile = Upload::fromPath($filePath);
						$inputArray['company']['logo_path'] = $uploadedFile;
					}
				}
			}
		}
		
		if (isset($inputArray['company_id']) && $inputArray['company_id'] == 'new') {
			$inputArray['company_id'] = 0;
		}
		
		// Add required data in the request for API
		$inputArray['count_packages'] = $this->countPackages ?? 0;
		$inputArray['count_payment_methods'] = $this->countPaymentMethods ?? 0;
		
		// $request->merge($inputArray);
		$request->replace($inputArray);
		
		// Set the company logo file in the current request (from the saved input variable)
		// Note: In that case file needs to be retrieved using $request->files->all() instead of $request->allFiles()
		if (isset($inputArray['company'])) {
			$uploadedLogo = $inputArray['company']['logo_path'] ?? null;
			if ($uploadedLogo instanceof UploadedFile) {
				$request->files->set('company.logo_path', $uploadedLogo);
			}
		}
		
		// Store the post
		$data = getServiceData($this->postService->store($request));
		
		// dd($data);
		
		// Parsing the API response
		$message = data_get($data, 'message');
		
		// Get the listing ID
		$postId = data_get($data, 'result.id');
		
		// Notification Message
		if (data_get($data, 'success')) {
			session()->put('message', $message);
			
			// Save the listing's ID in session
			if (!empty($postId)) {
				session()->put('postId', $postId);
			}
			
			// Clear Temporary Inputs & Files
			$this->clearTemporaryInput();
		} else {
			$message = $message ?? t('unknown_error');
			flash($message)->error();
			
			$previousUrl = data_get($data, 'extra.previousUrl');
			if (!empty($previousUrl)) {
				return redirect()->to($previousUrl)->withInput($request->except('company.logo_path'));
			} else {
				return redirect()->back()->withInput($request->except('company.logo_path'));
			}
		}
		
		// Get Listing Resource
		$post = data_get($data, 'result');
		
		abort_if(empty($post), 404, t('post_not_found'));
		
		// Get the Next URL
		$nextStep = $this->getStepByKey(FinishController::class);
		$nextUrl = $this->getStepUrl($nextStep);
		
		if (!empty($paymentInput)) {
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
		}
		
		// Get user's verification data
		$vEmailData = data_get($data, 'extra.sendEmailVerification');
		$vPhoneData = data_get($data, 'extra.sendPhoneVerification');
		$isUnverifiedEmail = (bool)(data_get($vEmailData, 'extra.isUnverifiedField') ?? false);
		$isUnverifiedPhone = (bool)(data_get($vPhoneData, 'extra.isUnverifiedField') ?? false);
		
		if ($isUnverifiedEmail || $isUnverifiedPhone) {
			// Save the Next URL before verification
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
		
		$nextUrl = urlQuery($nextUrl)
			->setParameters(request()->only(['packageId']))
			->toString();
		
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
