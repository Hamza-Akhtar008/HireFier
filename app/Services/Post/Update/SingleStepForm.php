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

namespace App\Services\Post\Update;

use App\Helpers\Common\Files\Upload;
use App\Http\Requests\Front\PostRequest;
use App\Http\Resources\PostResource;
use App\Jobs\GenerateLogoThumbnails;
use App\Models\City;
use App\Models\Company;
use App\Models\Post;
use App\Models\Scopes\ReviewedScope;
use App\Models\Scopes\VerifiedScope;

trait SingleStepForm
{
	/**
	 * @param $postId
	 * @param \App\Http\Requests\Front\PostRequest $request
	 * @return \Illuminate\Http\JsonResponse|mixed
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	protected function singleStepFormUpdate($postId, PostRequest $request)
	{
		$authUser = auth(getAuthGuard())->user();
		
		if (empty($authUser)) {
			return apiResponse()->unauthorized();
		}
		
		$countryCode = $request->input('country_code', config('country.code'));
		
		$post = Post::withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
			->inCountry($countryCode)
			->with(['payment'])
			->where('user_id', $authUser->getAuthIdentifier())
			->where('id', $postId)
			->first();
		
		if (empty($post)) {
			return apiResponse()->notFound(t('post_not_found'));
		}
		
		// Get the Post's City
		$city = City::find($request->input('city_id', 0));
		if (empty($city)) {
			return apiResponse()->error(t('city_not_found'));
		}
		
		// Retrieve the listing's company
		// ---
		// Get the company ID
		$companyId = $request->input('company_id');
		
		// Is new company creation allowed in this context?
		$isNewCompanyCreationAllowed = (empty($companyId) || $companyId == 'new');
		if ($isNewCompanyCreationAllowed) {
			// Create a new company
			// ---
			// Get company input
			$companyInput = $request->input('company');
			
			// Fill the company's country code (if missing)
			if (empty($companyInput['country_code'])) {
				$companyInput['country_code'] = $countryCode;
			}
			
			// Fill the logged-in user ID (if missing)
			if (empty($companyInput['user_id'])) {
				$companyInput['user_id'] = $authUser->getAuthIdentifier();
			}
			
			// Store the User's Company
			$company = new Company();
			foreach ($companyInput as $key => $value) {
				if (in_array($key, $company->getFillable())) {
					$company->{$key} = $value;
				}
			}
			$company->save();
			
			// Save the Company's Logo
			if ($request->hasFile('company.logo_path')) {
				$param = [
					'destPath' => 'files/' . strtolower($company->country_code) . '/' . $company->id,
					'width'    => (int)config('larapen.media.resize.namedOptions.company-logo.width', 800),
					'height'   => (int)config('larapen.media.resize.namedOptions.company-logo.height', 800),
					'ratio'    => config('larapen.media.resize.namedOptions.company-logo.ratio', '1'),
					'upsize'   => config('larapen.media.resize.namedOptions.company-logo.upsize', '1'),
				];
				$company->logo_path = Upload::image($request->file('company.logo_path'), $param['destPath'], $param);
				
				$company->save();
				
				// Generate the company's logo thumbnails
				GenerateLogoThumbnails::dispatchSync($company);
			}
		} else {
			// Get the user's selected company
			$company = Company::query()
				->where('user_id', $authUser->getAuthIdentifier())
				->where('id', $companyId)
				->first();
		}
		
		// Return error if a company is not set
		if (empty($company)) {
			$message = t('Please select a company or New Company to create one');
			
			return apiResponse()->error($message);
		}
		
		// Conditions to Verify User's Email or Phone
		$emailVerificationRequired = config('settings.mail.email_verification') == '1'
			&& $request->filled('email')
			&& $request->input('email') != $post->email;
		$phoneVerificationRequired = config('settings.sms.phone_verification') == '1'
			&& $request->filled('phone')
			&& $request->input('phone') != $post->phone;
		
		// Check if the listing sensitive data have been changed
		$hasListingDataUpdated = false;
		if (config('settings.listing_form.listings_review_activation')) {
			$hasListingDataUpdated = (
				md5($post->title) != md5($request->input('title'))
				|| md5($post->company_description) != md5((isset($company->description)) ? $company->description : null)
				|| md5($post->description) != md5($request->input('description'))
				|| md5($post->application_url) != md5($request->input('application_url'))
			);
		}
		
		// Update Post
		$input = $request->only($post->getFillable());
		foreach ($input as $key => $value) {
			$post->{$key} = $value;
		}
		
		// Revoke the listing approval when its sensitive data have
		// been changed, allowing the admin user to approve the changes
		if ($hasListingDataUpdated) {
			$post->reviewed_at = null;
		}
		
		// Checkboxes
		$post->negotiable = $request->input('negotiable');
		$post->phone_hidden = $request->input('phone_hidden');
		
		// Other fields
		$post->company_id = $company->id ?? 0;
		$post->company_name = $company->name ?? null;
		$post->company_description = $company->description ?? null;
		$post->logo_path = $company->logo_path ?? null;
		$post->lat = $city->latitude;
		$post->lon = $city->longitude;
		
		// Generate the listing's logo thumbnails
		GenerateLogoThumbnails::dispatchSync($post);
		
		// Email verification key generation
		if ($emailVerificationRequired) {
			$post->email_token = generateToken(hashed: true);
			$post->email_verified_at = null;
		}
		
		// Phone verification key generation
		if ($phoneVerificationRequired) {
			$post->phone_token = generateOtp(defaultOtpLength());
			$post->phone_verified_at = null;
		}
		
		// Save
		$post->save();
		
		$data = [
			'success' => true,
			'message' => null,
			'result'  => (new PostResource($post))->toArray($request),
		];
		
		$extra = [];
		
		if (!doesRequestIsFromWebClient()) {
			// ===| Make|send payment (if needed) |==============
			
			$payResult = $this->isPaymentRequested($request, $post);
			if (data_get($payResult, 'success')) {
				return $this->sendPayment($request, $post);
			}
			if (data_get($payResult, 'failure')) {
				return apiResponse()->error(data_get($payResult, 'message'));
			}
			
			// ===| If no payment is made (continue) |===========
		}
		
		$data['success'] = true;
		$data['message'] = t('your_listing_is_updated');
		
		// Send an Email Verification message
		if ($emailVerificationRequired) {
			$extra['sendEmailVerification'] = $this->sendEmailVerification('posts', $post);
			if (
				array_key_exists('success', $extra['sendEmailVerification'])
				&& array_key_exists('message', $extra['sendEmailVerification'])
			) {
				$vMessage = getModelVerificationMessage($post, 'email');
				$data['message'] = $data['message'] . ' ' . $vMessage;
				$extra['mail']['success'] = $extra['sendEmailVerification']['success'];
				$extra['mail']['message'] = $extra['sendEmailVerification']['message'];
			}
		}
		
		// Send a Phone Verification message
		if ($phoneVerificationRequired) {
			$extra['sendPhoneVerification'] = $this->sendPhoneVerification('posts', $post);
			if (
				array_key_exists('success', $extra['sendPhoneVerification'])
				&& array_key_exists('message', $extra['sendPhoneVerification'])
			) {
				$vMessage = getModelVerificationMessage($post, 'phone');
				$data['message'] = $data['message'] . ' ' . $vMessage;
				$extra['mail']['success'] = $extra['sendPhoneVerification']['success'];
				$extra['mail']['message'] = $extra['sendPhoneVerification']['message'];
			}
		}
		
		$data['extra'] = $extra;
		
		return apiResponse()->json($data);
	}
}
