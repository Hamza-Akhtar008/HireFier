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

use App\Http\Requests\Front\PostRequest;
use App\Http\Resources\PostResource;
use App\Jobs\GenerateLogoThumbnails;
use App\Models\City;
use App\Models\Post;
use App\Models\Scopes\ReviewedScope;
use App\Models\Scopes\VerifiedScope;
use App\Services\Post\Update\MultiStepsForm\UpdatePostCompany;
use Illuminate\Http\JsonResponse;

trait MultiStepsForm
{
	use UpdatePostCompany;
	
	/**
	 * @param $tokenOrId
	 * @param \App\Http\Requests\Front\PostRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	protected function multiStepsFormUpdate($tokenOrId, PostRequest $request): JsonResponse
	{
		$authUser = auth(getAuthGuard())->user();
		
		$countPackages = $request->input('count_packages', 0);
		$countPaymentMethods = $request->input('count_payment_methods', 0);
		$countryCode = $request->input('country_code', config('country.code'));
		
		$post = null;
		if (!empty($authUser)) {
			$post = Post::query()
				->withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
				->inCountry($countryCode)
				->where('user_id', $authUser->getAuthIdentifier())
				->where('id', $tokenOrId)
				->first();
		}
		
		if (empty($post)) {
			return apiResponse()->notFound(t('post_not_found'));
		}
		
		// Get the Post's City
		$city = City::find($request->input('city_id', 0));
		if (empty($city)) {
			return apiResponse()->error(t('city_not_found'));
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
			'message' => t('your_listing_is_updated'),
			'result'  => (new PostResource($post))->toArray($request),
		];
		
		$extra = [];
		
		// User should he go on Payment page or not?
		$shouldHeGoOnPaymentPage = (
			is_numeric($countPackages)
			&& is_numeric($countPaymentMethods)
			&& $countPackages > 0
			&& $countPaymentMethods > 0
		);
		if ($shouldHeGoOnPaymentPage) {
			$extra['steps']['payment'] = true;
		} else {
			$extra['steps']['payment'] = false;
		}
		
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
