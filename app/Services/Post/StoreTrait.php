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

namespace App\Services\Post;

use App\Helpers\Common\Arr;
use App\Helpers\Common\Files\Upload;
use App\Http\Resources\PostResource;
use App\Jobs\GenerateLogoThumbnails;
use App\Models\City;
use App\Models\Company;
use App\Models\Post;
use App\Services\Post\Store\AutoRegistrationTrait;
use Illuminate\Http\Request;
use Throwable;

trait StoreTrait
{
	use AutoRegistrationTrait;
	
	/**
	 * @param \App\Http\Requests\Front\PostRequest $request
	 * @return \Illuminate\Http\JsonResponse|mixed
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	protected function storePost(Request $request)
	{
		// Get the listing's city
		$city = City::find($request->input('city_id', 0));
		if (empty($city)) {
			return apiResponse()->error(t('city_not_found'));
		}
		
		// Get the logged user
		$authUser = auth(getAuthGuard())->user();
		
		// Retrieve the listing's company
		$company = null;
		
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
				$companyInput['country_code'] = config('country.code');
			}
			
			if (!empty($authUser)) {
				// For logged users
				// ---
				// Fill the logged-in user ID (if missing)
				if (empty($companyInput['user_id'])) {
					$companyInput['user_id'] = $authUser->getAuthIdentifier();
				}
				
				// Get the logo uploaded file object
				$logoFile = $request->file('company.logo_path', $request->files->get('company.logo_path'));
				
				// Store the user's company
				$company = new Company();
				foreach ($companyInput as $key => $value) {
					if (in_array($key, $company->getFillable())) {
						$company->{$key} = $value;
					}
				}
				$company->save();
				
				// Save the company's logo
				// NOTE: Not to be confused with the logo to save in the listing table
				if (!empty($logoFile)) {
					$param = [
						'destPath' => 'files/' . strtolower($company->country_code) . '/' . $company->id,
						'width'    => (int)config('larapen.media.resize.namedOptions.company-logo.width', 800),
						'height'   => (int)config('larapen.media.resize.namedOptions.company-logo.height', 800),
						'ratio'    => config('larapen.media.resize.namedOptions.company-logo.ratio', '1'),
						'upsize'   => config('larapen.media.resize.namedOptions.company-logo.upsize', '1'),
					];
					$company->logo_path = Upload::image($logoFile, $param['destPath'], $param);
					
					$company->save();
					
					// Generate the company's logo thumbnails
					GenerateLogoThumbnails::dispatchSync($company);
				}
			} else {
				// For guest users
				$company = Arr::toObject($companyInput);
				
				// Save the company's logo
				// Upload & save the (eventual) company's logo in the 'posts' table below
			}
		} else {
			// Get the user's selected company
			if (!empty($authUser)) {
				$company = Company::query()
					->where('user_id', $authUser->getAuthIdentifier())
					->where('id', $companyId)
					->first();
			}
		}
		
		// Return error if a company is not set
		if (empty($company)) {
			$message = t('Please select a company or New Company to create one');
			
			return apiResponse()->error($message);
		}
		
		// Conditions to Verify User's Email or Phone
		if (!empty($authUser)) {
			$emailVerificationRequired = (
				config('settings.mail.email_verification') == '1'
				&& $request->filled('email')
				&& $request->input('email') != $authUser->email
			);
			$phoneVerificationRequired = (
				config('settings.sms.phone_verification') == '1'
				&& $request->filled('phone')
				&& $request->input('phone') != $authUser->phone
			);
		} else {
			$emailVerificationRequired = config('settings.mail.email_verification') == '1' && $request->filled('email');
			$phoneVerificationRequired = config('settings.sms.phone_verification') == '1' && $request->filled('phone');
		}
		
		// New Post
		$post = new Post();
		$input = $request->only($post->getFillable());
		foreach ($input as $key => $value) {
			$post->{$key} = $value;
		}
		
		if (!empty($authUser)) {
			// Try to use the user's possible subscription
			$authUser->loadMissing('payment');
			if (!empty($authUser->payment)) {
				$post->payment_id = $authUser->payment->id ?? null;
			}
		}
		
		// Checkboxes
		$post->negotiable = $request->input('negotiable');
		$post->phone_hidden = $request->input('phone_hidden');
		
		// Other fields
		$post->country_code = $request->input('country_code', config('country.code'));
		$post->user_id = !empty($authUser) ? $authUser->getAuthIdentifier() : null;
		$post->company_id = $company->id ?? 0;
		$post->company_name = $company->name ?? null;
		$post->company_description = $company->description ?? null;
		$post->lat = $city->latitude;
		$post->lon = $city->longitude;
		$post->tmp_token = generateToken(hashed: true);
		$post->reviewed_at = null;
		
		if ($request->anyFilled(['email', 'phone'])) {
			$post->email_verified_at = now();
			$post->phone_verified_at = now();
			
			// Email verification key generation
			if ($emailVerificationRequired) {
				$post->email_token = generateToken(hashed: true);
				$post->email_verified_at = null;
			}
			
			// Mobile activation key generation
			if ($phoneVerificationRequired) {
				$post->phone_token = generateOtp(defaultOtpLength());
				$post->phone_verified_at = null;
			}
		}
		
		if (
			config('settings.listing_form.listings_review_activation') != '1'
			&& !$emailVerificationRequired
			&& !$phoneVerificationRequired
		) {
			$post->reviewed_at = now();
		}
		
		// Save
		try {
			
			$post->save();
			
		} catch (Throwable $e) {
			return apiResponse()->error($e->getMessage());
		}
		
		// Save the logo
		if (!empty($authUser)) {
			// For logged-in user
			$post->logo_path = $company->logo_path ?? null;
			
			$post->save();
		} else {
			// For guest user
			// Save the company's logo in the listing table
			// ---
			// Get the logo uploaded file object
			$logoFile = $request->file('company.logo_path', $request->files->get('company.logo_path'));
			if (!empty($logoFile)) {
				$destPath = 'files/' . strtolower($post->country_code) . '/' . $post->id;
				$post->logo_path = Upload::image($logoFile, $destPath);
				
				$post->save();
			}
		}
		
		// Generate the listing's logo thumbnails
		GenerateLogoThumbnails::dispatchSync($post);
		
		// Get the API response data
		$data = [
			'success' => true,
			'message' => $this->apiMsg['payable']['success'],
			'result'  => (new PostResource($post))->toArray($request),
		];
		
		$extra = [];
		
		// Auto-Register the Author
		$extra['autoRegisteredUser'] = $this->autoRegister($post, $request);
		
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
		$data['message'] = $this->apiMsg['payable']['success'];
		
		// Send Verification Link or Code
		// Email
		if ($emailVerificationRequired) {
			// Send Verification Link by Email
			$extra['sendEmailVerification'] = $this->sendEmailVerification('posts', $post);
			if (
				array_key_exists('success', $extra['sendEmailVerification'])
				&& array_key_exists('message', $extra['sendEmailVerification'])
			) {
				$extra['mail']['success'] = $extra['sendEmailVerification']['success'];
				$extra['mail']['message'] = $extra['sendEmailVerification']['message'];
			}
		}
		
		// Phone
		if ($phoneVerificationRequired) {
			// Send Verification Code by SMS
			$extra['sendPhoneVerification'] = $this->sendPhoneVerification('posts', $post);
			if (
				array_key_exists('success', $extra['sendPhoneVerification'])
				&& array_key_exists('message', $extra['sendPhoneVerification'])
			) {
				$extra['mail']['success'] = $extra['sendPhoneVerification']['success'];
				$extra['mail']['message'] = $extra['sendPhoneVerification']['message'];
			}
		}
		
		// Once Verification Notification is sent (containing Link or Code),
		// Send Confirmation Notification, when user clicks on the Verification Link or enters the Verification Code.
		// Done in the "app/Observers/PostObserver.php" file.
		
		$data['extra'] = $extra;
		
		return apiResponse()->json($data);
	}
}
