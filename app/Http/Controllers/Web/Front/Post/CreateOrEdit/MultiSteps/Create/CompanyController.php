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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create;

use App\Helpers\Common\Files\TmpUpload;
use App\Http\Requests\Front\CompanyRequest;
use Illuminate\Http\RedirectResponse;
use Throwable;

class CompanyController extends BaseController
{
	/**
	 * Company's step
	 *
	 * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
	 */
	public function showForm()
	{
		// Check if the 'Pricing Page' must be started first, and make redirection to it.
		$pricingUrl = $this->getPricingPage($this->getSelectedPackage());
		if (!empty($pricingUrl)) {
			return redirect()->to($pricingUrl)->withHeaders(config('larapen.core.noCacheHeaders'));
		}
		
		// Check if the form type is 'Single-Step Form' and make redirection to it (permanently).
		if (isSingleStepFormEnabled()) {
			$url = urlGen()->addPost();
			if ($url != request()->fullUrl()) {
				return redirect()->to($url, 301)->withHeaders(config('larapen.core.noCacheHeaders'));
			}
		}
		
		// Only Admin users and Employers/Companies can post ads
		if (auth()->check()) {
			$userTypeId = auth()->user()->user_type_id ?? null;
			$isCandidateAccount = ($userTypeId != 1);
			if ($isCandidateAccount) {
				return redirect()->intended(urlGen()->accountOverview());
			}
		}
		
		// If the multiple companies per user option is enabled,
		// then auto-select the latest user's company and go to the listing details page
		if (!isCompanyFormEnabled($this->companies)) {
			$latestCompany = $this->companies->first();
			
			if (!empty(data_get($latestCompany, 'id'))) {
				$companyInput['company_id'] = data_get($latestCompany, 'id');
				$companyInput['company'] = $latestCompany;
				
				session()->put('companyInput', $companyInput);
				
				// Get the next URL
				$currentStep = $this->getStepByKey(get_class($this));
				$nextUrl = $this->getNextStepUrl($currentStep);
				
				return redirect()->to($nextUrl)->withHeaders(config('larapen.core.noCacheHeaders'));
			}
		}
		
		// Create an unique temporary ID
		if (!session()->has('uid')) {
			session()->put('uid', uniqueCode(9));
		}
		
		$companyInput = session('companyInput');
		
		// Get steps URLs & labels
		$previousStepUrl = null;
		$previousStepLabel = null;
		$formActionUrl = request()->fullUrl();
		$nextStepUrl = null;
		$nextStepLabel = t('Next');
		
		// Share steps URLs & label variables
		view()->share('previousStepUrl', $previousStepUrl);
		view()->share('previousStepLabel', $previousStepLabel);
		view()->share('formActionUrl', $formActionUrl);
		view()->share('nextStepUrl', $nextStepUrl);
		view()->share('nextStepLabel', $nextStepLabel);
		
		return view('front.post.createOrEdit.multiSteps.create.company', compact('companyInput'));
	}
	
	/**
	 * Company's step (POST)
	 *
	 * @param \App\Http\Requests\Front\CompanyRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postForm(CompanyRequest $request): RedirectResponse
	{
		// Use unique ID to store post's pictures
		if (session()->has('uid')) {
			$this->tmpUploadDir = $this->tmpUploadDir . '/' . session('uid');
		}
		
		$companyInputOld = (array)session('companyInput');
		$companyInput = $request->except($this->unwantedFields());
		
		// Is new company creation allowed in this context?
		$isNewCompanyCreationAllowed = !empty($companyInput['company']['name']);
		if ($isNewCompanyCreationAllowed) {
			// New company creation
			// ---
			// Set temporary ID for the company before its creation
			$companyInput['company']['id'] = 'new';
			
			// Save uploaded file
			$file = $request->file('company.logo_path');
			if (!empty($file)) {
				$filePath = TmpUpload::image($file, $this->tmpUploadDir);
				
				// Create the thumbnail of the temporary image (to load it when user try to retrieve non-saved listing)
				$logoUrl = thumbService($filePath)->resize('picture-md')->url();
				
				$companyInput['company']['logo_path'] = $filePath;
				
				// Remove old company logo
				if (!empty($companyInputOld['company']['logo_path'])) {
					try {
						$this->removePictureWithItsThumbs($companyInputOld['company']['logo_path']);
					} catch (Throwable $e) {
					}
				}
			} else {
				// Skip old logo if the logo_path field is not filled
				if (!empty($companyInputOld['company']['logo_path'])) {
					$companyInput['company']['logo_path'] = $companyInputOld['company']['logo_path'];
				}
			}
		} else {
			// Retrieve the selected company
			// ---
			// Company is selected in user's companies list
			$companyId = $companyInput['company_id'] ?? 0;
			$selectedCompany = $this->companies->get($companyId);
			if (!empty($selectedCompany)) {
				$companyInput['company'] = $selectedCompany;
			}
		}
		
		session()->put('companyInput', $companyInput);
		
		// Get the next URL
		$currentStep = $this->getStepByKey(get_class($this));
		$nextUrl = $this->getNextStepUrl($currentStep);
		
		return redirect()->to($nextUrl)->withHeaders(config('larapen.core.noCacheHeaders'));
	}
}
