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

namespace App\Services\Company;

use App\Helpers\Common\Files\Upload;
use App\Http\Requests\Request;
use App\Jobs\GenerateLogoThumbnails;
use App\Models\Company;

trait SaveCompany
{
	/**
	 * Store the user's company
	 *
	 * @param $userId
	 * @param \App\Http\Requests\Request $request
	 * @return \App\Models\Company
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	protected function storeCompany($userId, Request $request): Company
	{
		return $this->saveCompany($userId, $request);
	}
	
	/**
	 * Update the user's company
	 *
	 * @param $userId
	 * @param \App\Http\Requests\Request $request
	 * @param \App\Models\Company $company
	 * @return \App\Models\Company
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	protected function updateCompany($userId, Request $request, Company $company): Company
	{
		return $this->saveCompany($userId, $request, $company);
	}
	
	/**
	 * Save the user's company
	 *
	 * @param $userId
	 * @param \App\Http\Requests\Request $request
	 * @param \App\Models\Company|null $company
	 * @return \App\Models\Company
	 * @throws \App\Exceptions\Custom\CustomException
	 */
	protected function saveCompany($userId, Request $request, Company|null $company = null): Company
	{
		// Get Company Input
		$companyInput = $request->input('company');
		if (empty($companyInput['user_id'])) {
			$companyInput['user_id'] = $userId;
		}
		if (empty($companyInput['country_code'])) {
			$companyInput['country_code'] = config('country.code');
		}
		
		// Create
		if (empty($company)) {
			$company = new Company();
		}
		
		// Update
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
		
		return $company;
	}
}
