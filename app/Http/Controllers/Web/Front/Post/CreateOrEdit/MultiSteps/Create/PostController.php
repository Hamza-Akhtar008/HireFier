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

use App\Http\Requests\Front\PostRequest;
use Illuminate\Http\RedirectResponse;

class PostController extends BaseController
{
	/**
	 * Listing's step
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
		
		if (isMultipleCompaniesPerUserEnabled()) {
			// Return to the last unlocked step if the current step remains locked
			$currentStep = $this->getStepByKey(get_class($this));
			$lastUnlockedStepUrl = $this->getLastUnlockedStepUrlOnlyIfGivenStepIsLocked($currentStep);
			if (!empty($lastUnlockedStepUrl)) {
				return redirect()->to($lastUnlockedStepUrl)->withHeaders(config('larapen.core.noCacheHeaders'));
			}
		}
		
		$companyInput = session('companyInput');
		$postInput = session('postInput');
		
		// Get steps URLs & labels
		$currentStep = $this->getStepByKey(get_class($this));
		$previousStepUrl = $this->getPrevStepUrl($currentStep);
		$previousStepLabel = t('Previous');
		$formActionUrl = request()->fullUrl();
		$nextStepUrl = null;
		if (
			isset($this->countPackages, $this->countPaymentMethods)
			&& $this->countPackages > 0
			&& $this->countPaymentMethods > 0
			&& doesNoPackageOrPremiumOneSelected()
		) {
			$nextStepLabel = t('Next');
		} else {
			$nextStepLabel = t('submit');
		}
		
		// Share steps URLs & label variables
		view()->share('previousStepUrl', $previousStepUrl);
		view()->share('previousStepLabel', $previousStepLabel);
		view()->share('formActionUrl', $formActionUrl);
		view()->share('nextStepUrl', $nextStepUrl);
		view()->share('nextStepLabel', $nextStepLabel);
		
		// Go the company page when its session doesn't create
		if (empty($companyInput)) {
			if (request()->query('dataMissing') != 'company') {
				$companyStep = $this->getStepByKey(CompanyController::class);
				$companyStepUrl = $this->getStepUrl($companyStep);
				$companyStepUrl = urlQuery($companyStepUrl)
					->setParameters(['dataMissing' => 'company'])
					->toString();
				
				return redirect()->to($companyStepUrl)->withHeaders(config('larapen.core.noCacheHeaders'));
			}
		}
		
		return view('front.post.createOrEdit.multiSteps.create.post', compact('companyInput', 'postInput'));
	}
	
	/**
	 * Listing's step (POST)
	 *
	 * @param \App\Http\Requests\Front\PostRequest $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	public function postForm(PostRequest $request): RedirectResponse
	{
		if (isMultipleCompaniesPerUserEnabled()) {
			// Return to the last unlocked step if the current step remains locked
			$currentStep = $this->getStepByKey(get_class($this));
			$lastUnlockedStepUrl = $this->getLastUnlockedStepUrlOnlyIfGivenStepIsLocked($currentStep);
			if (!empty($lastUnlockedStepUrl)) {
				return redirect()->to($lastUnlockedStepUrl)->withHeaders(config('larapen.core.noCacheHeaders'));
			}
		}
		
		// Use unique ID to store post's pictures
		if (session()->has('uid')) {
			$this->tmpUploadDir = $this->tmpUploadDir . '/' . session('uid');
		}
		
		session()->put('postInput', $request->except($this->unwantedFields()));
		
		// Redirect to the next page or Submit the form
		if (
			isset($this->countPackages, $this->countPaymentMethods)
			&& $this->countPackages > 0
			&& $this->countPaymentMethods > 0
			&& doesNoPackageOrPremiumOneSelected()
		) {
			// Get the next URL
			$currentStep = $this->getStepByKey(get_class($this));
			$nextUrl = $this->getNextStepUrl($currentStep);
			
			return redirect()->to($nextUrl)->withHeaders(config('larapen.core.noCacheHeaders'));
		} else {
			// Submit the form
			return $this->storeInputDataInDatabase($request);
		}
	}
}
