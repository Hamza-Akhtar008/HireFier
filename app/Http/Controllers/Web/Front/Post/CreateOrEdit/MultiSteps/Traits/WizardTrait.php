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

namespace App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Traits;

use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\CompanyController as CreateCompanyController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\FinishController as CreateFinishController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\PaymentController as CreatePaymentController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Create\PostController as CreatePostController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Edit\CompanyController as EditCompanyController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Edit\PaymentController as EditPaymentController;
use App\Http\Controllers\Web\Front\Post\CreateOrEdit\MultiSteps\Edit\PostController as EditPostController;
use App\Http\Controllers\Web\Front\Traits\TravelWizardTrait;

trait WizardTrait
{
	use TravelWizardTrait;
	
	/**
	 * Get Wizard Menu
	 *
	 * @param null $post
	 * @return void
	 */
	public function shareNavItems($post = null): void
	{
		$this->navItems = $this->getNavItems($post);
		view()->share('wizardMenu', $this->navItems);
	}
	
	/**
	 * Get the installation navigation links
	 *
	 * @param null $post
	 * @return array
	 */
	protected function getNavItems($post = null): array
	{
		$uriPath = request()->segment($this->stepsSegment);
		
		$navItems = [];
		if (isPostCreationRequest()) {
			$navItems = $this->getCreationNavItems($navItems, $uriPath, $post);
		} else {
			$navItems = $this->getEditionNavItems($navItems, $uriPath, $post);
		}
		
		// Save the original menu before formatting it
		$this->rawNavItems = $navItems;
		
		return $this->formatAllNavItems($navItems, $uriPath);
	}
	
	/**
	 * @param array $navItems
	 * @param string|null $uriPath
	 * @param null $post
	 * @return array
	 */
	private function getCreationNavItems(array $navItems, ?string $uriPath = null, $post = null): array
	{
		// Check if the company form step is enabled
		$isCompanyFormEnabled = isCompanyFormEnabled($this->companies);
		$initStep = $isCompanyFormEnabled ? 1 : 0;
		
		// Company's Information
		$navItems[CreateCompanyController::class] = [
			'step'        => $initStep,
			'label'       => t('company_information'),
			'url'         => urlGen()->addPostCompany(),
			'class'       => '',
			'included'    => $isCompanyFormEnabled,
			'lockMessage' => null,
			'unlocked'    => true, // Unlocked by default
		];
		
		// Listing's Details
		$navItems[CreatePostController::class] = [
			'step'        => $initStep + 1,
			'label'       => t('listing_details'),
			'url'         => urlGen()->addPostDetails(),
			'class'       => '',
			'included'    => true,
			'lockMessage' => t('wizard_company_input_required'),
			'unlocked'    => !empty(session('companyInput')),
		];
		
		// Payment
		$isIncluded = (
			isset($this->countPackages, $this->countPaymentMethods)
			&& $this->countPackages > 0
			&& $this->countPaymentMethods > 0
			&& doesNoPackageOrPremiumOneSelected()
		);
		$navItems[CreatePaymentController::class] = [
			'step'        => $initStep + 2,
			'label'       => t('Payment'),
			'url'         => urlGen()->addPostPayment(),
			'class'       => '',
			'included'    => $isIncluded,
			'lockMessage' => t('wizard_post_input_required'),
			'unlocked'    => (
				!empty(session('companyInput'))
				&& !empty(session('postInput'))
			),
		];
		
		if ($uriPath == 'verify') {
			// Activation
			$navItems['verifyInfo'] = [
				'step'        => $initStep + 3,
				'label'       => t('Activation'),
				'url'         => null,
				'class'       => '',
				'included'    => true,
				'lockMessage' => null,
				'unlocked'    => false,
			];
		} else {
			// Finish
			$navItems[CreateFinishController::class] = [
				'step'        => $initStep + 3,
				'label'       => t('Finish'),
				'url'         => urlGen()->addPostFinished(),
				'class'       => '',
				'included'    => true,
				'lockMessage' => null,
				'unlocked'    => false,
			];
		}
		
		return $navItems;
	}
	
	/**
	 * @param array $navItems
	 * @param string|null $uriPath
	 * @param null $post
	 * @return array
	 */
	private function getEditionNavItems(array $navItems, ?string $uriPath = null, $post = null): array
	{
		// Check if the company form step is enabled
		$isCompanyFormEnabled = isCompanyFormEnabled($this->companies);
		$initStep = $isCompanyFormEnabled ? 1 : 0;
		
		// Company's Information
		$navItems[EditCompanyController::class] = [
			'step'        => $initStep,
			'label'       => t('company_information'),
			'url'         => urlGen()->editPostCompany($post),
			'class'       => '',
			'included'    => $isCompanyFormEnabled,
			'lockMessage' => null,
			'unlocked'    => !empty($post),
		];
		
		// Listing's Details
		$navItems[EditPostController::class] = [
			'step'        => $initStep + 1,
			'label'       => t('listing_details'),
			'url'         => urlGen()->editPost($post),
			'class'       => '',
			'included'    => true,
			'lockMessage' => t('wizard_company_input_required'),
			'unlocked'    => !empty($post),
		];
		
		// Payment
		$isIncluded = (
			isset($this->countPackages, $this->countPaymentMethods)
			&& $this->countPackages > 0
			&& $this->countPaymentMethods > 0
		);
		$navItems[EditPaymentController::class] = [
			'step'        => $initStep + 2,
			'label'       => t('Payment'),
			'url'         => urlGen()->editPostPayment($post),
			'class'       => '',
			'included'    => $isIncluded,
			'lockMessage' => t('wizard_post_input_required'),
			'unlocked'    => !empty($post),
		];
		
		// Finish
		$navItems['finishInfo'] = [
			'step'        => $initStep + 3,
			'label'       => t('Finish'),
			'url'         => null,
			'class'       => '',
			'included'    => true,
			'lockMessage' => null,
			'unlocked'    => false,
		];
		
		return $navItems;
	}
}
