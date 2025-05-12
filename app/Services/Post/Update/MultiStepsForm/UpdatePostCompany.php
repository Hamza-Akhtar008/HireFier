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

namespace App\Services\Post\Update\MultiStepsForm;

use App\Helpers\Common\Files\Upload;
use App\Http\Requests\Front\CompanyRequest;
use App\Http\Resources\PostResource;
use App\Jobs\GenerateLogoThumbnails;
use App\Models\Company;
use App\Models\Post;
use App\Models\Scopes\ReviewedScope;
use App\Models\Scopes\VerifiedScope;
use Illuminate\Http\JsonResponse;

trait UpdatePostCompany
{
	/**
	 * Update (change) the post's company
	 *
	 * @param $tokenOrId
	 * @param \App\Http\Requests\Front\CompanyRequest $request
	 * @return \Illuminate\Http\JsonResponse
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	protected function changePostCompany($tokenOrId, CompanyRequest $request): JsonResponse
	{
		// Get the logged user
		$authUser = auth(getAuthGuard())->user();
		
		if (empty($authUser)) {
			return apiResponse()->unauthorized();
		}
		
		$countryCode = $request->input('country_code', config('country.code'));
		
		$post = Post::query()
			->withoutGlobalScopes([VerifiedScope::class, ReviewedScope::class])
			->inCountry($countryCode)
			->where('user_id', $authUser->getAuthIdentifier())
			->where('id', $tokenOrId)
			->first();
		
		if (empty($post)) {
			return apiResponse()->notFound(t('post_not_found'));
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
		
		// Check if the listing sensitive data have been changed
		$hasListingDataUpdated = false;
		if (config('settings.listing_form.listings_review_activation')) {
			$hasListingDataUpdated = (
				md5($post->company_description) != md5((isset($company->description)) ? $company->description : null)
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
		
		// Other fields
		$post->company_id = $company->id ?? 0;
		$post->company_name = $company->name ?? null;
		$post->company_description = $company->description ?? null;
		$post->logo_path = $company->logo_path ?? null;
		
		// Generate the listing's logo thumbnails
		GenerateLogoThumbnails::dispatchSync($post);
		
		// Save
		$post->save();
		
		$data = [
			'success' => true,
			'message' => t('listing_company_updated'),
			'result'  => (new PostResource($post))->toArray($request),
		];
		
		return apiResponse()->json($data);
	}
}
